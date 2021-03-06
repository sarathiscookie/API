<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManagerRequest;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CountryTrait;
use App\User;
use App\UserCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    use CompanyTrait, CountryTrait;
    /**
     * Show the manager view page. Passing all active countries and companies into the manager view page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Company trait to fetch the companies.
        $companies = $this->company();

        // Country trait to fetch the countries.
        $countries = $this->country();

        return view('admin.manager', ['companies' => $companies, 'countries' => $countries]);
    }

    /**
     * Show the managers data in to view page.
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

        // Getting count of users.
        $totalData = User::select('id', 'name', 'active')
            ->manager()
            ->count();

        // Query to select users where role is manager.
        $q         = User::select('id', 'name', 'active')
            ->manager();

        $totalFiltered = $totalData;

        $limit         = (int) $request->input('length');

        $start         = (int) $request->input('start');

        $order         = $columns[$params['order'][0]['column']]; //contains column index

        $dir           = $params['order'][0]['dir']; //contains order such as asc/desc

        // If the request has a search value (name), this query will execute and fetch the results.
        if (!empty($request->input('search.value'))) {
            $this->searchUserName($q, $request->input('search.value'));
        }

        // If the table has footer column value (name), this query will execute and fetch the results based on name.
        if (!empty($params['columns'][1]['search']['value'])) {
            $this->tfootUserName($q, $params['columns'][1]['search']['value']);
        }

        // If the table has footer column value (status), this query will execute and fetch the results based on status.
        if (!empty($params['columns'][2]['search']['value'])) {
            $this->tfootUserStatus($q, $params['columns'][2]['search']['value']);
        }

        // Query scripts ends here.
        $managerLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if (!empty($managerLists)) {

            foreach ($managerLists as $key => $managerList) {
                $nestedData['hash']       = '<input class="checked" type="checkbox" name="id[]" value="' . $managerList->id . '" />';
                $nestedData['name']       = $managerList->name;
                $nestedData['active']     = $this->userStatusHtml($managerList->id, $managerList->active);
                $nestedData['actions']    = $this->editMangerModel($managerList->id);
                $data[]                   = $nestedData;
            }
        }

        // Preparing array to send the response in JSON format to draw the data on datatable.
        $json_data = array(
            'draw'            => (int) $params['draw'],
            'recordsTotal'    => (int) $totalData,
            'recordsFiltered' => (int) $totalFiltered,
            'data'            => $data
        );

        return response()->json($json_data);
    }

    /**
     * Function to search managers based on the name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchUserName($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function ($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
            ->count();

        return $this;
    }

    /**
     * Function to filter managers based on the name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootUserName($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function ($query) use ($searchData) {
            $query->where('name', 'like', "%{$searchData}%");
        })
            ->count();

        return $this;
    }

    /**
     * Function to filter managers based on the status.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootUserStatus($q, $searchData)
    {
        $q->where(function ($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        });

        // Total filtered count.
        $totalFiltered = $q->where(function ($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        })
            ->count();

        return $this;
    }

    /**
     * HTML group button to change manager status 
     * @param  int $id
     * @param  string $oldStatus  
     * @return \Illuminate\Http\Response
     */
    public function userStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";
        $html    = '<label class="switch" data-userid="' . $id . '">
            <input type="checkbox" class="buttonStatus" ' . $checked . '>
            <span class="slider round"></span>
            </label>';

        return $html;
    }

    /**
     * Update manager status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {

            // Fetching manager details based on requested manager id.
            $user         = User::findOrFail($request->userId);

            $user->active = $request->newStatus;

            $user->save();

            return response()->json(['managerStatusChange' => 'success', 'message' => 'User status updated successfully'], 201);
        } 
        catch (\Exception $e) {
            return response()->json(['managerStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit manager data
     * @param  integer $mangerId
     * @return \Illuminate\Http\Response
     */
    public function editMangerModel($mangerId)
    {
        try {
            // Fetching manager details based on manager id.
            $user           = $this->edit($mangerId);

            //Getting countries and selecting matcing countries
            $countryOptions = '';
            foreach ($this->country() as $country) {
                $countrySelected = ($user->country->id === $country->id) ? 'selected' : '';
                $countryOptions .= '<option value="' . $country->id . '" ' . $countrySelected . '>' . $country->name . '</option>';
            }

            //Getting companies and selecting matching companies
            $managerCompanyId = [];
            foreach ($user->userCompanies as $managerCompany) {
                $managerCompanyId[] = $managerCompany->company_id;
            }

            $companyOptions = '';
            foreach ($this->company() as $company) {
                $companySelected = (in_array($company->id, $managerCompanyId)) ? 'selected' : '';
                $companyOptions .= '<option value="' . $company->id . '" ' . $companySelected . '>' . $company->company . '</option>';
            }

            $country  = ($user->country === 'de') ? 'selected' : '';

            $html     = '<a class="btn btn-secondary btn-sm editManager cursor" data-managerid="' . $user->id . '" data-toggle="modal" data-target="#editManagerModal_' . $user->id . '"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editManagerModal_' . $user->id . '" tabindex="-1" role="dialog" aria-labelledby="editManagerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editManagerModalLabel">Edit Manager Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="managerUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteEvent" data-id="' . $user->id . '"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="email">Email:</label>
            <div class="badge badge-secondary text-wrap" style="width: 6rem;">
            ' . $user->email . '
            </div>
            

            </div>
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            ' . date('d.m.y', strtotime($user->created_at)) . '
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="name">Name <span class="required">*</span></label>
            <input id="name_' . $user->id . '" type="text" class="form-control" name="name" value="' . $user->name . '" autocomplete="name" maxlength="255">

            </div>
            <div class="form-group col-md-6">

            <label for="phone">Phone <span class="required">*</span></label>
            <input id="phone_' . $user->id . '" type="text" class="form-control" name="phone" value="' . $user->phone . '" maxlength="20" autocomplete="phone">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="manger_company">Company <span class="required">*</span></label>
            <select id="manger_company_' . $user->id . '" class="form-control" name="manger_company[]" multiple="multiple">
            ' . $companyOptions . '
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="street">Street <span class="required">*</span></label>
            <input id="street_' . $user->id . '" type="text" class="form-control" name="street" value="' . $user->street . '" maxlength="255" autocomplete="street">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="city">City <span class="required">*</span></label>
            <input id="city_' . $user->id . '" type="text" class="form-control" name="city" value="' . $user->city . '" maxlength="255" autocomplete="city">

            </div>
            <div class="form-group col-md-4">

            <label for="country">Country <span class="required">*</span></label>
            <select id="country_' . $user->id . '" class="form-control" name="country">
            <option value="">Choose Country</option>
            ' . $countryOptions . '
            </select>

            </div>
            <div class="form-group col-md-2">

            <label for="zip">Zip <span class="required">*</span></label>
            <input id="zip_' . $user->id . '" type="text" class="form-control" name="zip" value="' . $user->postal . '" maxlength="20" autocomplete="zip">

            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateManager_' . $user->id . '"><i class="fas fa-user-edit"></i> Update Manager</button>

            </div>

            </div>
            </div>
            </div>';

            return $html;
        } 
        catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Show the form to create a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created manager into storage.
     *
     * @param  \App\Http\Requests\Admin\ManagerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ManagerRequest $request)
    {
        try {
            $user               = new User;
            $user->name         = $request->name;
            $user->password     = Hash::make($request->password);
            $user->email        = $request->email;
            $user->username     = $request->username;
            $user->phone        = $request->phone;
            $user->street       = $request->street;
            $user->city         = $request->city;
            $user->country_id   = $request->country;
            $user->postal       = $request->zip;
            $user->active       = 'no';
            $user->role         = 'manager';
            $user->save();

            // Storing user manager and company relation in to user_companies storage.
            foreach ($request->manager_company as $company) {
                $userCompany             = new UserCompany;
                $userCompany->user_id    = $user->id;
                $userCompany->company_id = $company;
                $userCompany->save();
            }

            return response()->json(['managerStatus' => 'success', 'message' => 'Well done! User created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['managerStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
     * Get the manager for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetching manager details based on manager id.
        $user = User::manager()->findOrFail($id);
        
        return $user;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Admin\ManagerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ManagerRequest $request)
    {
        DB::beginTransaction();
        try {
            // Delete and store company id in user companies table
            UserCompany::where('user_id', $request->managerid)->delete();

            $manager             = User::manager()->find($request->managerid);
            $manager->name       = $request->name;
            $manager->phone      = $request->phone;
            $manager->street     = $request->street;
            $manager->city       = $request->city;
            $manager->country_id = $request->country;
            $manager->postal     = $request->zip;
            $manager->save();

            foreach ($request->manager_company as $company) {
                $userCompany             = new UserCompany;
                $userCompany->user_id    = $manager->id;
                $userCompany->company_id = $company;
                $userCompany->save();
            }

            DB::commit();
            return response()->json(['managerStatusUpdate' => 'success', 'message' => 'Well done! User details updated successfully'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['managerStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
            // Remove manager data from user_companies storage.
            $managerCompany = UserCompany::where('user_id', $id)->delete();

            // Remove manager data from user storage.
            $user           = User::destroy($id);

            DB::commit();

            return response()->json(['deletedManagerStatus' => 'success', 'message' => 'Manager details deleted successfully'], 201);
        } 
        catch (\Exception $e) {
            
            DB::rollBack();

            return response()->json(['deletedManagerStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
