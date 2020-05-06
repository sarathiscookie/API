<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\KeyContainerRequest;
use App\Http\Traits\KeyContainerTrait;
use App\Http\Traits\KeyTypeTrait;
use App\Http\Traits\ShopTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\KeyShopTrait;
use App\Http\Traits\CountryTrait;
use App\Key;
use App\KeyContainer;
use App\KeyInstruction;
use App\KeyShop;
use Illuminate\Support\Facades\DB;

class KeyController extends Controller
{
    use KeyTypeTrait, ShopTrait, CompanyTrait, KeyContainerTrait, KeyShopTrait, CountryTrait;
    /**
     * Show the key view page. Passing all active key types and companies into the key view page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Key types trait to fetch the key types.
        $keyTypes    = $this->keytypes();

        // Company trait to fetch the companies.
        $companies   = $this->company();

        return view('admin.key', ['keyTypes' => $keyTypes, 'companies' => $companies]);
    }

    /**
     * Show the shops related to key.
     *
     * @param  int  $companyId
     * @return \Illuminate\Http\Response
     */
    public function findShops($companyId)
    {
        try {
            // Shop trait to fetch the active shops.
            $shops = $this->getShops($companyId);

            return response()->json(['shopAvailableStatus' => 'success', 'shops' => $shops], 200);
        } catch (\Exception $e) {
            return response()->json(['shopAvailableStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Show the key containers data in to view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        // Getting all the http request.
        $params = $request->all();

        $columns = array(
            1 => 'name',
            2 => 'active',
        );

        // Query to select key containers.
        $q             = KeyContainer::join('key_shops', 'key_containers.id', '=', 'key_shops.key_container_id')
            ->join('shops', 'key_shops.shop_id', '=', 'shops.id')
            ->join('companies', 'key_containers.company_id', '=', 'companies.id')
            ->join('shopnames', 'shops.shopname_id', '=', 'shopnames.id')
            ->select('key_containers.id', 'key_containers.name', 'key_containers.container', 'companies.company', DB::raw('group_concat(distinct shopnames.name separator ", ") as shopName'), 'key_containers.active')
            ->groupBy('key_containers.id');

        // Getting count of key containers.
        $totalData     = $q->count();

        $totalFiltered = $totalData;

        $limit         = (int) $request->input('length');

        $start         = (int) $request->input('start');

        $order         = $columns[$params['order'][0]['column']]; // Contains column index

        $dir           = $params['order'][0]['dir']; // Contains order such as asc/desc

        // // If the request has a search value (key), the query to search the keys will execute.
        if (!empty($request->input('search.value'))) {
            $this->searchKey($q, $request->input('search.value'));
        }

        // If the table has footer column value (key), the query to search keys based on the key will execute.
        if (!empty($params['columns'][1]['search']['value'])) {
            $this->tfootKey($q, $params['columns'][1]['search']['value']);
        }

        // If the table has footer column value (key status), the query to search keys based on the key will execute.
        if (!empty($params['columns'][2]['search']['value'])) {
            $this->tfootKeyStatus($q, $params['columns'][2]['search']['value']);
        }

        // Query scripts ends here.
        $keyLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if (!empty($keyLists)) {
            foreach ($keyLists as $key => $keyList) {

                $htmlBadgeShopName = '';

                foreach (explode(', ', $keyList->shopName) as $shopKey => $shopName) {
                    $htmlBadgeShopName .= '<span class="badge badge-info badge-pill">' . $shopName . '</span>';
                }

                $keyInstructions = '';

                $keyInstrutionDatas = KeyInstruction::select('key_instructions.id', 'key_instructions.country_id', 'key_instructions.key_container_id', 'key_instructions.instruction_url', 'countries.code AS countryCode')
                    ->join('countries', 'key_instructions.country_id', '=', 'countries.id')
                    ->where('key_instructions.key_container_id', $keyList->id)
                    ->get();

                if (count($keyInstrutionDatas) > 0) {
                    $keyInstructions = '<br> <h6>Key Instructions</h6> <hr>';
                    foreach ($keyInstrutionDatas as $keyInstrutionData) {
                        $keyInstructions .= '<div class="keyinstructiondata">' . $keyInstrutionData->countryCode . ': <a href="/admin/dashboard/key/instruction/download/file/' . $keyInstrutionData->id . '" class="downloadKeyInstruction"><i class="fas fa-download cursor"></i></a> <a  class="deleteKeyInstruction" data-keydeleteinstructionid="' . $keyInstrutionData->id . '"><i class="far fa-trash-alt cursor" style="color:#DC143C;"></i></a></div>';
                    }
                }

                $nestedData['hash']     = '<input class="checked" type="checkbox" name="id[]" value="' . $keyList->id . '" />';
                $nestedData['name']     = '<h6>' . $keyList->name . '</h6> <hr><div>Container: <span class="badge badge-info badge-pill">' . $keyList->container . '</span></div> <div>Company: <span class="badge badge-info badge-pill text-capitalize">' . $keyList->company . '</span></div> <div>Shops: ' . $htmlBadgeShopName . '</div>' . $keyInstructions;
                $nestedData['active']   = $this->keyStatusHtml($keyList->id, $keyList->active);
                $nestedData['actions']  = $this->editKeyContainerModel($keyList->id);
                $data[]                 = $nestedData;
            }
        }

        // Preparing array to send the response in JSON format to draw the data in datatable.
        $json_data = array(
            'draw'            => (int) $params['draw'],
            'recordsTotal'    => (int) $totalData,
            'recordsFiltered' => (int) $totalFiltered,
            'data'            => $data
        );

        return response()->json($json_data);
    }

    /**
     * Function to search keys based on the key name, shops and country.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchKey($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('key_containers.name', 'like', "%{$searchData}%")
                ->orWhere('companies.company', 'like', "%{$searchData}%")
                ->orWhere('shopnames.name', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;
    }

    /**
     * Function to filter keys based on the key name, shops and country.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootKey($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('key_containers.name', 'like', "%{$searchData}%")
                ->orWhere('companies.company', 'like', "%{$searchData}%")
                ->orWhere('shopnames.name', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;
    }

    /**
     * Function to filter keys based on the key status.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootKeyStatus($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('key_containers.active', "{$searchData}");
        });

        // Total filtered count
        $totalFiltered = $q->count();

        return $this;
    }

    /**
     * HTML group button to change key status 
     * @param  int $id
     * @param  string $oldStatus  
     * @return \Illuminate\Http\Response
     */
    public function keyStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-keycontid="' . $id . '">
            <input type="checkbox" class="buttonStatus" ' . $checked . '>
            <span class="slider round"></span>
            </label>';

        return $html;
    }

    /**
     * Update the key status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {
            // Fetching key container details based on requested key container id.
            $keyContainer = KeyContainer::findOrFail($request->keycontid);

            $keyContainer->active = $request->newStatus;

            $keyContainer->save();

            return response()->json(['keyContainerStatusChange' => 'success', 'message' => 'Key status updated successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['keyContainerStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit key container data
     * @param  integer $keyContainerId
     * @return \Illuminate\Http\Response
     */
    public function editKeyContainerModel($keyContainerId)
    {
        try {
            // Fetching key container details based on key container id.
            $keyContainer   = $this->edit($keyContainerId);

            // Getting the key types
            $keyTypeOptions = '';
            foreach ($this->keytypes() as $keyValue => $keyType) {
                $keyTypeSelected = ($keyContainer->type === $keyValue) ? 'selected' : '';
                $keyTypeOptions .= '<option value="' . $keyValue . '" ' . $keyTypeSelected . '>' . $keyType . '</option>';
            }

            // Getting the companies
            $companyOptions = '';
            foreach ($this->company() as $company) {
                $companySelected = ($keyContainer->company->id === $company->id) ? 'selected' : '';
                $companyOptions .= '<option value="' . $company->id . '" ' . $companySelected . '>' . $company->company . '</option>';
            }

            $keys = '';
            foreach ($keyContainer->keys as $keyDetail) {
                $keys .= $keyDetail->key . ',';
            }

            // Getting the shops
            $shopOptions = '';
            foreach ($this->getShops($keyContainer->company->id) as $shop) {
                $shopSelected = ($this->getKeyShop($keyContainer->id, $shop->id) !== null) ? 'selected' : '';
                $shopOptions .= '<option value="' . $shop->id . '" ' . $shopSelected . '>' . $shop->shop . '</option>';
            }

            // Getting the countries
            $countryOptions = '';
            foreach ($this->country() as $country) {
                $countryOptions .= '<option value="' . $country->id . '">' . $country->code . '</option>';
            }

            $html        = '<a class="btn btn-secondary btn-sm editKey cursor" data-keycontainerid="' . $keyContainer->id . '" data-keycontainercompanyid="' . $keyContainer->company->id . '"  data-toggle="modal" data-target="#editKeyModal_' . $keyContainer->id . '"><i class="fas fa-cog"></i></a> <a class="btn btn-secondary btn-sm createKeyInstruction cursor" data-keyinstructioncontainerid="' . $keyContainer->id . '" data-toggle="modal" data-target="#keyInstructionModal_' . $keyContainer->id . '" data-toggle="tooltip" data-placement="top" title="Create Key Instructions"><i class="fas fa-folder-plus"></i></a>

            <div class="modal fade" id="keyInstructionModal_' . $keyContainer->id . '" tabindex="-1" role="dialog" aria-labelledby="createKeyInstructionLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="createKeyInstructionLabel">Create Key Instruction</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="keyInstructionValidationAlert"></div>
            <div class="form-group col-md-6">
            <label for="key_instruction_language">Language <span class="required">*</span></label>
            <select id="key_instruction_language_' . $keyContainer->id . '" class="form-control" name="key_instruction_language">
            <option value="">Choose Language</option>
            ' . $countryOptions . '
            </select>
            </div>

            <div class="form-group col-md-12">
            <label for="key_instruction_file">Key Instruction File <span class="required">*</span></label>
            <input type="file" id="key_instruction_file_' . $keyContainer->id . '" class="form-control-file" name="key_instruction_file">
            </div>
            <button type="button" class="btn btn-primary btn-lg btn-block createKeyInstruction_' . $keyContainer->id . '"><i class="fas fa-plus"></i> Create Instruction </button>
            </div>

            </div>
            </div>
            </div>

            <div class="modal fade" id="editKeyModal_' . $keyContainer->id . '" tabindex="-1" role="dialog" aria-labelledby="editKeyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editKeyModalLabel">Edit Key Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>


            <div class="modal-body">
            <div class="keyUpdateValidationAlert_' . $keyContainer->id . '"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteEvent" data-id="' . $keyContainer->id . '"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="key_name_edit">Key Name <span class="required">*</span></label>
            <input type="text" name="key_name_edit" id="key_name_edit_' . $keyContainer->id . '" class="form-control"  maxlength="100" value="' . $keyContainer->name . '">
            </div>
            <div class="form-group col-md-6">
            <label for="key_company_name">Company <span class="required">*</span></label>
            <select id="key_company_name_' . $keyContainer->id . '" class="form-control" name="key_company_name">
            <option value="">Choose Company</option>
            ' . $companyOptions . '
            </select>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12" id="div_shop_edit_' . $keyContainer->id . '">
            <div class="no_shop_alert_' . $keyContainer->id . '"></div>

            <div class="div_shop_edit_' . $keyContainer->id . '">
            <label for="shop_edit">Shops <span class="required">*</span></label>
            <div id="shop_edits_first_div_' . $keyContainer->id . '">
            <select id="shop_edits_' . $keyContainer->id . '" class="form-control shop_edits_' . $keyContainer->id . '" name="shop_edit[]" multiple="multiple">
            <option class="first_option_shop_edit_' . $keyContainer->id . '" value="" disabled="disabled">Choose Shop</option>
            ' . $shopOptions . '
            </select>
            </div>

            </div>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-12">
            <label for="key_edit">Key <i class="far fa-question-circle" data-toggle="tooltip" data-placement="right" title="You can separated keys with commas, space and new line. But dont mix with these."></i><span class="required">*</span></label>
            <textarea class="form-control" name="keys_edit" id="keys_edit_' . $keyContainer->id . '" rows="3">' . trim($keys, ",") . '</textarea>
            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">
            <label for="activation_number_edit">Activation Number <span class="required">*</span></label>
            <input type="number" id="activation_number_edit_' . $keyContainer->id . '" class="form-control" name="activation_number_edit" maxlength="10" value="' . $keyContainer->activation_number . '">
            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateKeyContainer_' . $keyContainer->id . '"><i class="far fa-edit"></i> Update </button>

            </div>
            </div>
            </div>';

            return $html;
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Admin\KeyContainerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(KeyContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            // Key container trait to count the array.
            $countOfKeys                     = $this->countKeys($request->keys); //Count of array

            $keyContainer                    = new KeyContainer;
            $keyContainer->name              = $request->key_name;
            $keyContainer->container         = $this->generateContainer($request->key_type);
            $keyContainer->company_id        = $request->key_company;
            $keyContainer->type              = $request->key_type;
            $keyContainer->activation_number = $request->key_activation_number;
            $keyContainer->count             = $countOfKeys;
            $keyContainer->total_activation  = $request->key_activation_number * $countOfKeys;
            $keyContainer->active            = 'no';
            $keyContainer->save();

            // Storing shops id into the key shop table
            foreach ($request->key_shops as $shop) {
                $keyShops                   = new KeyShop;
                $keyShops->key_container_id = $keyContainer->id;
                $keyShops->shop_id          = $shop;
                $keyShops->save();
            }

            // Storing keys into the key table
            foreach ($request->keys as $key) {
                $keyDetails                   = new Key;
                $keyDetails->key_container_id = $keyContainer->id;
                $keyDetails->key              = preg_replace('/[ ,]+/', '', $key);
                $keyDetails->available        = 1;
                $keyDetails->save();
            }

            DB::commit();

            return response()->json(['keyStatus' => 'success', 'message' => 'Well done! Key created successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['keyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetching key container details based on key container id
        $keyContainer = KeyContainer::findOrFail($id);

        return $keyContainer;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\KeyContainerRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(KeyContainerRequest $request)
    {
        DB::beginTransaction();
        try {
            // Key container trait to count the array.
            $countOfKeys                     = $this->countKeys($request->keys); // Count of array

            // Deleting shops from key shop table
            KeyShop::where('key_container_id', $request->key_container_id)->delete();

            // Deleting keys from key table
            Key::where('key_container_id', $request->key_container_id)->delete();

            $keyContainer                    = KeyContainer::find($request->key_container_id);
            $keyContainer->name              = $request->key_name;
            $keyContainer->company_id        = $request->key_company;
            $keyContainer->activation_number = $request->key_activation_number;
            $keyContainer->count             = $countOfKeys;
            $keyContainer->total_activation  = $request->key_activation_number * $countOfKeys;
            $keyContainer->save();

            // Storing shops id in to key shop table
            foreach ($request->key_shop as $shop) {
                $keyShops                   = new KeyShop;
                $keyShops->key_container_id = $keyContainer->id;
                $keyShops->shop_id          = $shop;
                $keyShops->save();
            }

            // Storing keys in to key table
            foreach ($request->keys as $key) {
                $keyDetails                   = new Key;
                $keyDetails->key_container_id = $keyContainer->id;
                $keyDetails->key              = preg_replace('/[ ,]+/', '', $key);
                $keyDetails->available        = 1;
                $keyDetails->save();
            }

            DB::commit();

            return response()->json(['keyUpdatedStatus' => 'success', 'message' => 'Well done! Key details updated successfully'], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['keyUpdatedStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            // Deleting key details from key table.
            $key          = Key::where('key_container_id', $id)->delete();

            // Deleting key details from key shops table.
            $keyShop      = KeyShop::where('key_container_id', $id)->delete();

            // Deleting key details from key container table.
            $keyContainer = KeyContainer::destroy($id);

            DB::commit();

            return response()->json(['deletedKeyStatus' => 'success', 'message' => 'Key details deleted successfully'], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['deletedKeyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
