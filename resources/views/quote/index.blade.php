@extends('layouts.main', [
'title' => "Quotes",
'crumbs' => [
    ['text' => "Quotes"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalQuote();">
                        <i class="fa fa-plus"></i> Create Quote
                    </a>
                </div>
                <table class="table quoteTable table-striped mt-2" id="quoteTable">
                    <thead>
                    <th>Action</th>
                    <th>Customer/Title</th>
                    <th>Designer</th>
                    <th>Age</th>
                    <th>Type</th>
                    <th>Lead Status</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script type="text/javascript">

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if($request->get('upload_file') == 1)
    ShowModalDrawing({{ $request->quote_id }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif
});

function LoadData()
{
    $('#quoteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quotes') }}",
                  data: {

                        }
              },
      "bDeferRender": true,
      "searching": true,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "fnInitComplete": function(oSettings, json) {

      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

function ShowModalArchiveConfirm(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoArchive(' + quote_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoArchive(quote_id)
{
    $.ajax({
            url:"{{ route('set_quote_archived') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
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

function ShowModalQuote()
{
    removeMessageModal();

    $('.modal-title').html('Create Initial Quote');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <b>You are creating an initial quote. Once you have completed the initial quote you will be able to create a final quote</b>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="customer" class="col-md-4 control-label">\
                                  Customer:\
                                </label>\
                                <div class="col-md-8">\
                                  <select class="form-control" name="lead_id" id="lead_id">\
                                  <option value="">-- Select Customer -- </option>\
                                  @foreach($leads as $lead)\
                                    <option value="{{ $lead->id }}">{{ $lead->cust_name }} ({{ $lead->cust_city }}, {{ $lead->cust_state }}) ({{ $lead->id }})</option>\
                                  @endforeach\
                                  </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="quote_type" class="col-md-4 control-label">\
                                  Quote Type:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="quote_type_id" id="quote_type_id">\
                                    <option value="">-- Select Quote Type -- </option>\
                                    @foreach($quoteTypes as $quoteType)\
                                      <option value="{{ $quoteType->id }}">{{ $quoteType->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the quote type</p>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveQuote()"><i class="fa fa-save"></i> Begin Quote</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveQuote()
{
    $.ajax({
            url:"{{ route('save_quote') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": $('#lead_id').val(),
                  "quote_type_id": $('#quote_type_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  window.location = "{{ url('quotes') }}/" + res.quote_id + "/start";
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

function SetQuoteFinal(quote_id)
{
    $.ajax({
            url:"{{ route('set_quote_final') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  window.location = "{{ url('') }}/quotes/" + res.new_quote_id + "/start";
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

function ShowModalAddTask(quote_id)
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
                              <a href="#" id="btn_create_task" class="btn btn-primary" onclick="SaveTask(' + quote_id + ')"><i class="fa fa-plus"></i> Create Task</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');



    $('#myModal').modal('show');
}

function ShowModalArchiveConfirm(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoArchive(' + quote_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoArchive(quote_id)
{
    $.ajax({
            url:"{{ route('set_quote_archived') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                  $('#btn_create_task').removeAttr('disabled');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_create_task').removeAttr('disabled');
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

function SaveTask(quote_id)
{
    var urgent = '0';
    if($('#urgent').is(":checked")) urgent = '1';
    $.ajax({
            url:"{{ route('save_quote_task') }}",
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
                  "quote_id": quote_id
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
                  $('#btn_create_task').attr('onclick', 'SaveTask(' + quote_id + ')');
                  $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_create_task').attr('onclick', 'SaveTask(' + quote_id + ')');
                $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
            }
          });
}

function ShowModalDuplicate(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Duplicate Quote');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <b>You are duplicating a quote in it\'s exact form. You will need to change the quote title to differentiate between the original(s) and duplicates.</b>\
                              </div>\
                              <div class="form-group form-row ">\
                                <label for="title" class="col-md-4 control-label">\
                                  Duplicated Quote Title:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="title" id="title" required>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" id="btn_create_duplicate" class="btn btn-primary" onclick="SaveDuplicate(' + quote_id + ')"><i class="fa fa-plus"></i> Create Duplicate</a>\
                              \
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveDuplicate(quote_id)
{
    $.ajax({
            url:"{{ route('duplicate_quote') }}",
            type:'POST',
            beforeSend: function() {
                 $('#btn_create_duplicate').attr('onclick', '');
                 $('#btn_create_duplicate').html('<i class="fa fa-spinner"></i> Creating Duplicate...');
            },
            data:{
                  "_token":"{{ csrf_token() }}",
                  "title": $('#title').val(),
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                  $('#btn_create_duplicate').attr('onclick', 'SaveDuplicate(' + quote_id + ')');
                  $('#btn_create_duplicate').html('<i class="fa fa-plus"></i> Create Duplicate');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_create_duplicate').attr('onclick', 'SaveDuplicate(' + quote_id + ')');
                $('#btn_create_duplicate').html('<i class="fa fa-plus"></i> Create Duplicate');
            }
          });
}

function ShowModalAppliance(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Appliance Settings');
    $('.modal-body').html('\
                              <form id="form_appliance_settings" role="form" action="{{ url('quotes') }}/' + quote_id + '/appsettings" method="post">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <table class="table applianceTable table-striped mt-2" id="applianceTable">\
                                </table>\
                              </div>\
                              \
                              <button id="btn_quote_appliance_save" class="btn btn-primary"><i class="fa fa-check"></i> Save</button>\
                              \
                              <a href="{{ url('quotes') }}/' + quote_id + '/appsettings/send" class="btn btn-info"><i class="fa fa-send"> Send Appliances to Customer</i></a>\
                              </form>\
                          ');

    $('.modal-footer').html('');

    $('.modal-dialog').attr('style', 'max-width: 1000px;');
    GetApplianceData(quote_id);
}

function GetApplianceData(quote_id)
{
  $.ajax({
          url:"{{ route('get_quote_appliances') }}",
          type:'GET',
          data:{
                "quote_id": quote_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                $('#applianceTable').html(res.data);
                $('#myModal').modal('show');
              }
              else if(res.response == "error")
              {
              }
          },
          error: function(a, b, c)
          {
          }
        });
}

</script>
@endsection
