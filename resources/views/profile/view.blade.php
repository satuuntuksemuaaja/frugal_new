@extends('layouts.main', [
'title' => "Customer Profile | " . $customer->name,
'crumbs' => [
    ['text' => "Customer Profile"]
]])
@section('content')
    <div class="row">
      <div class="col-lg-3">
          <div class="btn-group">
            @if($customer->quotes)
              @foreach($customer->quotes AS $quote)
                @if ($quote->files)
                  <a href="#" class="btn btn-info" onclick="ShowModalDrawing('{{ $quote->id }}')"><h3><i class="fa fa-picture-o"></i></h3>Drawings/Files<br/>(Quote: {{ $quote->id }})</a>
                @endif
              @endforeach
            @endif
          </div>
      </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card groupBody">
                <div class="card-header bg-primary text-white"><b>Customer Details</b></div>
                <div class="card-body">
                  <table class="table table-striped mt-2 table-bordered" id="customerDetailsTable">
                      <tbody id="body_customer_details"></tbody>
                  </table>
                  <br/>
                  <h4>Customer Contacts</h4>
                  <table class="table table-striped mt-2 table-bordered" id="customerContactsTable">
                      <tbody id="body_customer_contacts">
                      </tbody>
                  </table>
                </div>
            </div>
        </div> <!-- card -->
        <br/>
        <div class="card">
            <div class="card groupBody">
            <div class="card-header bg-info text-white"><b>Leads</b></div>
            <div class="card-body">
              <table class="table table-striped mt-2 table-bordered" id="leadsTable">
                  <thead>
                      <th>Age</th>
                      <th>Status</th>
                      <th>Designer</th>
                      <th>Lead Source</th>
                  </thead>
                  <tbody id="body_customer_leads"></tbody>
              </table>
            </div>
        </div>
    </div> <!-- card -->
    <br/>
    <div class="card">
        <div class="card groupBody">
        <div class="card-header bg-warning text-white"><b>Quote</b></div>
        <div class="card-body">
          <table class="table table-striped mt-2 table-bordered" id="quoteTable">
              <thead>
                  <th>Quote</th>
                  <th>Type</th>
                  <th>Status</th>
                  <th>Accepted</th>
              </thead>
              <tbody id="body_customer_quotes"></tbody>
          </table>
        </div>
      </div>
    </div> <!-- card -->
  </div> <!-- col 4 -->
  <div class="col-lg-4">
    <div class="card">
    <div class="card groupBody">
    <div class="card-header bg-info text-white"><b>Jobs</b></div>
    <div class="card-body" style="overflow-x:auto;">
      <a href="{{ route('customer_job_multiple_auth', ['id' => $customer->id]) }}" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Multiple Authorization</a>
      <table class="table table-striped mt-2 table-bordered" id="jobsTable">
          <thead>
              <th>#</th>
              <th>Contractor</th>
              <th>Starts</th>
              <th>Notes</th>
              <th>Incoming Notes</th>
          </thead>
          <tbody id="body_customer_jobs"></tbody>
      </table>
    </div>
    </div>
    </div> <!-- card -->
    <br/>
    <div class="card">
    <div class="card groupBody">
    <div class="card-header bg-danger text-white"><b>Final Touch</b></div>
    <div class="card-body" style="overflow-x:auto;">
      <table class="table table-striped mt-2 table-bordered" id="finalTouchTable">
          <thead>
              <th>Pre-Assign</th>
              <th>Pre-Schedule</th>
              <th>Assigned</th>
              <th>Scheduled</th>
              <th>Notes</th>
          </thead>
          <tbody id="body_customer_final_touch"></tbody>
      </table>
      </div>
    </div>
    </div> <!-- card -->
    <br/>
    <div class="card">
    <div class="card groupBody">
    <div class="card-header bg-danger text-white"><b>Warranty Items</b></div>
    <div class="card-body" style="overflow-x:auto;">
      <table class="table table-striped mt-2 table-bordered" id="WarrantyTable">
          <thead>
              <th>Pre-Assign</th>
              <th>Pre-Schedule</th>
              <th>Assigned</th>
              <th>Scheduled</th>
              <th>Notes</th>
          </thead>
          <tbody id="body_customer_warranty"></tbody>
      </table>
      </div>
    </div>
    </div> <!-- card -->

</div> <!-- col 4 -->
<div class="col-lg-4">
  <div class="card">
  <div class="card groupBody">
  <div class="card-header bg-default text-gray"><b>Customer Notes</b></div>
  <div class="card-body">
    <table class="table table-striped mt-2 table-bordered" id="customerNotesTable">
        <thead>
            <th>From</th>
            <th>Note</th>
        </thead>
        <tbody id="body_customer_notes"></tbody>
    </table>
  </div>
  <div class="card-body">
    <div class="form-group form-row ">
      <label for="state" class="col-md-2 control-label">
        Notes:
      </label>
      <div class="col-md-10">
          <textarea class="form-control" name="note" type="text" id="note"></textarea>
      </div>
    </div>
  </div>
  <div class="card-footer">
    <a href="#" class="btn btn-primary" onclick="AddNotes();"><i class="fa fa-check"></i> Save Notes</a>
  </div>
  </div>
  </div> <!-- card -->
  <br/>
  <div class="card">
  <div class="card groupBody">
  <div class="card-header bg-primary text-white"><b>Task List for Customer</b></div>
  <div class="card-body">
    <table class="table table-striped mt-2 table-bordered" id="tasksTable">
        <thead>
            <th>Task</th>
            <th>Assigned</th>
            <th>Due</th>
        </thead>
        <tbody id="body_customer_tasks"></tbody>
    </table>
  </div>
  <div class="card-footer">
    <a href="#" class="btn btn-primary" onclick="ShowModalAddTask('{{ $customer->id }}');"><i class="fa fa-plus"></i> Add Task</a>
  </div>
  </div>
  </div>
</div>
</div> <!-- row -->
@endsection
@section('javascript')
<script>
$(function(){
  LoadCustomerDetails();
  LoadCustomerContacts();
  LoadLeads();
  LoadQuotes();
  LoadJobs();
  LoadFinalTouch();
  LoadWarranty();
  LoadCustomerNotes();
  LoadCustomerTasks();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if($request->get('upload_file') == 1)
    ShowModalDrawing({{ $request->quote_id }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif
});

function ShowModalDrawing(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('File/Designs');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table fileTable table-striped mt-2" id="fileTable">\
                                    <thead>\
                                    <th></th>\
                                    <th>Description</th>\
                                    <th>Uploaded By</th>\
                                    <th>Attach to Contractors</th>\
                                    <th>Delete</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                              <form id="form_upload_file" role="form" action="{{ url('quotes') }}/' + quote_id + '/upload_file" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  File Description:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="description" id="description" required>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="file" class="col-md-4 control-label">\
                                  Select File:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" class="form-control" name="file" id="file" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select the files</p>\
                                </div>\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary">Upload</button>\
                              \
                              </form>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataFiles(quote_id);
}

function LoadDataFiles(quote_id)
{
    $('#fileTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_files') }}",
                  data: {
                          'quote_id': quote_id
                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "fnInitComplete": function(oSettings, json) {
        $('#myModal').modal('show');
      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

function LoadCustomerDetails()
{
  $.ajax({
          url:"{{ route('get_customer_details') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_details').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalEditCustomerName(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Name');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="name" class="col-md-4 control-label">\
                                Name:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" name="name_modal" type="text" id="name_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the name</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerName(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerName(customer_id);
}

function GetCustomerName(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_name') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#name_modal').val(res.name);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerName(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_name') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "name": $('#name_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerAddress(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Address');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="address" class="col-md-4 control-label">\
                                Address:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" address="address_modal" type="text" id="address_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the address</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerAddress(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerAddress(customer_id);
}

function GetCustomerAddress(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_address') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#address_modal').val(res.address);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerAddress(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_address') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "address": $('#address_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerCity(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer City');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="city" class="col-md-4 control-label">\
                                City:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" city="city_modal" type="text" id="city_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the city</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerCity(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerCity(customer_id);
}

function GetCustomerCity(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_city') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#city_modal').val(res.city);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerCity(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_city') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "city": $('#city_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerState(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer State');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="state" class="col-md-4 control-label">\
                                State:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" state="state_modal" type="text" id="state_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the state</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerState(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerState(customer_id);
}

function GetCustomerState(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_state') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#state_modal').val(res.state);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerState(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_state') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "state": $('#state_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerZip(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Zip');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="zip" class="col-md-4 control-label">\
                                Zip:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" zip="zip_modal" type="text" id="zip_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the zip</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerZip(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerZip(customer_id);
}

function GetCustomerZip(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_zip') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#zip_modal').val(res.zip);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerZip(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_zip') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "zip": $('#zip_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerJobAddress(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Job Address');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="job_address" class="col-md-4 control-label">\
                                Job Address:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" job_address="job_address_modal" type="text" id="job_address_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job_address</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerJobAddress(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerJobAddress(customer_id);
}

function GetCustomerJobAddress(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_job_address') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#job_address_modal').val(res.job_address);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerJobAddress(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_job_address') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "job_address": $('#job_address_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerJobCity(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Job City');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="job_city" class="col-md-4 control-label">\
                                Job City:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" job_city="job_city_modal" type="text" id="job_city_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job_city</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerJobCity(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerJobCity(customer_id);
}

function GetCustomerJobCity(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_job_city') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#job_city_modal').val(res.job_city);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerJobCity(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_job_city') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "job_city": $('#job_city_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerJobState(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Job State');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="job_state" class="col-md-4 control-label">\
                                Job State:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" job_state="job_state_modal" type="text" id="job_state_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job_state</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerJobState(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerJobState(customer_id);
}

function GetCustomerJobState(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_job_state') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#job_state_modal').val(res.job_state);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerJobState(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_job_state') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "job_state": $('#job_state_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditCustomerJobZip(customer_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Customer Job Zip');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="job_zip" class="col-md-4 control-label">\
                                Job Zip:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" job_zip="job_zip_modal" type="text" id="job_zip_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the job_zip</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetCustomerJobZip(\'' + customer_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetCustomerJobZip(customer_id);
}

function GetCustomerJobZip(customer_id)
{
  $.ajax({
          url:"{{ route('get_customer_job_zip') }}",
          type:'GET',
          data:{
                "customer_id": customer_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#job_zip_modal').val(res.job_zip);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCustomerJobZip(customer_id)
{
  $.ajax({
          url:"{{ route('set_customer_job_zip') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "customer_id": customer_id,
                "job_zip": $('#job_zip_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerDetails();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function LoadCustomerContacts()
{
  $.ajax({
          url:"{{ route('get_customer_contacts') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_contacts').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalEditContactName(contact_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Contact Name');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="name" class="col-md-4 control-label">\
                                Name:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" name="name_modal" type="text" id="name_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the name</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetContactName(\'' + contact_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetContactName(contact_id);
}

function GetContactName(contact_id)
{
  $.ajax({
          url:"{{ route('get_contact_name') }}",
          type:'GET',
          data:{
                "contact_id": contact_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#name_modal').val(res.name);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetContactName(contact_id)
{
  $.ajax({
          url:"{{ route('set_contact_name') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "contact_id": contact_id,
                "name": $('#name_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerContacts();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditContactEmail(contact_id, number)
{
  removeMessageModal();

  $('.modal-title').html('Set Contact Email');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="email" class="col-md-4 control-label">\
                                Email:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" email="email_modal" type="text" id="email_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the email</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetContactEmail(\'' + contact_id + '\', \'' + number + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetContactEmail(contact_id, number);
}

function GetContactEmail(contact_id, number)
{
  $.ajax({
          url:"{{ route('get_contact_email') }}",
          type:'GET',
          data:{
                "contact_id": contact_id,
                "number": number
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#email_modal').val(res.email);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetContactEmail(contact_id, number)
{
  $.ajax({
          url:"{{ route('set_contact_email') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "contact_id": contact_id,
                "email": $('#email_modal').val(),
                "number": number
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerContacts();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditContactMobile(contact_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Contact Mobile');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="mobile" class="col-md-4 control-label">\
                                Mobile:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" mobile="mobile_modal" type="text" id="mobile_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the mobile</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetContactMobile(\'' + contact_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetContactMobile(contact_id);
}

function GetContactMobile(contact_id)
{
  $.ajax({
          url:"{{ route('get_contact_mobile') }}",
          type:'GET',
          data:{
                "contact_id": contact_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#mobile_modal').val(res.mobile);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetContactMobile(contact_id)
{
  $.ajax({
          url:"{{ route('set_contact_mobile') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "contact_id": contact_id,
                "mobile": $('#mobile_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerContacts();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditContactHome(contact_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Contact Home');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="home" class="col-md-4 control-label">\
                                Home:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" home="home_modal" type="text" id="home_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the home</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetContactHome(\'' + contact_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetContactHome(contact_id);
}

function GetContactHome(contact_id)
{
  $.ajax({
          url:"{{ route('get_contact_home') }}",
          type:'GET',
          data:{
                "contact_id": contact_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#home_modal').val(res.home);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetContactHome(contact_id)
{
  $.ajax({
          url:"{{ route('set_contact_home') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "contact_id": contact_id,
                "home": $('#home_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerContacts();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function ShowModalEditContactAlternate(contact_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Contact Alternate');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="alternate" class="col-md-4 control-label">\
                                Alternate:\
                              </label>\
                              <div class="col-md-8">\
                                  <input class="form-control" alternate="alternate_modal" type="text" id="alternate_modal" class="form-control" required>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the alternate</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetContactAlternate(\'' + contact_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetContactAlternate(contact_id);
}

function GetContactAlternate(contact_id)
{
  $.ajax({
          url:"{{ route('get_contact_alternate') }}",
          type:'GET',
          data:{
                "contact_id": contact_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#alternate_modal').val(res.alternate);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetContactAlternate(contact_id)
{
  $.ajax({
          url:"{{ route('set_contact_alternate') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "contact_id": contact_id,
                "alternate": $('#alternate_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadCustomerContacts();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function LoadLeads()
{
  $.ajax({
          url:"{{ route('get_customer_leads') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_leads').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalEditLeadSource(lead_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Lead Source');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="alternate" class="col-md-4 control-label">\
                                Source:\
                              </label>\
                              <div class="col-md-8">\
                                  <select class="form-control" id="source_id_modal" name="source_id_modal">\
                                    <option value="">-- Select Source --</option>\
                                    @foreach($leadSources as $source)\
                                      <option value="{{ $source->id }}">{{ $source->name }}</option>\
                                    @endforeach\
                                  </select>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the Source</p>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetLeadSource(\'' + lead_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetLeadSource(lead_id);
}

function GetLeadSource(lead_id)
{
  $.ajax({
          url:"{{ route('get_lead_source') }}",
          type:'GET',
          data:{
                "lead_id": lead_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#source_id_modal').val(res.source_id);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetLeadSource(lead_id)
{
  $.ajax({
          url:"{{ route('set_lead_source') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "lead_id": lead_id,
                "source_id": $('#source_id_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadLeads();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function LoadQuotes()
{
  $.ajax({
          url:"{{ route('get_customer_quotes') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_quotes').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadJobs()
{
  $.ajax({
          url:"{{ route('get_customer_jobs') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_jobs').html(res.data);
                  $('[data-toggle="popover"]').popover();
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadFinalTouch()
{
  $.ajax({
          url:"{{ route('get_customer_final_touch_warranty') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}',
                "warranty": false
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_final_touch').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadWarranty()
{
  $.ajax({
          url:"{{ route('get_customer_final_touch_warranty') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}',
                "warranty": true
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_warranty').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadCustomerNotes()
{
  $.ajax({
          url:"{{ route('get_customer_notes') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_notes').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function AddNotes()
{
  $.ajax({
          url:"{{ route('save_customer_notes') }}",
          type:'POST',
          data:{
                "customer_id": '{{ $customer->id }}',
                "note": $('textarea#note').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  setStatusMessage('success', 'Success', res.message);
                  $('textarea#note').val('');
                  LoadCustomerNotes();
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadCustomerTasks()
{
  $.ajax({
          url:"{{ route('get_customer_tasks') }}",
          type:'GET',
          data:{
                "customer_id": '{{ $customer->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_customer_tasks').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalAddTask(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Create Task');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="subject" class="col-md-4 control-label">\
                                  Subject:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="subject" id="subject">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="due_date" class="col-md-4 control-label">\
                                  Due Date:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" name="due_date" id="due_date" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#due_date">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="due_time" class="col-md-4 control-label">\
                                  Due Time:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" name="due_time" id="due_time" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#due_time">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="urgent" class="col-md-4 control-label">\
                                  Is this urgent?\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="checkbox" class="form-control" name="urgent" id="urgent" value="1">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="assigned_id" class="col-md-4 control-label">\
                                  Assigned:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="assigned_id" id="assigned_id">\
                                    <option value="0">-- Unassigned -- </option>\
                                    @foreach($users as $user)\
                                      <option value="{{ $user->id }}">{{ $user->name }}</option>\
                                    @endforeach\
                                    </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="body" class="col-md-4 control-label">\
                                  Details:\
                                </label>\
                                <div class="col-md-8">\
                                  <textarea class="form-control" name="body" id="body"></textarea>\
                                </div>\
                              </div>\
                          ');

    $('#due_date').datetimepicker({
                    format: 'L'
                });

    $('#due_time').datetimepicker({
                    format: 'LT'
                });

    $('.modal-footer').html('\
                              <a href="#" id="btn_create_task" class="btn btn-primary" onclick="SaveTask(' + job_id + ')"><i class="fa fa-plus"></i> Create Task</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');



    $('#myModal').modal('show');
}

function SaveTask(job_id)
{
    var urgent = '0';
    if($('#urgent').is(":checked")) urgent = '1';
    $.ajax({
            url:"{{ route('save_job_task') }}",
            type:'POST',
            beforeSend: function() {
                 $('#btn_create_task').attr('onclick', '');
                 $('#btn_create_task').html('<i class="fa fa-spinner"></i> Creating Task...');
      		  },
            data:{
                  "_token":"{{ csrf_token() }}",
                  "subject": $('#subject').val(),
                  "due_date": $('#due_date').val(),
                  "due_time": $('#due_time').val(),
                  "urgent": urgent,
                  "assigned_id": $('#assigned_id').val(),
                  "body": $("textarea#body").val(),
                  "job_id": job_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                  $('#btn_create_task').attr('onclick', 'SaveTask(' + job_id + ')');
                  $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_create_task').attr('onclick', 'SaveTask(' + job_id + ')');
                $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
            }
          });
}

function ShowModalJobNotes(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Job Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table leadTable table-striped mt-2" id="noteTable">\
                                    <thead>\
                                    <th>Time</th>\
                                    <th>Notes</th>\
                                    <th>User</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              <hr>\
                              <div class="form-group form-row ">\
                                <label for="notes" class="col-md-4 control-label">\
                                  Notes:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" name="notes" id="notes" required></textarea>\
                                    <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the notes</p>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveNotes(\'' + job_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    LoadDataNotes(job_id);
}

function LoadDataNotes(job_id)
{
    $('#noteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_job_notes') }}",
                  data: {
                          'job_id': job_id
                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "fnInitComplete": function(oSettings, json) {
        $('#myModal').modal('show');
      },
      "aoColumns" : [
        {},
        {},
        {}
      ]
    });
}

function SaveNotes(job_id)
{
    $.ajax({
            url:"{{ route('save_job_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": job_id,
                  "notes": $('textarea#notes').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  $('textarea#notes').val('');
                  LoadDataNotes(job_id);
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function DoDeleteFile(quote_id, file_id)
{
    $.ajax({
            url:"{{ route('quote_delete_file') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "file_id": file_id,
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  LoadDataFiles(quote_id);
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

</script>
@endsection
