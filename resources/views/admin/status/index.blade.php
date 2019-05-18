@extends('layouts.main', [
'title' => "Status",
'crumbs' => [
    ['text' => "Status"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table table-striped mt-2" id="statusTable">
                    <thead>
                    <th>Status</th>
                    <th>Actions</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Status</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Name:
                    </label>
                    <div class="col-md-8">
                      <textarea id="name" name="name" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="followup_status" data-parsley-multiple="groups" data-parsley-mincheck="2" value="Y">
                        <label class="custom-control-label" for="followup_status">This is a follow-up status</label>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="followup_lock" data-parsley-multiple="groups" data-parsley-mincheck="2" value="Y">
                        <label class="custom-control-label" for="followup_lock">Lock this followup status once set?</label>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="status_id" id="status_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddStatus();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdateStatus();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
                </div>
            </div>
            <br/>
            <div class="card" id="div_expirations" style="display:none;">
                <div class="card-header bg-primary text-white"><b>Expirations</b></div>
                <div class="card-body">
                  <table class="table table-striped mt-2" id="expirationTable">
                      <thead>
                      <th>Name</th>
                      <th>Expires</th>
                      <th>Delete</th>
                      </thead>
                      <tbody id="body_expiration"></tbody>
                  </table>
                </div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Name:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="expires_name" name="expires_name" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Expires in Hours:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="expires" name="expires" class="form-control" value="0"/>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Expires in Hours Before Appointment:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="expires_before" name="expires_before" class="form-control" value="0"/>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Expires in Hours AFTER Appointment:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="expires_after" name="expires_after" class="form-control" value="0"/>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      This expiration is based on:
                    </label>
                    <div class="col-md-8">
                      <select type="text" id="type" name="type" class="form-control">
                        <option value="Status Change">Time since last status change</option>
                        <option value="Last Note">Time since last note was made</option>
                      </select>
                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Expirations are based on time since status has been changed or when a follow-up happened.</p>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Color of Lead/Quote Warning:
                    </label>
                    <div class="col-md-8">
                      <select type="text" id="warning" name="warning" class="form-control">
                        <option value="N">No Warning</option>
                        <option value="Y">Yellow</option>
                        <option value="R">Red</option>
                      </select>
                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">If this expiration happens, what color should the lead/quote warning notification be?</p>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="expiration_id" id="expiration_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddStatusExpiration();" id="btn_add_expiration"><i class="fa fa-check"></i> Save</a>
                    <a href="#" class="btn btn-info" onclick="UpdateStatusExpiration();" id="btn_update_expiration" style="display:none;"><i class="fa fa-check"></i> Update</a>
                </div>
            </div>
            <br/>
            <div class="card" id="div_action" style="display:none;">
                <div class="card-header bg-primary text-white"><b>Actions</b></div>
                <div class="card-body">
                  <table class="table table-striped mt-2" id="expirationTable">
                      <thead>
                      <th>Description</th>
                      <th>To</th>
                      <th>SMS</th>
                      <th>Email</th>
                      <th>Attachment</th>
                      <th>Delete</th>
                      </thead>
                      <tbody id="body_expiration_action"></tbody>
                  </table>
                </div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Description:
                    </label>
                    <div class="col-md-8">
                      <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Send To:
                    </label>
                    <div class="col-md-8">
                      <select id="group_id" name="group_id" class="form-control">
                        @foreach($groups as $group)
                          <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="sms" data-parsley-multiple="groups" data-parsley-mincheck="2" value="Y">
                        <label class="custom-control-label" for="sms">Send SMS</label>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      SMS Content:
                    </label>
                    <div class="col-md-8">
                      <textarea id="sms_content" name="sms_content" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="email" data-parsley-multiple="groups" data-parsley-mincheck="2" value="Y">
                        <label class="custom-control-label" for="email">Send Email</label>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Email Subject:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="email_subject" name="email_subject" class="form-control" value=""/>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Email Content:
                    </label>
                    <div class="col-md-8">
                      <textarea id="email_content" name="email_content" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="action_id" id="action_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddStatusExpirationAction();" id="btn_add_expiration_action"><i class="fa fa-check"></i> Save</a>
                    <a href="#" class="btn btn-info" onclick="UpdateStatusExpirationAction();" id="btn_update_expiration_action" style="display:none;"><i class="fa fa-check"></i> Update</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif
});

function LoadData()
{
    $('#statusTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_statuses') }}",
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
        {}
      ]
    });
}

function AddStatus()
{
    var followup_status = 0;
    var followup_lock = 0;
    if($('#followup_status').is(":checked")) followup_status = 1;
    if($('#followup_lock').is(":checked")) followup_lock = 1;

    $.ajax({
            url:"{{ route('statuses.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('textarea#name').val(),
                  'followup_status' : followup_status,
                  'followup_lock' : followup_lock,
                  'status_id' : $('#status_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function EditStatus(status_id)
{
    $.ajax({
            url:"{{ route('get_status') }}",
            type:'GET',
            data:{
                  'status_id' : status_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('textarea#name').val(res.name);
                    if(res.followup_status == '1') $('#followup_status').prop('checked', true);
                    else $('#followup_status').prop('checked', false);
                    if(res.followup_lock == '1') $('#followup_lock').prop('checked', true);
                    else $('#followup_lock').prop('checked', false);
                    $('#status_id').val(res.status_id);
                    $('#btn_update').attr('style', '');
                    $('#btn_add').attr('style', 'display:none;');
                    ShowExpirationForm(res.status_id);
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function UpdateStatus()
{
    var followup_status = 0;
    var followup_lock = 0;
    if($('#followup_status').is(":checked")) followup_status = 1;
    if($('#followup_lock').is(":checked")) followup_lock = 1;

    $.ajax({
            url:"{{ route('update_status') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('textarea#name').val(),
                  'followup_status' : followup_status,
                  'followup_lock' : followup_lock,
                  'status_id' : $('#status_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    $('#div_expirations').attr('style', 'display:none;');
                    ClearFormExpiration();
                    ClearForm();
                    LoadData();
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ShowExpirationForm(status_id)
{
    LoadExpirationData(status_id);
}

function LoadExpirationData(status_id)
{
  $.ajax({
          url:"{{ route('get_expirations') }}",
          type:'GET',
          data:{
                'status_id' : status_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_expiration').html(res.data);
                  $('#div_expirations').attr('style', '');
                  ClearFormExpiration();
              }
              else
              {
                  setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function AddStatusExpiration()
{
    $.ajax({
            url:"{{ route('save_status_expiration') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#expires_name').val(),
                  'expires' : $('#expires').val(),
                  'expires_before' : $('#expires_before').val(),
                  'expires_after' : $('#expires_after').val(),
                  'type' : $('#type').val(),
                  'warning' : $('#warning').val(),
                  'status_id' : $('#status_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpiration();
                    LoadExpirationData($('#status_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function DeleteExpiration(expiration_id)
{
    $.ajax({
            url:"{{ route('delete_status_expiration') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'expiration_id' : expiration_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpiration();
                    LoadExpirationData($('#status_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ShowEditExpiration(expiration_id)
{
    $.ajax({
            url:"{{ route('get_expiration') }}",
            type:'GET',
            data:{
                  'expiration_id' : expiration_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#expires_name').val(res.name);
                    $('#expires').val(res.expires);
                    $('#expires_before').val(res.expires_before);
                    $('#expires_after').val(res.expires_after);
                    $('#type').val(res.type);
                    $('#warning').val(res.warning);
                    $('#btn_add_expiration').attr('style', 'display:none;');
                    $('#btn_update_expiration').attr('style', '');
                    $('#expiration_id').val(res.expiration_id);
                    ShowExpirationAction(expiration_id);
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function UpdateStatusExpiration()
{
    $.ajax({
            url:"{{ route('update_status_expiration') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#expires_name').val(),
                  'expires' : $('#expires').val(),
                  'expires_before' : $('#expires_before').val(),
                  'expires_after' : $('#expires_after').val(),
                  'type' : $('#type').val(),
                  'warning' : $('#warning').val(),
                  'expiration_id' : $('#expiration_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpiration();
                    LoadExpirationData($('#status_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ShowExpirationAction(expiration_id)
{
    LoadExpirationActionData(expiration_id);
    $('#div_action').attr('style', '');
}

function LoadExpirationActionData(expiration_id)
{
  $.ajax({
          url:"{{ route('get_expiration_actions') }}",
          type:'GET',
          data:{
                'status_expiration_id' : expiration_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_expiration_action').html(res.data);
                  ClearFormExpirationAction();
              }
              else
              {
                  setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowEditExpirationAction(action_id)
{
    $.ajax({
            url:"{{ route('get_expiration_action') }}",
            type:'GET',
            data:{
                  'action_id' : action_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('textarea#description').val(res.description);
                    $('#group_id').val(res.group_id);
                    if(res.sms == '1') $('#sms').prop('checked', true);
                    else $('#sms').prop('checked', false);
                    $('textarea#sms_content').val(res.sms_content);
                    if(res.email == '1') $('#email').prop('checked', true);
                    else $('#email').prop('checked', false);
                    $('#email_subject').val(res.email_subject);
                    $('textarea#email_content').val(res.email_content);
                    $('#btn_add_expiration_action').attr('style', 'display:none;');
                    $('#btn_update_expiration_action').attr('style', '');
                    $('#action_id').val(res.action_id);
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function AddStatusExpirationAction()
{
    var sms = 0;
    var email = 0;
    if($('#sms').is(":checked")) sms = 1;
    if($('#email').is(":checked")) email = 1;

    $.ajax({
            url:"{{ route('save_status_expiration_action') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'description' : $('textarea#description').val(),
                  'group_id' : $('#group_id').val(),
                  'sms' : sms,
                  'sms_content' : $('textarea#sms_content').val(),
                  'email' : email,
                  'email_subject' : $('#email_subject').val(),
                  'email_content' : $('textarea#email_content').val(),
                  'expiration_id' : $('#expiration_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpirationAction();
                    LoadExpirationActionData($('#expiration_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function UpdateStatusExpirationAction()
{
    var sms = 0;
    var email = 0;
    if($('#sms').is(":checked")) sms = 1;
    if($('#email').is(":checked")) email = 1;

    $.ajax({
            url:"{{ route('update_status_expiration_action') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'description' : $('textarea#description').val(),
                  'group_id' : $('#group_id').val(),
                  'sms' : sms,
                  'sms_content' : $('textarea#sms_content').val(),
                  'email' : email,
                  'email_subject' : $('#email_subject').val(),
                  'email_content' : $('textarea#email_content').val(),
                  'action_id' : $('#action_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpirationAction();
                    LoadExpirationActionData($('#expiration_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function DeleteExpirationAction(action_id)
{
    $.ajax({
            url:"{{ route('delete_status_expiration_action') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'action_id' : action_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearFormExpirationAction();
                    LoadExpirationActionData($('#expiration_id').val());
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ShowModalAttachment(action_id)
{
    removeMessageModal();

    $('.modal-title').html('Upload Attachment');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <b>This attachment will be emailed to all parties that are emailed in this action.</b>\
                              </div>\
                              \
                              <form id="form_upload_attachment" action="#" method="post" enctype="multipart/form-data">\
                              <div class="form-group form-row ">\
                                <label for="customer" class="col-md-4 control-label">\
                                  Attachment:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" name="attachment" id="attachment" />\
                                </div>\
                              </div>\
                              <input type="hidden" name="upload_action_id" value="' + action_id + '" />\
                              <button type="submit" id="btn_upload" class="btn btn-primary">Upload</button>\
                              </form>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');

    $("#btn_upload").click(function(event) {
      event.preventDefault();
      var form = $('#form_upload_attachment')[0];
      var data = new FormData(form);

      $.ajax({
              url:"{{ route('status_upload_attachment') }}",
              type: "POST",
              data: data,
              enctype: 'multipart/form-data',
              processData: false,  // Important!
              contentType: false,
              cache: false,
              success: function (res)
              {
                  if(res.response == "success")
                  {
                      setStatusMessage("success", "Success", res.message);
                      ClearFormExpirationAction();
                      LoadExpirationActionData($('#expiration_id').val());
                      CloseModal();
                  }
                  else
                  {
                      setMessageModal("danger", "Error", res.message);
                  }
              },
              error: function(a, b, c)
              {

              }
            });
    });
}

function ClearForm()
{
    $('#title').html('New Status');
    $('textarea#name').val('');
    $('#followup_status').prop('checked', false);
    $('#followup_lock').prop('checked', false);
    $('#status_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

function ClearFormExpiration()
{
    $('#expires_name').val('');
    $('#expires').val('0');
    $('#expires_before').val('0');
    $('#expires_after').val('0');
    $('#type').val('Status Change');
    $('#warning').val('N');
    $('#expiration_id').val('');
    $('#btn_add_expiration').attr('style', '');
    $('#btn_update_expiration').attr('style', 'display:none;');
    $('#div_action').attr('style', 'display:none;');
}

function ClearFormExpirationAction()
{
    $('textarea#description').val('');
    $('#group_id').prop('selectedIndex', 0);
    $('#sms').prop('checked', false);
    $('textarea#sms_content').val('');
    $('#email').prop('checked', false);
    $('#email_subject').val('');
    $('textarea#email_content').val('');
    $('#action_id').val('');
    $('#btn_add_expiration_action').attr('style', '');
    $('#btn_update_expiration_action').attr('style', 'display:none;');
}

</script>
@endsection
