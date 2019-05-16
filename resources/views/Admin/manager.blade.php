@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-list"></i> Manager List</li>
        </ol>
      </nav>

      <div class="card border-primary">
        <div class="card-header bg-primary">
          Manager List
        </div>

        <div class="card-body">
          <div class="text-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createManagerModal"><i class="fas fa-user-plus"></i> Create Manager</button>
            <hr>
          </div>
          
          <div class="table-responsive">
            @if (session()->has('successStoreManager'))
            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> 
              {{ session()->get('successStoreManager') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>  
            @endif

            @if (session()->has('successUpdateManager'))
            <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> 
              {{ session()->get('successUpdateManager') }}
              <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>  
            @endif

            <div class="responseMessage"></div>

            <table id="datatable_list" class="table table-bordered table-hover display" style="width:100%">
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
  
      <!-- Create manager modal -->
      <div class="modal fade" id="createManagerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Create Manager</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">

              @if (session()->has('failureStoreManager'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> 
                {{ session()->get('failureStoreManager') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>  
              @endif

              <form method="POST" action="{{ route('admin.dashboard.manager.store') }}">
                @csrf

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="name">Name</label>

                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" autofocus>

                    @error('name')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="form-group col-md-6">
                    <label for="email">Email</label>

                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email">

                    @error('email')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="phone">Phone</label>

                    <input id="phone" type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}" maxlength="20" autocomplete="phone">

                    @error('phone')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="form-group col-md-6">
                    <label for="street">Street</label>

                    <input id="street" type="text" class="form-control @error('street') is-invalid @enderror" name="street" value="{{ old('street') }}" maxlength="255" autocomplete="street">

                    @error('street')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="city">City</label>

                    <input id="city" type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city') }}" maxlength="255" autocomplete="city">

                    @error('city')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="form-group col-md-4">
                    <label for="country">Country</label>

                    <select id="country" class="form-control @error('country') is-invalid @enderror" name="country">
                      <option value="">Choose...</option>
                      <option value="de" @if(old('country') === 'de') selected @endif>Germany</option>
                    </select>

                    @error('country')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="form-group col-md-2">
                    <label for="zip">Zip</label>

                    <input id="zip" type="text" class="form-control @error('zip') is-invalid @enderror" name="zip" value="{{ old('zip') }}" maxlength="20" autocomplete="zip">

                    @error('zip')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-4">
                    <label for="password">Password</label>

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" maxlength="255">

                    @error('password')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                  <div class="form-group col-md-4">
                    <label for="password-confirm">Confirm Password</label>

                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation">
                  </div>
                  <div class="form-group col-md-4">
                    <label for="company">Company</label>
                    <select id="company" class="form-control @error('company') is-invalid @enderror" name="company">
                      <option value="">Choose...</option>
                      <option value="tcs" @if(old('company') === 'tcs') selected @endif>TCS</option>
                    </select>

                    @error('company')
                    <span class="invalid-feedback" role="alert">
                      <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                  </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Create Manager</button>
              </form>
            </div>

          </div>
        </div>
      </div>
    </main>
@endsection
