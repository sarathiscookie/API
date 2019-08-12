@extends('admin.layouts.app')

@section('title', 'Supplier List')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-tie"></i> Supplier List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Supplier List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createSupplierModal"><i class="fas fa-user-plus"></i> Create Supplier</button>
            <hr>
          </div>
          
          <div class="table-responsive">

            <div class="responseSupplierMessage"></div>

            <table id="supplier_list" class="table table-bordered table-hover display" style="width:100%">
              <thead class="thead-light">
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Active</th>
                  <th>Actions</th>
                </tr>
              </thead>

              <tbody></tbody>

              <tfoot>
                <td></td>
                <th><input type="text" id="1"  class="form-control input-sm search-input" placeholder="Search Name"></th>
                <td>
                  <select class="form-control input-sm search-input" id="2">
                    <option value="">All</option>
                    <option value="yes">Active</option>
                    <option value="no">Not Active</option>
                  </select>
                </td>
                <td></td>
              </tfoot>

            </table>

            <!-- Export buttons are append here -->
            <div id="buttons"></div>

          </div>
        </div>
      </div>
  
      <!-- Create supplier modal -->
      <div class="modal fade" id="createSupplierModal" tabindex="-1" role="dialog" aria-labelledby="createSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="createSupplierModalLabel">Create Supplier</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              <div class="supplierValidationAlert"></div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="supplier_name">Name <span class="required">*</span></label>
                  <input id="supplier_name" type="text" class="form-control" name="supplier_name" maxlength="255">
                </div>

                <div class="form-group col-md-6">
                  <label for="email">Email <span class="required">*</span></label>
                  <input id="email" type="email" class="form-control" name="email" maxlength="255">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="supplier_company">Company <span class="required">*</span></label>
                  <select id="supplier_company" class="form-control" name="supplier_company[]" multiple="multiple">
                    @isset($companies)
                      @foreach( $companies as $company )
                          <option value="{{ $company->id }}">{{ $company->company }}</option>
                      @endforeach
                    @endisset
                  </select>
                </div>

                <div class="form-group col-md-6">
                  <label for="supplier_phone">Phone <span class="required">*</span></label>
                  <input id="supplier_phone" type="text" class="form-control" name="supplier_phone" maxlength="20">
                </div>
                
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="supplier_country">Country <span class="required">*</span></label>
                  <select id="supplier_country" class="form-control" name="supplier_country">
                    <option value="">Choose...</option>
                    @foreach ($countries as $country)
                      <option value="{{$country->id}}">{{$country->name}}</option>
                    @endforeach
                  </select>
                </div>

                <div class="form-group col-md-6">
                  <label for="supplier_city">City <span class="required">*</span></label>
                  <input id="supplier_city" type="text" class="form-control" name="supplier_city" maxlength="255">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="supplier_street">Street <span class="required">*</span></label>
                  <input id="supplier_street" type="text" class="form-control" name="supplier_street" maxlength="255">
                </div>

                <div class="form-group col-md-6">
                  <label for="supplier_zip">Zip <span class="required">*</span></label>
                  <input id="supplier_zip" type="text" class="form-control" name="supplier_zip" maxlength="20">
                </div>
              </div>

              <button type="button" class="btn btn-primary btn-lg btn-block createSupplier"><i class="fas fa-user-plus"></i> Create Supplier</button>
            </div>

          </div>
        </div>
      </div>

    </main>
@endsection
