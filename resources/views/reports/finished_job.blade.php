@extends('layouts.main', [
'title' => "Finished Job Reports",
'crumbs' => [
    ['text' => "Finished Job Reports"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="btn-group">
                  <a href="{{ route('reports') }}" class="btn btn-sm btn-primary" onclick="ShowLeadUserReport();"><i class="fa fa-question"></i><br>Lead Sources
                  </a>
                  <a href="{{ route('cabinet_report') }}" class="btn btn-sm btn-warning"><i class="fa fa-question"></i><br>Cabinets
                  </a>
                  <a href="{{ route('designers_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Designers
                  </a>
                  <a href="{{ route('locations_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Showrooms
                  </a>
                  <a href="{{ route('promotions_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Promotions
                  </a>
                  <a href="#" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Finished Job
                  </a>
                  <a href="{{ route('reports') }}?tab=frugal" class="btn btn-sm btn-info" onclick="ShowFrugalReport();"><i class="fa fa-money"></i><br>Frugal Report
                  </a>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <label for="start" class="col-md-1 control-label">
              Start:
            </label>
            <div class="col-md-3">
              <input type="text" name="start" id="start" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#start">
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <label for="end" class="col-md-1 control-label">
              End:
            </label>
            <div class="col-md-3">
              <input type="text" name="end" id="end" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#end">
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <label for="type" class="col-md-1 control-label">
              Limit To:
            </label>
            <div class="col-md-3">
              <select name="type" id="type" class="form-control">
                <option value="Lead to Close Time">Lead to Close Time</option>
                <option value="Cabinet Install Date">Cabinet Install Date</option>
                <option value="Final Payment Date">Final Payment Date</option>
                <option value="Closeout Date">Closeout Date</option>
              </select>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <a href="#" class="btn btn-primary" onclick="LoadFinishedJobReport();"><i class="fa fa-save"></i> Set Date Range for Reports</a>
          </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table finishedJobTable table-striped mt-2 table-bordered" id="finishedJobTable">
                    <thead>
                    <th>Customer</th>
                    <th>Jan</th>
                    <th>Feb</th>
                    <th>Mar</th>
                    <th>Apr</th>
                    <th>May</th>
                    <th>Jun</th>
                    <th>Jul</th>
                    <th>Aug</th>
                    <th>Sep</th>
                    <th>Oct</th>
                    <th>Nov</th>
                    <th>Dec</th>
                    <th>TTL</th>
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

$('#start').datetimepicker({
                format: 'L'
            });
$('#start').val('01/01/2018');

$('#end').datetimepicker({
                format: 'L'
            });
$('#end').val('12/31/2018');

LoadFinishedJobReport();

$('#start').datetimepicker({
                format: 'L'
            });

$('#end').datetimepicker({
                format: 'L'
            });

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadFinishedJobReport()
{
      $('#finishedJobTable')
      .dataTable({
        "ajax": {
                    url: "{{ route('get_finished_job_report') }}",
                    data: {
                              "start": $('#start').val(),
                              "end": $('#end').val(),
                              "type": $('#type').val(),
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
          {},
          {},
          {},
          {}
        ]
      });
}

function ShowModalLeadToCloseReport(user_name, lead_id, month, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + user_name + ' for month ' + month);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table leadToCloseReportTable table-striped mt-2" id="leadToCloseReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Amount</th>\
                                  @if($request->has('profit'))\
                                  <th>Profit</th>\
                                  @endif\
                                  </thead>\
                                  <tbody id="body_lead_to_close_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetLeadToCloseReport(lead_id, month, start, end);
}

function GetLeadToCloseReport(lead_id, month, start, end)
{
  var profit = 0;
  @if($request->has('profit'))
    profit = 1;
  @endif
  $.ajax({
          url:"{{ route('get_lead_to_close_detail_report') }}",
          type:'GET',
          data:{
                  "lead_id": lead_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "profit": profit
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_lead_to_close_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_lead_to_close_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalCabinetInstallDateReport(user_name, job_id, month, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + user_name + ' for month ' + month);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table cabinetInstallDateReportTable table-striped mt-2" id="cabinetInstallDateReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Amount</th>\
                                  @if($request->has('profit'))\
                                  <th>Profit</th>\
                                  @endif\
                                  </thead>\
                                  <tbody id="body_cabinet_install_date_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCabinetInstallDateReport(job_id, month, start, end);
}

function GetCabinetInstallDateReport(job_id, month, start, end)
{
  var profit = 0;
  @if($request->has('profit'))
    profit = 1;
  @endif
  $.ajax({
          url:"{{ route('get_cabinet_install_date_detail_report') }}",
          type:'GET',
          data:{
                  "job_id": job_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "profit": profit
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_cabinet_install_date_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_cabinet_install_date_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalFinalPaymentDateReport(user_name, fft_id, month, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + user_name + ' for month ' + month);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table cabinetInstallDateReportTable table-striped mt-2" id="cabinetInstallDateReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Amount</th>\
                                  @if($request->has('profit'))\
                                  <th>Profit</th>\
                                  @endif\
                                  </thead>\
                                  <tbody id="body_final_payment_date_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetFinalPaymentDateReport(fft_id, month, start, end);
}

function GetFinalPaymentDateReport(fft_id, month, start, end)
{
  var profit = 0;
  @if($request->has('profit'))
    profit = 1;
  @endif
  $.ajax({
          url:"{{ route('get_final_payment_date_detail_report') }}",
          type:'GET',
          data:{
                  "fft_id": fft_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "profit": profit
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_final_payment_date_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_final_payment_date_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalCloseoutDateReport(user_name, fft_id, month, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + user_name + ' for month ' + month);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table cabinetInstallDateReportTable table-striped mt-2" id="cabinetInstallDateReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Amount</th>\
                                  @if($request->has('profit'))\
                                  <th>Profit</th>\
                                  @endif\
                                  </thead>\
                                  <tbody id="body_closeout_date_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCloseoutDateReport(fft_id, month, start, end);
}

function GetCloseoutDateReport(fft_id, month, start, end)
{
  var profit = 0;
  @if($request->has('profit'))
    profit = 1;
  @endif
  $.ajax({
          url:"{{ route('get_closeout_date_detail_report') }}",
          type:'GET',
          data:{
                  "fft_id": fft_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "profit": profit
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_closeout_date_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_closeout_date_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

</script>
@endsection
