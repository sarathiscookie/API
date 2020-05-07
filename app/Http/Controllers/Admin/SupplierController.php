<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupplierRequest;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CountryTrait;
use App\User;
use App\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    use CompanyTrait, CountryTrait;
    /**
     * Show the suppliers view page. Passing all active companies and countries into the supplier view page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Country trait to fetch the companies.
        $companies = $this->company();

        // Country trait to fetch the countries.
        $countries = $this->country();

        return view('admin.supplier', ['companies' => $companies, 'countries' => $countries]);
    }

    /**
     * Show the suppliers data into the view page.
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

        // Getting count of suppliers.
        $totalData = User::select('id', 'name', 'active')
        ->supplier()
        ->count();

        // Query to select suppliers.
        $q         = User::select('id', 'name', 'active')
        ->supplier();

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; //contains column index
        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // If the request has a search value (supplier name), this query will execute and fetch the results.
        if( !empty($request->input('search.value')) ) {
            $this->searchSupplierName($q, $request->input('search.value'));
        }

        // If the table has footer column value (supplier name), this query will execute and fetch the results based on the supplier name.
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootSupplierName($q, $params['columns'][1]['search']['value']);
        }

        // If the table has footer column value (supplier status), this query will execute and fetch the results based on the supplier status.
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootSupplierStatus($q, $params['columns'][2]['search']['value']);
        }

        // Query scripts ends here.
        $supplierLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($supplierLists)) {

            foreach ($supplierLists as $key=> $supplierList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="'.$supplierList->id.'" />';
                $nestedData['name']       = $supplierList->name;
                $nestedData['active']     = $this->supplierStatusHtml($supplierList->id, $supplierList->active);
                $nestedData['actions']    = $this->editSupplierModel($supplierList->id);
                $data[]                   = $nestedData;
            }

        }

        // Preparing array to send the response in JSON format to draw the data on datatable.
        $json_data = array(
            'draw'            => (int)$params['draw'],
            'recordsTotal'    => (int)$totalData,
            'recordsFiltered' => (int)$totalFiltered,
            'data'            => $data
        );

        return response()->json($json_data);
    }

    /**
     * Function to search suppliers based on the supplier name.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchSupplierName($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * Function to filter suppliers based on the supplier name.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootSupplierName($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * Function to filter suppliers based on the supplier status.
     * @param  string $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootSupplierStatus($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        })
        ->count();

        return $this;    
    }

    /**
     * HTML group button to change supplier status 
     * @param  int $id
     * @param  string $oldStatus  
     * @return \Illuminate\Http\Response
     */
    public function supplierStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";

        $html    = '<label class="switch" data-supplierstatusid="'.$id.'">
            <input type="checkbox" class="buttonStatus" '.$checked.'>
            <span class="slider round"></span>
            </label>';

        return $html;
    }

    /**
     * Update supplier status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {
            // Fetching supplier details based on requested supplier id.
            $supplier         = User::findOrFail($request->supplierStatusId);

            $supplier->active = $request->newStatus;

            $supplier->save();

            return response()->json(['supplierStatusChange' => 'success', 'message' => 'Supplier status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['supplierStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit supplier data
     * @param  integer $supplierId
     * @return \Illuminate\Http\Response
     */
    public function editSupplierModel($supplierId)
    {
        try {
            // Fetching supplier details based on the supplier id.
            $supplier           = $this->edit($supplierId);

            // Getting countries and selecting matcing countries
            $countryOptions = '';
            foreach($this->country() as $country) {
                $countrySelected = ($supplier->country->id === $country->id) ? 'selected' : '';
                $countryOptions .= '<option value="'.$country->id.'" '.$countrySelected.'>'.$country->name.'</option>';
            }

            // Getting companies and selecting matching companies
            $supplierCompanyId = [];
            foreach($supplier->userCompanies as $supplierCompany) {
                $supplierCompanyId[] = $supplierCompany->company_id;
            }

            $companyOptions = '';
            foreach($this->company() as $company) {
                $companySelected = (in_array($company->id, $supplierCompanyId)) ? 'selected' : '';
                $companyOptions .= '<option value="'.$company->id.'" '.$companySelected.'>'.$company->company.'</option>';
            }

            $country  = ($supplier->country === 'de') ? 'selected' : '';

            $html     = '<a class="btn btn-secondary btn-sm editSupplier cursor" data-supplierid="'.$supplier->id.'" data-toggle="modal" data-target="#editSupplierModal_'.$supplier->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editSupplierModal_'.$supplier->id.'" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="supplierUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteSupplierEvent" data-id="'.$supplier->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="email">Email:</label>
            <div class="badge badge-secondary text-wrap" style="width: 6rem;">
            '.$supplier->email.'
            </div>
            

            </div>
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($supplier->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="supplier_name">Name <span class="required">*</span></label>
            <input id="supplier_name_'.$supplier->id.'" type="text" class="form-control" name="supplier_name" value="'.$supplier->name.'" maxlength="255">

            </div>
            <div class="form-group col-md-6">

            <label for="supplier_phone">Phone <span class="required">*</span></label>
            <input id="supplier_phone_'.$supplier->id.'" type="text" class="form-control" name="supplier_phone" value="'.$supplier->phone.'" maxlength="20">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="supplier_company">Company <span class="required">*</span></label>
            <select id="supplier_company_'.$supplier->id.'" class="form-control" name="supplier_company[]" multiple="multiple">
            '.$companyOptions.'
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="supplier_street">Street <span class="required">*</span></label>
            <input id="supplier_street_'.$supplier->id.'" type="text" class="form-control" name="supplier_street" value="'.$supplier->street.'" maxlength="255">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="supplier_city">City <span class="required">*</span></label>
            <input id="supplier_city_'.$supplier->id.'" type="text" class="form-control" name="supplier_city" value="'.$supplier->city.'" maxlength="255">

            </div>
            <div class="form-group col-md-4">

            <label for="supplier_country">Country <span class="required">*</span></label>
            <select id="supplier_country_'.$supplier->id.'" class="form-control" name="supplier_country">
            <option value="">Choose Country</option>
            '.$countryOptions.'
            </select>

            </div>
            <div class="form-group col-md-2">

            <label for="supplier_zip">Zip <span class="required">*</span></label>
            <input id="supplier_zip_'.$supplier->id.'" type="text" class="form-control" name="supplier_zip" value="'.$supplier->postal.'" maxlength="20">

            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateSupplier_'.$supplier->id.'"><i class="fas fa-user-edit"></i> Update Supplier</button>

            </div>

            </div>
            </div>
            </div>';

            return $html;
            
        } 
        catch(\Exception $e) {
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
     * Store a newly created supplier into storage.
     *
     * @param  \App\Http\Requests\Admin\SupplierRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupplierRequest $request)
    {
        try {
            // Create username for supplier. Reason is, username field is mandatory and unique. 
            $explodeEmail           = explode("@", $request->email);

            $supplier               = new User;
            $supplier->name         = $request->supplier_name;
            $supplier->email        = $request->email;
            $supplier->username     = $explodeEmail[0];
            $supplier->phone        = $request->supplier_phone;
            $supplier->country_id   = $request->supplier_country;
            $supplier->city         = $request->supplier_city;
            $supplier->street       = $request->supplier_street;
            $supplier->postal       = $request->supplier_zip;
            $supplier->active       = 'no';
            $supplier->role         = 'supplier';
            $supplier->save();

            foreach($request->supplier_company as $company) {
                $supplierCompany             = new UserCompany;
                $supplierCompany->user_id    = $supplier->id;
                $supplierCompany->company_id = $company;
                $supplierCompany->save();
            }

            return response()->json(['supplierStatus' => 'success', 'message' => 'Well done! Supplier created successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['supplierStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
     * Show the form for editing the sipplier resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetching supplier details based on the requested supplier id.
        $supplier = User::supplier()->findOrFail($id);

        return $supplier;
    }

    /**
     * Update the specified supplier in storage.
     *
     * @param  \App\Http\Requests\Admin\SupplierRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SupplierRequest $request)
    {
        DB::beginTransaction();
        try {
            // Delete and store company id in user_companies table.
            UserCompany::where('user_id', $request->supplierid)->delete();
            
            $supplier             = User::supplier()->find($request->supplierid);
            $supplier->name       = $request->supplier_name;
            $supplier->phone      = $request->supplier_phone;
            $supplier->street     = $request->supplier_street;
            $supplier->city       = $request->supplier_city;
            $supplier->country_id = $request->supplier_country;
            $supplier->postal     = $request->supplier_zip;
            $supplier->save();

            foreach($request->supplier_company as $company) {
                $supplierCompany             = new UserCompany;
                $supplierCompany->user_id    = $supplier->id;
                $supplierCompany->company_id = $company;
                $supplierCompany->save();
            }

            DB::commit();
            return response()->json(['supplierStatusUpdate' => 'success', 'message' => 'Well done! Supplier details updated successfully'], 201);
        } 
        catch(\Exception $e){
            DB::rollBack();
            return response()->json(['supplierStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        } 
    }

    /**
     * Remove the specified supplier from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $supplierCompany = UserCompany::where('user_id', $id)->delete();
            
            $supplier        = User::destroy($id);

            DB::commit();
            return response()->json(['deletedSupplierStatus' => 'success', 'message' => 'Supplier details deleted successfully'], 201);
        }   
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json(['deletedSupplierStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
