@extends('layouts.main', [
'title' => "Leads",
'crumbs' => [
    ['text' => "Leads"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="/leads/create" class="btn btn-primary mb-2">
                        <i class="fa fa-plus"></i> Create Lead
                    </a>
                </div>
                <table class="table leadTable table-striped mt-2" id="leadTable">
                    <thead>
                    <th>Customer</th>
                    <th>Age</th>
                    <th>Status</th>
                    <th>Source</th>
                    <th>Showroom Scheduled</th>
                    <th>Closing Date</th>
                    <th>Digital Measure</th>
                    <th>Designer</th>
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
});

function LoadData()
{
    $('#leadTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_leads') }}",
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
        {},
        {},
        {}
      ]
    });
}

function ShowModalSetShowroomSchedule(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Showroom Schedule');
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
                              \
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Time:\
                                </label>\
                                <div class="col-md-8">\
                                    <input class="form-control" name="time" type="text" id="time" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#time" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the time of the schedule</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetShowroomSchedule(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });

    GetShowroomSchedule(lead_id);
}

function GetShowroomSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('get_showroom_schedule') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#date').val(res.date);
                    $('#time').val(res.time);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetShowroomSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('set_showroom_schedule') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "date": $('#date').val(),
                  "time": $('#time').val()
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

function ShowModalSetShowroomLocation(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Showroom Location');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Location:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="showroom_location_id" id="showroom_location_id">\
                                    <option value="">-- Select Showroom Location -- </option>\
                                    @foreach($locations as $location)\
                                      <option value="{{ $location->id }}">{{ $location->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the location</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetShowroomLocation(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetShowroomLocation(lead_id);
}

function GetShowroomLocation(lead_id)
{
    $.ajax({
            url:"{{ route('get_showroom_location') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#showroom_location_id').val(res.showroom_location_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetShowroomLocation(lead_id)
{
    $.ajax({
            url:"{{ route('set_showroom_location') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "showroom_location_id": $('#showroom_location_id').val()
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

function ShowModalSetShowroomUser(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Showroom User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Showroom User:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="showroom_user_id" id="showroom_user_id">\
                                    <option value="">-- Select Showroom User -- </option>\
                                    @foreach($showroomUsers as $showroomUser)\
                                      <option value="{{ $showroomUser->id }}">{{ $showroomUser->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the showroom user</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetShowroomUser(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetShowroomUser(lead_id);
}

function GetShowroomUser(lead_id)
{
    $.ajax({
            url:"{{ route('get_showroom_user') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#showroom_user_id').val(res.showroom_user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetShowroomUser(lead_id)
{
    $.ajax({
            url:"{{ route('set_showroom_user') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "showroom_user_id": $('#showroom_user_id').val()
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

function ShowModalSetDesigner(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Designer');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Designer:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="designer" id="designer">\
                                    <option value="">-- Select Designer -- </option>\
                                    @foreach($designers as $designer)\
                                      <option value="{{ $designer->id }}">{{ $designer->name }}</option>\
                                    @endforeach\
                                    <option value="5">Richard Bishop</option></select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the designer</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetDesigner(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetDesigner(lead_id);
}

function GetDesigner(lead_id)
{
    $.ajax({
            url:"{{ route('get_designer') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#designer').val(res.user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetDesigner(lead_id)
{
    $.ajax({
            url:"{{ route('set_designer') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "user_id": $('#designer').val()
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

function ShowModalSetDigitalSchedule(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Digital Schedule');
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
                              \
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Time:\
                                </label>\
                                <div class="col-md-8">\
                                    <input class="form-control" name="time" type="text" id="time" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#time" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the time of the schedule</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetDigitalSchedule(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });

    GetDigitalSchedule(lead_id);
}

function GetDigitalSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('get_digital_schedule') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#date').val(res.date);
                    $('#time').val(res.time);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetDigitalSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('set_digital_schedule') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "date": $('#date').val(),
                  "time": $('#time').val()
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

function ShowModalSetDigitalUser(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Digital User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Digital User:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="digital_user_id" id="digital_user_id">\
                                    <option value="">-- Select Digital User -- </option>\
                                    @foreach($digitalUsers as $digitalUser)\
                                      <option value="{{ $digitalUser->id }}">{{ $digitalUser->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the digital user</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetDigitalUser(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetDigitalUser(lead_id);
}

function GetDigitalUser(lead_id)
{
    $.ajax({
            url:"{{ route('get_digital_user') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#digital_user_id').val(res.digital_user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetDigitalUser(lead_id)
{
    $.ajax({
            url:"{{ route('set_digital_user') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "digital_user_id": $('#digital_user_id').val()
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

function ShowModalSetClosingSchedule(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Closing Schedule');
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
                              \
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Time:\
                                </label>\
                                <div class="col-md-8">\
                                    <input class="form-control" name="time" type="text" id="time" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#time" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the time of the schedule</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetClosingSchedule(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });

    GetClosingSchedule(lead_id);
}

function GetClosingSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('get_closing_schedule') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#date').val(res.date);
                    $('#time').val(res.time);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetClosingSchedule(lead_id)
{
    $.ajax({
            url:"{{ route('set_closing_schedule') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "date": $('#date').val(),
                  "time": $('#time').val()
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

function ShowModalSetClosingUser(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Closing User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Closing User:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="closing_user_id" id="closing_user_id">\
                                    <option value="">-- Select Closing User -- </option>\
                                    @foreach($closingUsers as $closingUser)\
                                      <option value="{{ $closingUser->id }}">{{ $closingUser->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the closing user</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetClosingUser(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetClosingUser(lead_id);
}

function GetClosingUser(lead_id)
{
    $.ajax({
            url:"{{ route('get_closing_user') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#closing_user_id').val(res.closing_user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetClosingUser(lead_id)
{
    $.ajax({
            url:"{{ route('set_closing_user') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "closing_user_id": $('#closing_user_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
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

function ShowModalSetStatus(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Closing User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Status:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="status_id" id="status_id">\
                                    <option value="">-- Select Status -- </option>\
                                    @foreach($statuses as $status)\
                                      <option value="{{ $status->id }}">{{ $status->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the status</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetStatus(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetStatus(lead_id);
}

function GetStatus(lead_id)
{
    $.ajax({
            url:"{{ route('get_lead_status') }}",
            type:'GET',
            data:{
                  "lead_id": lead_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#status_id').val(res.status_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetStatus(lead_id)
{
    $.ajax({
            url:"{{ route('set_lead_status') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "status_id": $('#status_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  $('#span_status_' + lead_id).html(res.data);
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

function ShowModalArchiveConfirm(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoArchive(' + lead_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoArchive(lead_id)
{
    $.ajax({
            url:"{{ route('set_lead_archived') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id
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

function ShowModalNotes(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Lead Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table leadTable table-striped mt-2" id="noteTable">\
                                    <thead>\
                                    <th>Timestamp</th>\
                                    <th>From</th>\
                                    <th>Note</th>\
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
                              <a href="#" class="btn btn-primary" onclick="SaveNotes(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    LoadDataNotes(lead_id);
}

function LoadDataNotes(lead_id)
{
    $('#noteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_lead_notes') }}",
                  data: {
                          'lead_id': lead_id
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

function SaveNotes(lead_id)
{
    $.ajax({
            url:"{{ route('save_lead_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "notes": $('textarea#notes').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  $('textarea#notes').val('');
                  LoadDataNotes(lead_id);
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

function ShowModalFollowUp(lead_id, cust_name)
{
    removeMessageModal();

    $('.modal-title').html('Followups | ' + cust_name);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table followupTable table-striped mt-2" id="followupTable">\
                                    <thead>\
                                    <th>Timestamp</th>\
                                    <th>Status</th>\
                                    <th>By</th>\
                                    <th>Comments</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataFollowUp(lead_id);
}

function LoadDataFollowUp(lead_id)
{
    $('#followupTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_lead_follow_up') }}",
                  data: {
                          'lead_id': lead_id
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
        {}
      ]
    });
}

function SetCloseFollowup(followup_id)
{
    $.ajax({
            url:"{{ route('set_close_follow_up') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "followup_id": followup_id,
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  LoadDataFollowUp(res.lead_id);
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

function ShowModalQuote(lead_id, cust_name)
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
                                    <input type="text" class="form-control" name="customer_id" id="customer_id" value="' + cust_name + '" disabled>\
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
                              <a href="#" class="btn btn-primary" onclick="SaveQuote(\'' + lead_id + '\')"><i class="fa fa-save"></i> Begin Quote</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function ShowModalSetSource(lead_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Source');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="source_id" class="col-md-4 control-label">\
                                  Source Name:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="source_id" id="source_id">\
                                    <option value="">-- Select Source -- </option>\
                                    @foreach($leadSources as $leadSource)\
                                      <option value="{{ $leadSource->id }}">{{ $leadSource->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the source</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetSource(\'' + lead_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetSource(lead_id);
}

function GetSource(lead_id)
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
                    $('#source_id').val(res.source_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetSource(lead_id)
{
    $.ajax({
            url:"{{ route('set_lead_source') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
                  "source_id": $('#source_id').val()
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

function SaveQuote(lead_id)
{
    $.ajax({
            url:"{{ route('save_lead_quote') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "lead_id": lead_id,
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

</script>
@endsection
