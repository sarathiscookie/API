@extends('admin.layouts.app')

@section('content')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
    	<nav aria-label="breadcrumb">
    		<ol class="breadcrumb">
    			<li class="breadcrumb-item"><a href="/admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
    			<li class="breadcrumb-item"><a href="/admin/dashboard/manager/list"><i class="fas fa-list"></i> Manager</a></li>
    			<li class="breadcrumb-item active" aria-current="page"><i class="fas fa-user-plus"></i> Add</li>
    		</ol>
    	</nav>

    	<div class="card border-primary">
    		<div class="card-header bg-primary">
    			Create Manager
    		</div>
    		<div class="card-body">
    			<form>
    				<div class="form-row">
    					<div class="form-group col-md-6">
    						<label for="name">Name</label>
    						<input type="text" class="form-control" id="name" placeholder="Name">
    					</div>
    					<div class="form-group col-md-6">
    						<label for="email">Email</label>
    						<input type="email" class="form-control" id="email" placeholder="Email">
    					</div>
    				</div>

    				<div class="form-row">
    					<div class="form-group col-md-6">
    						<label for="phone">Phone</label>
    						<input type="text" class="form-control" id="phone" placeholder="Phone">
    					</div>
    					<div class="form-group col-md-6">
    						<label for="street">Street</label>
    						<input type="text" class="form-control" id="street" placeholder="Street">
    					</div>
    				</div>

    				<div class="form-row">
    					<div class="form-group col-md-6">
    						<label for="city">City</label>
    						<input type="text" class="form-control" id="city" placeholder="City">
    					</div>
    					<div class="form-group col-md-4">
    						<label for="state">Country</label>
    						<select id="state" class="form-control">
    							<option selected>Choose Country</option>
    							<option>...</option>
    						</select>
    					</div>
    					<div class="form-group col-md-2">
    						<label for="zip">Zip</label>
    						<input type="text" class="form-control" id="zip" placeholder="Zip">
    					</div>
    				</div>

    				<div class="form-row">
    					<div class="form-group col-md-4">
    						<label for="password">Password</label>
    						<input type="password" class="form-control" id="password" placeholder="Password">
    					</div>
    					<div class="form-group col-md-4">
    						<label for="password-confirm">Confirm Password</label>
    						<input type="password" class="form-control" id="password-confirm" placeholder="Password">
    					</div>
    					<div class="form-group col-md-4">
    						<label for="company">Company</label>
    						<select id="company" class="form-control">
    							<option selected>Choose Company</option>
    							<option>...</option>
    						</select>
    					</div>
    				</div>

    				<button type="button" class="btn btn-primary btn-lg btn-block">Create Manager</button>
    			</form>
    		</div>
    	</div>
    </main>
@endsection
