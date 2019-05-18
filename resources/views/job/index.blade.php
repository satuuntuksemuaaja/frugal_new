@extends('layouts.main', [
'title' => "Jobs",
'crumbs' => [
    ['text' => "Jobs"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
					<div class="form-inline">
						<div class="form-group">
							Search by:
							&nbsp;
							<select class="form-control" id="search_by" name="search_by">
								<option value="Client Name">Client Name</option>
								<option value="Quote Type">Quote Type</option>
								<option value="Designer">Designer</option>
								<option value="Closed">Closed</option>
								<option value="Starts">Starts</option>
							</select>
							&nbsp;
							<input type="text" id="q" name="q" class="form-control" />
							&nbsp;
							<button class="btn btn-success" id="btn_search" onclick="DoSearch();">Search</button>
						</div>
					</div>
                </div>
                <table class="table jobTable table-striped mt-2" id="jobTable">
                    <thead>
                    <th>Client</th>
                    <th>Designer</th>
                    <th>Closed</th>
                    <th>Cabinets</th>
                    <th>Hardwares</th>
                    <th>Accessories</th>
                    <th>Items</th>
                    <th>Starts</th>
                    <th>Schedule</th>
                    </thead>
                    <tbody></tbody>
                </table>

                <div class="card-body">
                <a href="#" class="btn btn-primary" onclick="ShowModalExportJobs();"><i class="fa fa-arrow-right"></i> Export Jobs</a>
                </div>

                <div class="card-body">
                  <b>Color Codes: RED - Not Ordered, ORANGE - Shipped (Confirmed), BLUE - Needs to be confirmed, GREEN - RECEIVED</b>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
.popover {
  top: 0;
  left: 0;
  z-index: 9999;
  max-width: 800px;
  padding: 1px;
  text-align: left;
  white-space: normal;
  background-color: #ffffff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, 0.2);
  -webkit-border-radius: 6px;
     -moz-border-radius: 6px;
          border-radius: 6px;
  -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
     -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
          box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
  -webkit-background-clip: padding-box;
     -moz-background-clip: padding;
          background-clip: padding-box;
}
</style>
@endsection

@section('javascript')
<script type="text/javascript">
var arrJobs = [];

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if($request->upload_file == 1)
    ShowModalDrawing({{ $request->quote_id }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif
});

function LoadData()
{
    $('#jobTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_jobs') }}",
                  data: {

                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": true,
	  "language": {
			"processing": "Loading. Please wait... <b><font color='red'>(Give this a minute to load..)</font></b>"
	  },
	  "search": {
		"smart": false
	  },
      "serverSide": true,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "createdRow": function( row, data, dataIndex){
          if( data[9] ==  `Outdated`){
              $(row).css('background-color', '#f7e0db');
          }
      },
      "drawCallback": function() {
        $('[data-toggle="popover"]').popover();
      },
      "fnInitComplete": function(oSettings, json) {
        $('[data-toggle="popover"]').popover();
      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });

}

function ShowModalJobItem(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Verify Items');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table fileTable table-striped mt-2" id="itemVerifyTable">\
                                    <thead>\
                                    <th>Item</th>\
                                    <th>Verified On</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="reference" class="col-md-4 control-label">\
                                  New Item:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="reference" id="reference" required>\
                                </div>\
                              </div>\
                              \
                              <a href="#" id="btn_add_job_item" class="btn btn-primary" onclick="SaveJobItem(' + job_id + ')"><i class="fa fa-plus"> Add Item</i></a>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataJobItems(job_id);
}

function LoadDataJobItems(job_id)
{
    $('#itemVerifyTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_job_items') }}",
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
        {}
      ]
    });
}

function SaveJobItem(job_id)
{
  $.ajax({
          url:"{{ route('save_job_items') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_add_job_item').attr('onclick', '');
               $('#btn_add_job_item').html('<i class="fa fa-spinner"></i> Adding Item...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "reference": $('#reference').val(),
                "job_id": job_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                $('#reference').val('');
                $('#btn_add_job_item').attr('onclick', 'SaveJobItem(' + job_id + ')');
                $('#btn_add_job_item').html('<i class="fa fa-plus"></i> Add Item');
                LoadDataJobItems(job_id);
                LoadData();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_add_job_item').attr('onclick', 'SaveJobItem(' + job_id + ')');
                $('#btn_add_job_item').html('<i class="fa fa-plus"></i> Add Item');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_add_job_item').attr('onclick', 'SaveJobItem(' + job_id + ')');
              $('#btn_add_job_item').html('<i class="fa fa-plus"></i> Add Item');
          }
        });
}

function SetVerifyItem(job_id, item_id)
{
  $.ajax({
          url:"{{ route('set_verify_job_item') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_verify_item_' + item_id).attr('onclick', '');
               $('#btn_verify_item_' + item_id).html('<i class="fa fa-spinner"></i> Verifying Item...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "item_id": item_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                LoadDataJobItems(job_id);
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_verify_item_' + item_id).attr('onclick', 'SetVerifyItem(' + item_id + ')');
                $('#btn_verify_item_' + item_id).html('<i class="fa fa-check"></i> Verify');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_verify_item_' + item_id).attr('onclick', 'SetVerifyItem(' + item_id + ')');
              $('#btn_verify_item_' + item_id).html('<i class="fa fa-check"></i> Verify');
          }
        });
}

function DeleteItem(job_id, item_id)
{
  $.ajax({
          url:"{{ route('delete_job_item') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_delete_item_' + item_id).attr('onclick', '');
               $('#btn_delete_item_' + item_id).html('<i class="fa fa-spinner"></i> Deleting Item...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "item_id": item_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                LoadDataJobItems(job_id);
                LoadData();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_delete_item_' + item_id).attr('onclick', 'DeleteItem(' + item_id + ')');
                $('#btn_delete_item_' + item_id).html('<i class="fa fa-trash"></i> Delete');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_delete_item_' + item_id).attr('onclick', 'DeleteItem(' + item_id + ')');
              $('#btn_delete_item_' + item_id).html('<i class="fa fa-trash"></i> Delete');
          }
        });
}

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
                              <form id="form_upload_file" role="form" action="{{ url('jobs') }}/' + quote_id + '/upload_file" method="post" enctype="multipart/form-data">\
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
                  url: "{{ route('display_job_files') }}",
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

function DoDeleteFile(quote_id, file_id)
{
    $.ajax({
            url:"{{ route('job_delete_file') }}",
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

function ShowModalNotes(job_id)
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

function ShowModalDeleteConfirm(job_id)
{
    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure? This action cannot be undone.</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + job_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(job_id)
{
    window.location = '{{ url("jobs") }}/' + job_id + '/destroy';
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

function SetArrival(job_id)
{
    $.ajax({
            url:"{{ url('jobs') }}/" + job_id + "/arrival",
            type:'GET',
            beforeSend: function() {
                setStatusMessage("info", "Info", "Setting Arrival...");
      		  },
            data:{
                  "_token":"{{ csrf_token() }}"
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                }
                else if(res.response == "error")
                {
                  setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setStatusMessage("danger", "Error", "Save not commited!");
            }
          });
}

function ShowModalQuoteAppliances(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Appliance Settings');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table leadTable table-striped mt-2" id="quoteAppliancesTable">\
                                    <thead>\
                                    <th>Appliance</th>\
                                    <th>Brand</th>\
                                    <th>Model</th>\
                                    <th>Size</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <input type="hidden" name="quoteApplianceIdArr" id="quoteApplianceIdArr" value="">\
                              <a href="#" id="btn_save_quote_appliances" class="btn btn-primary" onclick="SaveQuoteAppliances(\'' + quote_id + '\')"><i class="fa fa-check"></i> Save</a>\
                              <a href="#" id="btn_send_quote_appliances" class="btn btn-info" onclick="SendQuoteAppliances(\'' + quote_id + '\')"><i class="fa fa-paper-plane"></i> Send Appliances to Customer</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    LoadDataQuoteAppliances(quote_id);
}

function LoadDataQuoteAppliances(quote_id)
{
    $('#quoteAppliancesTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_job_appliances') }}",
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
        $('#quoteApplianceIdArr').val(json.quoteApplianceIdArr);
        $('#myModal').modal('show');
      },
      "aoColumns" : [
        {},
        {},
        {},
        {}
      ]
    });
}

function SaveQuoteAppliances(quote_id)
{
    var stringQuoteApplianceId = $('#quoteApplianceIdArr').val();
    var quoteApplianceIdArr = stringQuoteApplianceId.split(',');

    var data = { "_token":"{{ csrf_token() }}" };
    for(var x = 0; x < quoteApplianceIdArr.length; x++)
    {
        data['brand_' + quoteApplianceIdArr[x]] = $('#brand_' + quoteApplianceIdArr[x]).val();
        data['model_' + quoteApplianceIdArr[x]] = $('#model_' + quoteApplianceIdArr[x]).val();
        data['size_' + quoteApplianceIdArr[x]] = $('#size_' + quoteApplianceIdArr[x]).val();
    }

    data['quoteApplianceIdArr'] = stringQuoteApplianceId;

    $.ajax({
            url:"{{ route('save_job_appliances') }}",
            type:'POST',
            data,
            beforeSend: function() {
                 $('#btn_save_quote_appliances').attr('onclick', '');
                 $('#btn_save_quote_appliances').html('<i class="fa fa-spinner"></i> Saving Appliances...');
      		  },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  LoadDataQuoteAppliances(quote_id);
                  $('#btn_save_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                  $('#btn_save_quote_appliances').html('<i class="fa fa-check"></i> Save');
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                  $('#btn_save_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                  $('#btn_save_quote_appliances').html('<i class="fa fa-check"></i> Save');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_save_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                $('#btn_save_quote_appliances').html('<i class="fa fa-check"></i> Save');
            }
          });
}

function SendQuoteAppliances(quote_id)
{
    $.ajax({
            url:"{{ route('send_quote_appliances') }}",
            type:'POST',
            data: {
                    "_token": "{{ csrf_token() }}",
                    "quote_id": quote_id
                  },
            beforeSend: function() {
                 $('#btn_send_quote_appliances').attr('onclick', '');
                 $('#btn_send_quote_appliances').html('<i class="fa fa-spinner"></i> Sending Quote Appliances...');
      		  },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  $('#btn_send_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                  $('#btn_send_quote_appliances').html('<i class="fa fa-paper-plane"></i> Send Appliances to Customer');
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                  $('#btn_send_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                  $('#btn_send_quote_appliances').html('<i class="fa fa-paper-plane"></i> Send Appliances to Customer');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_send_quote_appliances').attr('onclick', 'SaveQuoteAppliances(' + quote_id + ')');
                $('#btn_send_quote_appliances').html('<i class="fa fa-check"></i> Send Appliances to Customer');
            }
          });
}

function ShowModalExportJobs()
{
    removeMessageModal();

    $('.modal-title').html('Export Jobs');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="start_date" class="col-md-4 control-label">\
                                  Start Date:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" name="start_date" id="start_date" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#start_date">\
                                </div>\
                              </div>\
                              \
                          ');

    $('#start_date').datetimepicker({
                    format: 'L'
                });

    $('.modal-footer').html('\
                              <a href="#" id="btn_generate_report" class="btn btn-primary" onclick="GenerateReport()">Generate Report</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');



    $('#myModal').modal('show');
}

function ShowModalOverrideXml(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Override XML');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <div class="alert alert-warning" role="alert">\
                                    You are about to override XML files for ordering cabinets. This will not change the contract but will change which items you are ordering and verifying. Proceed with caution! <span id="span_last_override"></span>\
                                </div>\
                              </div>\
                              <form id="form_upload_file" role="form" action="{{ url('jobs') }}/' + job_id + '/xml" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row " id="div_override_xml_field">\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary">Upload</button>\
                              \
                              </form>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetJobOverrideXmlData(job_id);
}

function GetJobOverrideXmlData(job_id)
{
    $.ajax({
            url:"/jobs/" + job_id + "/get_job_override_xml_data",
            type:'GET',
            data:{
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#div_override_xml_field').html(res.fields);
                    if(res.last_override) $('#span_last_override').html('<br/><font color="red"><b>Warning!</b> Previous override was set on ' + res.last_override + '</font>');
                    $('#myModal').modal('show');
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ShowModalSetStartDate(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Start Date');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Date:\
                                </label>\
                                <div class="col-md-8">\
                                    <input class="form-control" name="date" type="text" id="date" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#date" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the date of the schedule</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetStartDate(\'' + job_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    GetStartDate(job_id);
}

function GetStartDate(job_id)
{
    $.ajax({
            url:"{{ route('get_job_start_date') }}",
            type:'GET',
            data:{
                  "job_id": job_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#date').val(res.start_date);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetStartDate(job_id)
{
    $.ajax({
            url:"{{ route('set_job_start_date') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": job_id,
                  "date": $('#date').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  $('#a_start_date_' + job_id).html(res.data);
                  //LoadData();
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

function SetReview(job_id)
{
    $.ajax({
            url:"{{ url('jobs') }}/" + job_id + "/review",
            type:'GET',
            beforeSend: function() {
                setStatusMessage("info", "Info", "Setting Review...");
      		  },
            data:{
                  "_token":"{{ csrf_token() }}"
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                }
                else if(res.response == "error")
                {
                  setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setStatusMessage("danger", "Error", "Save not commited!");
            }
          });
}

function GenerateReport()
{
    if($('#start_date').val() == '')
    {
        setMessageModal('danger', 'Error', "Please select Start Date.")
    }
    else
    {
        window.open("{{ route('job_export') }}?start_date=" + $('#start_date').val())
    }
}

function ShowModalConstructionConfirm(job_id)
{
    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure want to verify the construction?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoConstructionVerification(' + job_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoConstructionVerification(job_id)
{
    $.ajax({
            url:"{{ url('jobs') }}/" + job_id + "/construction",
            type:'GET',
            beforeSend: function() {
                setMessageModal("info", "Info", "Verifying Construction...");
      		  },
            data:{
                  "_token":"{{ csrf_token() }}"
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
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


function DoSearch()
{
	$('#jobTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_jobs') }}",
                  data: {
							"q": $('#q').val(),
							"search_by": $('#search_by').val()
                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": true,
      "serverSide": true,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "createdRow": function( row, data, dataIndex){
          if( data[9] ==  `Outdated`){
              $(row).css('background-color', '#f7e0db');
          }
      },
      "drawCallback": function() {
        $('[data-toggle="popover"]').popover();
      },
      "fnInitComplete": function(oSettings, json) {
        $('[data-toggle="popover"]').popover();
      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

</script>
@endsection
