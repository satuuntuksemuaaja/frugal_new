@extends('layouts.main', [
'title' => "Create New Store",
'crumbs' => [
    ['text' => "Stores", 'url' => "/admin/stores"],
    ['text' =>  "Create"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card groupBody">
                    <div class="card-body">

                      <form id="form_create_location" role="form" action="{{ route('stores.store') }}" method="post">
                      {{ csrf_field() }}

                      <div class="form-group form-row ">
                        <label for="name" class="col-md-4 control-label">
                          Name:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="name" type="text" id="name" value="{{ old('name') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the name of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          Address:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="address" type="text" id="address" value="{{ old('address') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the address of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          City:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="city" type="text" id="city" value="{{ old('city') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the city of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="state" class="col-md-4 control-label">
                          State:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="job_state" type="text" id="state" value="{{ old('state') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the state of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="state" class="col-md-4 control-label">
                          Number:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="number" type="text" id="number" value="{{ old('number') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the number of the store</p>
                        </div>
                      </div>

                      <input class="btn btn-primary uiblock !important" data-message="Creating Store.." name="" type="submit" value="Create Store">

                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
