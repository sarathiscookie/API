<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Traits\CountryTrait;
use App\Http\Requests\Admin\CompanyRequest;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use CountryTrait;

    /**
     * Show the company view page. Passing all active countries into the company view page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Country trait to fetch the countries.
        $countries = $this->country();

        return view('admin.company', ['countries' => $countries]);
    }

    /**
     * Show the companies data into the view page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function datatable(Request $request)
    {
        // Getting all the http request.
        $params = $request->all();

        $columns = array(
            1 => 'company',
            2 => 'active',
        );

        // Getting count of companies.
        $totalData = Company::select('id', 'company', 'active')
        ->count();

        // Query to select companies.
        $q         = Company::select('id', 'company', 'active');

        $totalFiltered = $totalData;
        $limit         = (int)$request->input('length');
        $start         = (int)$request->input('start');
        $order         = $columns[$params['order'][0]['column']]; // contains column index.
        $dir           = $params['order'][0]['dir']; // contains order such as asc/desc.

        // If the request has a search value (company name), this query will execute and fetch the results.
        if(!empty($request->input('search.value'))) {
            $this->searchCompany($q, $request->input('search.value'));
        }

        // If the table has footer column value (company name), this query will execute and fetch the results based on the company name.
        if( !empty($params['columns'][1]['search']['value']) ) {
            $this->tfootCompany($q, $params['columns'][1]['search']['value']);
        }

        // If the table has footer column value (company status), this query will execute and fetch results based on the company status.
        if( !empty($params['columns'][2]['search']['value']) ) {
            $this->tfootCompanyStatus($q, $params['columns'][2]['search']['value']);
        }

        // Query scripts ends here.
        $companiesLists = $q->skip($start)
            ->take($limit)
            ->orderBy($order, $dir)
            ->get();

        $data = [];

        if(!empty($companiesLists)) {

            foreach ($companiesLists as $key=> $companiesList) {
                $nestedData['hash'] = '<input class="checked" type="checkbox" name="id[]" value="'.$companiesList->id.'" />';
                $nestedData['company'] = $companiesList->company;
                $nestedData['active'] = $this->companyStatusHtml($companiesList->id, $companiesList->active);
                $nestedData['actions'] = $this->editCompanyModel($companiesList->id);
                $data[] = $nestedData;
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
     * Function to search companies based on the company name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function searchCompany($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('company', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('company', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * Function to filter companies based on the company name.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootCompany($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('company', 'like', "%{$searchData}%");
        });

        // Total filtered count
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('company', 'like', "%{$searchData}%");
        })
        ->count();

        return $this;    
    }

    /**
     * Function to filter companies based on the company status.
     * @param  object $q
     * @param  string $searchData
     * @return \Illuminate\Http\Response
     */
    public function tfootCompanyStatus($q, $searchData)
    {
        $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        });

        // Total filtered count
        $totalFiltered = $q->where(function($query) use ($searchData) {
            $query->where('active', "{$searchData}");
        })
        ->count();

        return $this;    
    }

    /**
     * HTML group button to change company status (Active or Disable).
     * @param  int $id
     * @param  string $oldStatus
     * @return \Illuminate\Http\Response
     */
    public function companyStatusHtml($id, $oldStatus)
    {
        $checked = ($oldStatus === 'yes') ? 'checked' : "";

        $html    = '<label class="switch" data-companystatusid="'.$id.'">
            <input type="checkbox" class="buttonStatus" '.$checked.'>
            <span class="slider round"></span>
            </label>';

        return $html;
    }

    /**
     * Update company status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request)
    {
        try {
            // Fetching company details based on requested company id.
            $company         = Company::findOrFail($request->companyStatusId);

            $company->active = $request->newStatus;

            $company->save();

            return response()->json(['companyStatusChange' => 'success', 'message' => 'Company status updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['companyStatusChange' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }

    /**
     * Model to edit company data
     * @param  integer $companyId
     * @return \Illuminate\Http\Response
     */
    public function editCompanyModel($companyId)
    {
        try {
            // Fetching company details based on company id
            $company            = $this->edit($companyId);
            
            $countryOptions     = '';

            foreach($this->country() as $country) {
                $countrySelected = ($company->country->id === $country->id) ? 'selected' : '';
                $countryOptions .= '<option value="'.$country->id.'" '.$countrySelected.'>'.$country->name.'</option>';
            }

            $html               = '<a class="btn btn-secondary btn-sm editCompany cursor" data-companyid="'.$company->id.'" data-toggle="modal" data-target="#editCompanyModal_'.$company->id.'"><i class="fas fa-cog"></i></a>
            <div class="modal fade" id="editCompanyModal_'.$company->id.'" tabindex="-1" role="dialog" aria-labelledby="editCompanyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="editCompanyModalLabel">Edit Company Details</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
            </div>

            <div class="modal-body">
            <div class="companyUpdateValidationAlert"></div>
            <div class="text-right">
            <a href="" class="btn btn-danger btn-sm deleteCompany" data-deletecompanyid="'.$company->id.'"><i class="fas fa-trash-alt"></i> Delete</a>
            <hr>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="createdon">Created On:</label>
            <div class="badge badge-secondary" style="width: 6rem;">
            '.date('d.m.y', strtotime($company->created_at)).'
            </div>
            

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="name">Company <span class="required">*</span></label>
            <input id="company_'.$company->id.'" type="text" class="form-control" name="company" value="'.$company->company.'" autocomplete="company" maxlength="255">

            </div>
            <div class="form-group col-md-6">

            <label for="phone">Phone <span class="required">*</span></label>
            <input id="phone_'.$company->id.'" type="text" class="form-control" name="phone" value="'.$company->phone.'" maxlength="20" autocomplete="phone">

            </div>
            </div>

            <div class="form-row">
            <div class="form-group col-md-6">

            <label for="street">Street <span class="required">*</span></label>
            <input id="street_'.$company->id.'" type="text" class="form-control" name="street" value="'.$company->street.'" maxlength="255" autocomplete="street">

            </div>
            <div class="form-group col-md-6">

            <label for="city">City <span class="required">*</span></label>
            <input id="city_'.$company->id.'" type="text" class="form-control" name="city" value="'.$company->city.'" maxlength="255" autocomplete="city">

            </div>
            </div>

            <div class="form-row">
            
            <div class="form-group col-md-6">

            <label for="country">Country <span class="required">*</span></label>
            <select id="country_'.$company->id.'" class="form-control" name="country">
            <option value="">Choose Country</option>
            '.$countryOptions.'
            </select>

            </div>
            <div class="form-group col-md-6">

            <label for="zip">Zip <span class="required">*</span></label>
            <input id="zip_'.$company->id.'" type="text" class="form-control" name="zip" value="'.$company->postal.'" maxlength="20" autocomplete="zip">

            </div>
            </div>

            <button type="button" class="btn btn-primary btn-lg btn-block updateCompany_'.$company->id.'"><i class="far fa-edit"></i> Update Company</button>

            </div>

            </div>
            </div>
            </div>';

            return $html;
            
        } 
        catch(\Exception $e){
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
     * Store companies into companies table.
     *
     * @param  \App\Http\Requests\Admin\CompanyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        try {
            $company               = new Company;
            $company->company      = $request->company;
            $company->phone        = $request->phone;
            $company->street       = $request->street;
            $company->city         = $request->city;
            $company->country_id   = $request->country;
            $company->postal       = $request->zip;
            $company->active       = 'no';
            $company->save();

            return response()->json(['companyStatus' => 'success', 'message' => 'Well done! Company created successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['companyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
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
     * Get the company for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Fetching company details based on company id.
        $company = Company::findOrFail($id);

        return $company;
    }

    /**
     * Update the company in storage.
     *
     * @param  \App\Http\Requests\Admin\CompanyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(CompanyRequest $request)
    {
        try {
            // Fetching company details based on requested company id.
            $company             = Company::find($request->companyid);
            $company->company    = $request->company; 
            $company->phone      = $request->phone;
            $company->street     = $request->street; 
            $company->city       = $request->city; 
            $company->country_id = $request->country;
            $company->postal     = $request->zip;
            $company->save();

            return response()->json(['companyStatusUpdate' => 'success', 'message' => 'Well done! Company details updated successfully'], 201);
        } 
        catch(\Exception $e){
            return response()->json(['companyStatusUpdate' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        } 
    }

    /**
     * Remove the company from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Company::destroy($id);

            return response()->json(['deletedCompanyStatus' => 'success', 'message' => 'Company details deleted successfully'], 201);
        }
        catch(\Exception $e) {
            return response()->json(['deletedCompanyStatus' => 'failure', 'message' => 'Whoops! Something went wrong'], 404);
        }
    }
}
