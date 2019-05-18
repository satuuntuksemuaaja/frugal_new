@extends('layouts.main', [
'title' => "Tasks",
'crumbs' => [
    ['text' => "Tasks"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalAddTask();">
                        <i class="fa fa-plus"></i> Create Task
                    </a>
                </div>
                <table class="table quoteTable table-striped mt-2" id="taskTable">
                    <thead>
                    <th>Id</th>
                    <th>Task</th>
                    <th>Assigned</th>
                    <th>Customer</th>
                    <th>Job</th>
                    <th>Created</th>
                    <th>Created By</th>
                    <th>Last Updated</th>
                    <th>Due</th>
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
    $('#taskTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_tasks') }}",
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
        {},
        {}
      ]
    });
}

function ShowModalAddTask()
{
    removeMessageModal();

    $('.modal-title').html('Create Task');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <b>Selecting Fields You do not need to select a customer and a job, just one or the other. If this task is generic and not related to a customer or job, just leave them blank. If there is no due date, leave blank.</b>\
                              </div>\
                              \
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
                                <label for="customer_id" class="col-md-4 control-label">\
                                  Customer:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="customer_id" id="customer_id">\
                                    <option value="0">-- No Customer -- </option>\
                                    @foreach($customers as $customer)\
                                      <option value="{{ $customer->id }}">{{ $customer->name }}</option>\
                                    @endforeach\
                                    </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="customer_id" class="col-md-4 control-label">\
                                  Job:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="job_id" id="job_id">\
                                    <option value="0">-- No Job -- </option>\
                                    @foreach($userGroups as $userGroup)\
                                      <option value="{{ $userGroup->id }}">{{ $userGroup->name }} ({{ $userGroup->group_name }} - {{ $userGroup->id }})</option>\
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
                              <a href="#" id="btn_create_task" class="btn btn-primary" onclick="SaveTask()"><i class="fa fa-plus"></i> Create Task</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');



    $('#myModal').modal('show');
}

function SaveTask()
{
    var urgent = '0';
    if($('#urgent').is(":checked")) urgent = '1';
    $.ajax({
            url:"{{ route('save_task') }}",
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
                  "customer_id": $('#customer_id').val(),
                  "job_id": $('#job_id').val(),
                  "assigned_id": $('#assigned_id').val(),
                  "body": $("textarea#body").val()
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
                  $('#btn_create_task').attr('onclick', 'SaveTask()');
                  $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
                $('#btn_create_task').attr('onclick', 'SaveTask()');
                $('#btn_create_task').html('<i class="fa fa-plus"></i> Create Task');
            }
          });
}

function ShowModalTaskNotes(task_id, subject)
{
    removeMessageModal();

    $('.modal-title').html('Task Notes | ' + subject);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                  <div class="card col-md-12">\
                                    <div id="div_task_note_body" class="card">\
                                    </div>\
                                  </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                  <div class="card col-md-12">\
                                    <div class="panel panel-primary">\
                                      <div class="panel-heading">\
                                        <h3 class="panel-title">\
                                          <b>New Note</b>\
                                        </h3>\
                                      </div>\
                                      <div class="panel-body">\
                                        <fieldset>\
                                          <div class="form-group  body_l">\
                                            <div class="col-sm-8">\
                                              <textarea class="form-control " name="task_note_body" rows="4" placeholder="" id="task_note_body"></textarea>\
                                            </div>\
                                          </div>\
                                        </fieldset>\
                                     </div>\
                                    <footer class="panel-footer">\
                                      <b>\
                                        <div class="btn-group">\
                                            <a href="#" class="btn btn-primary post  " id="btn_add_note" onclick="SaveTaskNote(' + task_id + ')">\
                                              <i class="fa fa-plus "></i>\
                                                Add Note \
                                            </a>\
                                        </div>\
                                        <div class="btn-group">\
                                            <a href="#" class="btn btn-danger get  " id="btn_close_task" onclick="CloseTask(' + task_id + ')">\
                                              <i class="fa fa-trash-o "></i>\
                                                Close Task \
                                            </a>\
                                        </div>\
                                      </b>\
                                    </footer>\
                                  </div>\
                                </div>\
                              </div>\
                              \
                              <div id="div_task_notes" class="card">\
                              </div>\
                          ');

    $('#due_date').datetimepicker({
                    format: 'L'
                });

    $('#due_time').datetimepicker({
                    format: 'LT'
                });

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetTaskNote(task_id);
}

function GetTaskNote(task_id)
{
    $.ajax({
            url:"{{ route('get_task_note') }}",
            type:'GET',
            data:{
                  "task_id": task_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#div_task_note_body').html(res.body);
                    $('#div_task_notes').html(res.notes);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SaveTaskNote(task_id)
{
  $.ajax({
          url:"{{ route('save_task_note') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_close_task').attr('onclick', '');
               $('#btn_close_task').html('<i class="fa fa-spinner"></i> Closing Task...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "task_id": task_id,
                "body": $('textarea#task_note_body').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                GetTaskNote(task_id);
                $('textarea#task_note_body').val('');
                $('#btn_close_task').attr('onclick', 'CloseTask(' + task_id + ')');
                $('#btn_close_task').html('<i class="fa fa-plus"></i> Close Task');
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_close_task').attr('onclick', 'CloseTask(' + task_id + ')');
                $('#btn_close_task').html('<i class="fa fa-plus"></i> Close Task');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_close_task').attr('onclick', 'CloseTask(' + task_id + ')');
              $('#btn_close_task').html('<i class="fa fa-plus"></i> Close Task');
          }
        });
}

function CloseTask(task_id)
{
  $.ajax({
          url:"{{ route('close_task') }}",
          type:'POST',
          beforeSend: function() {
               $('#btn_close_task').attr('onclick', '');
               $('#btn_close_task').html('<i class="fa fa-spinner"></i> Closing Task...');
          },
          data:{
                "_token":"{{ csrf_token() }}",
                "task_id": task_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadData();
                $('textarea#task_note_body').val('');
                $('#btn_close_task').attr('onclick', 'SaveTaskNote(' + task_id + ')');
                $('#btn_close_task').html('<i class="fa fa-trash-o"></i> Close Task');
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
                $('#btn_close_task').attr('onclick', 'SaveTaskNote(' + task_id + ')');
                $('#btn_close_task').html('<i class="fa fa-trash-o"></i> Close Task');
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
              $('#btn_close_task').attr('onclick', 'SaveTaskNote(' + task_id + ')');
              $('#btn_close_task').html('<i class="fa fa-trash-o"></i> Close Task');
          }
        });
}

</script>
@endsection
