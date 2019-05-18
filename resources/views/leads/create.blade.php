@extends('layouts.main', [
'title' => "Create new Lead",
'crumbs' => [
    ['text' => "Leads", 'url' => "/admin/leads"],
    ['text' =>  "Create"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card groupBody">
                    <div class="card-body">

                      <form id="form_create_lead" role="form" action="{{ route('leads.store') }}" method="post">
                      {{ csrf_field() }}

                      <div class="form-group form-row ">
                          <p>Info</p>
                          <p>When adding a lead, you can either select an existing customer or create a new one.
                            <b>
                                To create a new customer with existing information, select the customer, and add a new name. The rest of the information will copy to the new customer, contact and lead.
                            </b>
                          </p>
                      </div>

                      <div class="form-group form-row ">
                        <label for="name" class="col-md-4 control-label">
                          Existing Customer:
                        </label>
                        <div class="col-md-8">
                            <select class="form-control" name="customer_id" id="customer_id">
                              <option value="">-- New Customer -- </option>
                              @foreach($customers as $customer)
                                @if(old('customer_id') == $customer->id)
                                  <option value="{{ $customer->id }}" selected>{{ $customer->name }}</option>
                                @else
                                  <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endif
                              @endforeach
                            </select>
                            <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select existing customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="name" class="col-md-4 control-label">
                          Name:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="name" type="text" id="name" value="{{ old('name') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the name of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          Address:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="address" type="text" id="address" value="{{ old('address') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the address of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          City:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="city" type="text" id="city" value="{{ old('city') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the city of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="state" class="col-md-4 control-label">
                          State:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="state" type="text" id="state" value="{{ old('state') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the state of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="zip" class="col-md-4 control-label">
                          Zip:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="zip" type="text" id="zip" value="{{ old('zip') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the zip of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="job_address" class="col-md-4 control-label">
                          Job Address:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="job_address" type="text" id="job_address" placeholder="Leave blank if same" value="{{ old('job_address') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job address of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="job_address" class="col-md-4 control-label">
                          Job City:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="job_city" type="text" id="job_city" placeholder="Leave blank if same" value="{{ old('job_city') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job city of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="job_state" class="col-md-4 control-label">
                          Job State:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="job_state" type="text" id="job_state" value="{{ old('job_state') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job state of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="job_zip" class="col-md-4 control-label">
                          Job Zip:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="job_zip" type="text" id="job_zip" value="{{ old('job_zip') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job zip of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="email" class="col-md-4 control-label">
                          E-mail Address:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="email" type="email" id="email" value="{{ old('email') }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the email of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="home" class="col-md-4 control-label">
                          Home Phone:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="home" type="home" id="home" value="{{ old('home') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the home phone of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="mobile" class="col-md-4 control-label">
                          Mobile (SMS):
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="mobile" type="mobile" id="mobile" value="{{ old('mobile') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the mobile of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="alternate" class="col-md-4 control-label">
                          Alternate Phone:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="alternate" type="alternate" id="alternate" value="{{ old('alternate') }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the alternate phone of the customer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="source_id" class="col-md-4 control-label">
                          Lead Source:
                        </label>
                        <div class="col-md-8">
                            <select class="form-control" name="source_id" id="source_id" required>
                              <option value="">-- Select Lead Source -- </option>
                              @foreach($leadSources as $leadSource)
                                @if(old('source_id') == $leadSource->id)
                                  <option value="{{ $leadSource->id }}" selected>{{ $leadSource->name }}</option>
                                @else
                                  <option value="{{ $leadSource->id }}">{{ $leadSource->name }}</option>
                                @endif
                              @endforeach
                            </select>
                            <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select existing lead source</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="designer_id" class="col-md-4 control-label">
                          Designer:
                        </label>
                        <div class="col-md-8">
                            <select class="form-control" name="user_id" id="user_id" required>
                              <option value="">-- Select Designer -- </option>
                                <option value="5">Rich Bishop</option>
                              @foreach($designers as $designer)

                                @if(old('designer_id') == $designer->id)
                                  <option value="{{ $designer->id }}" selected>{{ $designer->name }}</option>
                                @else
                                  <option value="{{ $designer->id }}">{{ $designer->name }}</option>
                                @endif
                              @endforeach
                            </select>
                            <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select existing designer</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="location" class="col-md-4 control-label">
                          Showroom Location:
                        </label>
                        <div class="col-md-8">
                            <select class="form-control" name="location_id" id="location_id" required>
                              <option value="">-- Select Location -- </option>
                              @foreach($locations as $location)
                                @if(old('location') == $location)
                                  <option value="{{ $location->id }}" selected>{{ $location->name }}</option>
                                @else
                                  <option value="{{ $location->id }}">{{ $location->name }}</option>
                                @endif
                              @endforeach
                            </select>
                            <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select existing showroom location</p>
                        </div>
                      </div>

                      <input class="btn btn-primary uiblock !important" data-message="Creating Lead.." name="" type="submit" value="Create Lead">

                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
