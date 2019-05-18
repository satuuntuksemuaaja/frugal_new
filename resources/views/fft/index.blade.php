@extends('layouts.main', [
'title' => $title,
'crumbs' => [
    ['text' => $title]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowAll();">
                        <i class="fa fa-refresh"></i> Show All
                    </a>
                    <a href="#" class="btn btn-warning mb-2" onclick="ShowClosed();">
                        <i class="fa fa-refresh"></i> Show Closed
                    </a>
                    <a href="#" class="btn btn-success mb-2" onclick="ShowOpened();">
                        <i class="fa fa-refresh"></i> Show Opened
                    </a>

                      <!--Show:
                      <select id="length" class="form-control" style="width:80px;" onchange="LoadData();">
                          <option value="10" selected>10</option>
                          <option value="25">25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                      </select>-->
                </div>
                <table class="table quoteTable table-striped mt-2" id="fftTable">
                    <thead>
                    <th>Customer</th>
                    <th>Started</th>
                    <th>Hours</th>
                    <th>Visit Assigned</th>
                    <th>Visit Scheduled</th>
                    <th>Punch Assigned</th>
                    <th>Punch Scheduled</th>
                    <th>Punches</th>
                    <th>Notes</th>
                    <th>Schedules</th>
                    <th>Payment Received</th>
                    </thead>
                    <tbody></tbody>
                </table>

                @if(isset($warranty) && $warranty == '1')
                <div class="card-body">
                  <a href="#" class="btn btn-info" onclick="ShowModalAddWarranty();"><i class="fa fa-plus"></i> Create New Warranty</a>
                </div>
                @endif

                @if(isset($service) && $service == '1')
                <div class="card-body">
                  <a href="#" class="btn btn-info" onclick="ShowModalAddService();"><i class="fa fa-plus"></i> Create New Service Work</a>
                </div>
                @endif
				
				<!--
                <div class="card-body">
                  <span class='badge' style='color: #000; font-size: 14px; background-color: #92f509;'>Items Received</span>
                  <span class='badge' style='color: #000; font-size: 14px; background-color: #edf903;'>Needs Walkthrough</span>
                  <span class='badge' style='color: #fff; font-size: 14px; background-color: #f5024ab3;'>Walkthrough Scheduled</span>
                  <span class='badge' style='color: #fff; font-size: 14px; background-color: #f10a0ad9;'>Punch Not Signed</span>
                  <span class='badge' style='color: #fff; font-size: 14px; background-color: #0088cc;'>All items ordered</span>
                  <span class='badge' style='color: #fff; font-size: 14px; background-color: #ccc3bc;'>Items not ordered</span>
                  <span class='badge' style='color: #fff; font-size: 14px; background-color: #f1870a;'>Punch Scheduled</span>
                </div>
				-->

            </div>
        </div>
    </div>
    <input type="hidden" name="totalRecord" id="totalRecord" />
    <input type="hidden" name="all" id="all" value="false" />
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

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function ShowAll()
{
    $('#all').val('true');
    LoadData();
}

function ShowClosed()
{
    $('#all').val('closed');
    LoadData();
}

function ShowOpened()
{
    $('#all').val('opened');
    LoadData();
}

function LoadData(all)
{
    $('#fftTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_ffts') }}",
                  data: {
                            'all': $('#all').val(),
                            @if(isset($warranty))
                            'warranty': '{{ $warranty }}'
                            @endif
                            @if(isset($service))
                            'service': '{{ $service }}'
                            @endif
                        }
              },
      "bDeferRender": true,
      "searching": true,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"fnInitComplete": function(oSettings, json) {
      //  $('#totalRecord').val(json.iTotalRecords);
      //},
      //"pageLength": parseInt($('#length').val()),
      /* "createdRow": function( row, data, dataIndex){
          if( data[11] ==  `NeedsWalkthrough`){ $(row).css('background-color', '#edf903'); }
          if( data[11] ==  `WalkthroughScheduled`){ $(row).css('background-color', '#f5024ab3'); }
          if( data[11] ==  `PunchScheduled`){ $(row).css('background-color', '#f1870a'); }
          if( data[11] ==  `AllItemsOrdered`){ $(row).css('background-color', '#0088cc'); }
          if( data[11] ==  `ItemsNotOrdered`){ $(row).css('background-color', '#ccc3bc'); }
          if( data[11] ==  `ItemsReceived`){ $(row).css('background-color', '#92f509'); }
          if( data[11] ==  `PunchNotSigned`){ $(row).css('background-color', '#f10a0ad9'); }

      }, */
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
        {},
        {},
        {}
      ]
    });
}

function ShowModalFftNotes(fft_id)
{
    removeMessageModal();

    $('.modal-title').html('FFT Payment Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table fftNoteTable table-striped mt-2" id="fftNoteTable">\
                                    <thead>\
                                    <th>Time</th>\
                                    <th>Category</th>\
                                    <th>Notes</th>\
                                    <th>User</th>\
                                    <th>Action</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="note" class="col-md-4 control-label">\
                                  Category:\
                                </label>\
                                <div class="col-md-8">\
                                  <select class="form-control" name="category" id="category">\
                                    @foreach($paymentCategories as $category)\
                                      <option value="{{ $category }} selected"> {{ $category }}</option>\
                                    @endforeach\
                                  </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="note" class="col-md-4 control-label">\
                                  Note:\
                                </label>\
                                <div class="col-md-8">\
                                  <textarea class="form-control" name="note" id="note" required></textarea>\
                                </div>\
                              </div>\
                              \
                              <a href="#" id="btn_add_fft_note" class="btn btn-primary" onclick="SaveFftNote(' + fft_id + ')"><i class="fa fa-plus"> Add Note</i></a>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataFftNotes(fft_id);
}

function LoadDataFftNotes(fft_id)
{
    $('#fftNoteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_fft_notes') }}",
                  data: {
                          'fft_id': fft_id
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

function SaveFftNote(fft_id)
{
  $.ajax({
          url:"{{ route('save_fft_notes') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_add_fft_note').attr('onclick', '');
               $('#btn_add_fft_note').html('<i class="fa fa-spinner"></i> Adding Note...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "category": $('#category').val(),
                "note": $('textarea#note').val(),
                "fft_id": fft_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                $('#note').val('');
                $('#btn_add_fft_note').attr('onclick', 'SaveFftNote(' + fft_id + ')');
                $('#btn_add_fft_note').html('<i class="fa fa-plus"></i> Add Note');
                LoadDataFftNotes(fft_id);
                LoadData();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_add_fft_note').attr('onclick', 'SaveFftNote(' + fft_id + ')');
                $('#btn_add_fft_note').html('<i class="fa fa-plus"></i> Add Note');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_add_fft_note').attr('onclick', 'SaveFftNote(' + fft_id + ')');
              $('#btn_add_fft_note').html('<i class="fa fa-plus"></i> Add Note');
          }
        });
}

function DeleteFftNote(fft_id, fft_note_id)
{
  $.ajax({
          url:"{{ route('delete_fft_notes') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_delete_item_' + fft_note_id).attr('onclick', '');
               $('#btn_delete_item_' + fft_note_id).html('<i class="fa fa-spinner"></i> Deleting Note...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "fft_note_id": fft_note_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                LoadDataFftNotes(fft_id);
                LoadData();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_delete_item_' + fft_note_id).attr('onclick', 'DeleteFftNote(' + fft_note_id + ')');
                $('#btn_delete_item_' + fft_note_id).html('<i class="fa fa-trash"></i> Delete');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_delete_item_' + fft_note_id).attr('onclick', 'DeleteFftNote(' + fft_note_id + ')');
              $('#btn_delete_item_' + fft_note_id).html('<i class="fa fa-trash"></i> Delete');
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

function ShowModalSetPreSchedule(fft_id)
{
    removeMessageModal();

    $('.modal-title').html('Change Schedule Pre-Scheduled time.');
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
                              <a href="#" class="btn btn-primary" onclick="SetPreSchedule(\'' + fft_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });

    GetPreSchedule(fft_id);
}

function GetPreSchedule(fft_id)
{
    $.ajax({
            url:"{{ route('get_fft_pre_schedule') }}",
            type:'GET',
            data:{
                  "fft_id": fft_id
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

function SetPreSchedule(fft_id)
{
    $.ajax({
            url:"{{ route('set_fft_pre_schedule') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "fft_id": fft_id,
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

function ShowModalSetScheduleStart(fft_id)
{
    removeMessageModal();

    $('.modal-title').html('Change Schedule time.');
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
                              <a href="#" class="btn btn-primary" onclick="SetScheduleStart(\'' + fft_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#date').datetimepicker({
                    format: 'L'
                });

    $('#time').datetimepicker({
                    format: 'LT'
                });

    GetScheduleStart(fft_id);
}

function GetScheduleStart(fft_id)
{
    $.ajax({
            url:"{{ route('get_fft_schedule_start') }}",
            type:'GET',
            data:{
                  "fft_id": fft_id
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

function SetScheduleStart(fft_id)
{
    $.ajax({
            url:"{{ route('set_fft_schedule_start') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "fft_id": fft_id,
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

@if(isset($warranty) && $warranty == '1')
function ShowModalAddWarranty()
{
  removeMessageModal();

  $('.modal-title').html('Create Warranty');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="notes" class="col-md-4 control-label">\
                                Job:\
                              </label>\
                              <div class="col-md-8">\
                                  <select id="job_id" name="job_id" class="form-control">\
                                    <option value="">-- Select Job --</option>\
                                      @php echo $selectJob; @endphp\
                                  </select>\
                              </div>\
                            </div>\
                            <hr>\
                            <div class="form-group form-row ">\
                              <label for="customer_id" class="col-md-4 control-label">\
                                Select Customer:\
                              </label>\
                              <div class="col-md-8">\
                                  <select id="customer_id" name="customer_id" class="form-control">\
                                    <option value="">-- Select Customer --</option>\
                                      @foreach($customers as $customer)\
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->city }} - {{ $customer->state }})</option>\
                                      @endforeach\
                                  </select>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SaveNewWarranty();">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  $('#myModal').modal('show');
}

function SaveNewWarranty()
{
    $.ajax({
            url:"{{ route('fft_warranty_new') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": $('#job_id').val(),
                  "customer_id": $('#customer_id').val()
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
@endif

@if(isset($service) && $service == '1')
function ShowModalAddService()
{
  removeMessageModal();

  $('.modal-title').html('Create Service Work');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <b>Service items not warranty.</b>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="notes" class="col-md-4 control-label">\
                                Job:\
                              </label>\
                              <div class="col-md-8">\
                                  <select id="job_id" name="job_id" class="form-control">\
                                    <option value="">-- Select Job --</option>\
                                      @php echo $selectJob; @endphp\
                                  </select>\
                              </div>\
                            </div>\
                            <hr>\
                            <div class="form-group form-row ">\
                              <label for="customer_id" class="col-md-4 control-label">\
                                Select Customer:\
                              </label>\
                              <div class="col-md-8">\
                                  <select id="customer_id" name="customer_id" class="form-control">\
                                    <option value="">-- Select Customer --</option>\
                                      @foreach($customers as $customer)\
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->city }} - {{ $customer->state }})</option>\
                                      @endforeach\
                                  </select>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SaveNewService();">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  $('#myModal').modal('show');
}

function SaveNewService()
{
    $.ajax({
            url:"{{ route('fft_service_new') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": $('#job_id').val(),
                  "customer_id": $('#customer_id').val()
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
@endif

function ShowModalSetVisitAssigned(fft_id, user_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Visit Assigned User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Visit Assigned User:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="visit_assigned_user_id" id="visit_assigned_user_id">\
                                    <option value="">-- Select Visit Assigned User -- </option>\
                                    @foreach($visitUsers as $visitUser)\
                                      <option value="{{ $visitUser->id }}">{{ $visitUser->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the visit assigned user</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetVisitAssignedUser(\'' + fft_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetVisitAssignedUser(fft_id);
}

function GetVisitAssignedUser(fft_id)
{
    $.ajax({
            url:"{{ route('get_visit_assigned_user') }}",
            type:'GET',
            data:{
                  "fft_id": fft_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#visit_assigned_user_id').val(res.user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetVisitAssignedUser(fft_id)
{
    $.ajax({
            url:"{{ route('set_visit_assigned_user') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "fft_id": fft_id,
                  "visit_assigned_user_id": $('#visit_assigned_user_id').val()
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

function ShowModalSetPunchAssigned(fft_id, user_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Punch Assigned User');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Punch Assigned User:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="punch_assigned_user_id" id="punch_assigned_user_id">\
                                    <option value="">-- Select Punch Assigned User -- </option>\
                                    @foreach($punchUsers as $punchUser)\
                                      <option value="{{ $punchUser->id }}">{{ $punchUser->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the punch assigned user</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetPunchAssignedUser(\'' + fft_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetPunchAssignedUser(fft_id);
}

function GetPunchAssignedUser(fft_id)
{
    $.ajax({
            url:"{{ route('get_punch_assigned_user') }}",
            type:'GET',
            data:{
                  "fft_id": fft_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#punch_assigned_user_id').val(res.user_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetPunchAssignedUser(fft_id)
{
    $.ajax({
            url:"{{ route('set_punch_assigned_user') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "fft_id": fft_id,
                  "punch_assigned_user_id": $('#punch_assigned_user_id').val()
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
