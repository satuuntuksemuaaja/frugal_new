@extends('layouts.main', [
'title' => "Job Schedules | for " . ($customer ? $customer->name : '--No Customer--') . ' on ' . $start_date,
'crumbs' => [
    ['text' => "Jobs"]
]])
@section('content')

@if ($job->start_date != '0000-00-00')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <table class="table jobTable table-striped mt-2 table-bordered" id="jobTable">
                      <thead>
                      <th>Day</th>
                      <th>Group</th>
                      <th>Contractor</th>
                      <th>Default Email</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Contractor Notes</th>
                      <th>Customer Notes</th>
                      <th>From Contractor</th>
                      </thead>
                      <tbody id="body_job"></tbody>
                  </table>
                </div>

                <div class="card-body">
                <a href="#" class="btn btn-info" onclick="CloseJob();"><i class="fa fa-arrow-right"></i> Close Job (Send to FFT)</a>
                <a href="#" class="btn btn-primary" onclick="AddAdditionalSchedule();"><i class="fa fa-plus"></i> Add Additional Schedule</a>
                <a href="#" class="btn btn-info" onclick="Refresh();"><i class="fa fa-refresh"></i> See Schedules</a>
                <a href="{{ route('job_send_schedule', ['id' => $job->id]) }}" class="btn btn-success" style="float:right;"><i class="fa fa-check"></i> Send Schedule to Customer</a>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif

  @if ($job->start_date == '0000-00-00')
  setStatusMessage('danger', 'Error', '<strong>No Start Date Set</strong> You must set a job start date before assigning contractors. Please click back to the job board and set a job start date.');
  @endif
});

function LoadData()
{
    $.ajax({
            url:"{{ route('display_job_schedules') }}",
            type:'GET',
            data:{
                    'job_id': '{{ $job->id }}'
            },
            beforeSend: function () {
                $('#body_job').html("Loading....");
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#body_job').html(res.data);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function AddAdditionalSchedule()
{
    $.ajax({
            url:"{{ route('new_aux_schedule') }}",
            type:'POST',
            data:{
                    'job_id': '{{ $job->id }}'
            },
            beforeSend: function () {

            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage('success', 'Success', res.message);
                    LoadData();
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function CloseJob()
{
    $.ajax({
            url:"{{ route('job_close') }}",
            type:'POST',
            data:{
                    'job_id': '{{ $job->id }}'
            },
            beforeSend: function () {

            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage('success', 'Success', res.message);
                    window.location = res.url;
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function Refresh()
{
    LoadData();
}

function ShowModalCloseContractor(schedule_id)
{
    removeMessageModal();

    $('.modal-title').html('Close Contractor Schedule');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Contractor Notes (if any):\
                                </label>\
                                <div class="col-md-8">\
                                  <textarea id="notes_modal" class="form-control"></textarea>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="CloseSchedule(' + schedule_id +')"><i class="fa fa-check"></i> Close Schedule</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetFromContractorNotes(schedule_id);
}

function GetFromContractorNotes(schedule_id)
{
  $.ajax({
          url:"{{ route('schedule_get_from_contractor_notes') }}",
          type:'GET',
          data:{
                  'schedule_id': schedule_id
          },
          beforeSend: function () {

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('textarea#notes_modal').val(res.contactor_notes);
                  $('#myModal').modal('show');
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function CloseSchedule(schedule_id)
{
  $.ajax({
          url:"{{ route('schedule_close') }}",
          type:'POST',
          data:{
                  'schedule_id': schedule_id,
                  'notes': $('textarea#notes_modal').val()
          },
          beforeSend: function () {

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  setStatusMessage('success', 'Success', res.message);
                  CloseModal();
                  LoadData();
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function GetInstaller(schedule_id)
{
  $.ajax({
          url:"{{ route('schedule_get_installer') }}",
          type:'GET',
          data:{
                  'schedule_id': schedule_id
          },
          beforeSend: function () {

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#user_id_modal').val(res.user_id);
                  $('#myModal').modal('show');
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function SaveInstaller(schedule_id, group_id)
{
  if($('#user_id_modal').val() == '0')
  {
      setMessageModal('danger', 'Error', 'Please select Installer');
      return;
  }

  $.ajax({
          url:"{{ route('schedule_save_installer') }}",
          type:'POST',
          data:{
                  'schedule_id': schedule_id,
                  'user_id': $('#user_id_modal').val(),
                  'job_id': '{{ $job->id }}',
                  'group_id': group_id
          },
          beforeSend: function () {

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  setStatusMessage('success', 'Success', res.message)
                  CloseModal();
                  LoadData();
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalEditCabinetOnlyInstaller(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($cabinetOnlyInstallers as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditCabinetInstallerDayOne(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($cabinetInstallerDayOnes as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditCabinetDeliveryDayOne(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($cabinetDeliveryDayOnes as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditCabinetInstallerDayTwo(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($cabinetInstallerDayTwos as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditGraniteInstallerDayTwo(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($graniteInstallerDayTwos as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditGraniteInstallerDayFour(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($graniteInstallerDayFours as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditPlumberInstallerDayFive(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($plumberInstallerDayFives as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditElectricianInstallerDayFive(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($electricianInstallerDayFives as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditTileInstaller(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($tileInstallers as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditFftInstaller(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($fftInstallers as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </seelct>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditContractorInstaller(schedule_id, title, group_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit ' + title);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Select ' + title + ':\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="user_id_modal" id="user_id_modal" class="form-control">\
                                    <option value="0">--Select ' + title + '</option>\
                                    @foreach($contractorInstallers as $installer)\
                                    <option value="{{ $installer->id }}">{{ $installer->name }}</option>\
                                    @endforeach\
                                  </select>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveInstaller(\'' + schedule_id +'\',' + group_id + ')"><i class="fa fa-check"></i> Save ' + title + '</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetInstaller(schedule_id);
}

function ShowModalEditDate(schedule_id, type)
{
    removeMessageModal();

    $start_end = 'Start';
    if(type == 'end') $start_end = 'End';
    $('.modal-title').html('Change Schedule ' + $start_end + ' Date/Time');
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
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetDate(' + schedule_id + ', \'' + type + '\')"><i class="fa fa-check"></i> Save </a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });


    GetDate(schedule_id, type);
}

function GetDate(schedule_id, type)
{
    $.ajax({
            url:"{{ route('get_schedule_date') }}",
            type:'GET',
            data:{
                  "schedule_id": schedule_id,
                  "type": type
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

function SetDate(schedule_id, type)
{
    $.ajax({
            url:"{{ route('set_schedule_date') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "schedule_id": schedule_id,
                  "type": type,
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

function ShowModalEditScheduleNotes(schedule_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit Schedule Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="notes_modal" class="col-md-4 control-label">\
                                  Notes: \
                                </label>\
                                <div class="col-md-8">\
                                    <textarea id="notes_modal" class="form-control"></textarea>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveNotes(' + schedule_id +')"><i class="fa fa-check"></i> Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetNotes(schedule_id);
}

function GetNotes(schedule_id)
{
    $.ajax({
            url:"{{ route('get_schedule_notes') }}",
            type:'GET',
            data:{
                  "schedule_id": schedule_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('textarea#notes_modal').val(res.notes)
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SaveNotes(schedule_id)
{
    $.ajax({
            url:"{{ route('set_schedule_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "schedule_id": schedule_id,
                  "notes": $('textarea#notes_modal').val()
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

function ShowModalEditCustomerNotes(schedule_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit Customer Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="notes_modal" class="col-md-4 control-label">\
                                  Notes: \
                                </label>\
                                <div class="col-md-8">\
                                    <textarea id="notes_modal" class="form-control"></textarea>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveCustomerNotes(' + schedule_id +')"><i class="fa fa-check"></i> Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCustomerNotes(schedule_id);
}

function GetCustomerNotes(schedule_id)
{
    $.ajax({
            url:"{{ route('get_schedule_customer_notes') }}",
            type:'GET',
            data:{
                  "schedule_id": schedule_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('textarea#notes_modal').val(res.customer_notes)
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SaveCustomerNotes(schedule_id)
{
    $.ajax({
            url:"{{ route('set_schedule_customer_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "schedule_id": schedule_id,
                  "customer_notes": $('textarea#notes_modal').val()
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

</script>
@endsection
