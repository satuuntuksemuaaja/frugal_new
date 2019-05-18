@extends('layouts.main', [
'title' => "Reports",
'crumbs' => [
    ['text' => "Reports"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="btn-group">
                  <a href="#" class="btn btn-sm btn-primary" onclick="ShowLeadUserReport();"><i class="fa fa-question"></i><br>Lead Sources
                  </a>
                  <a href="{{ route('cabinet_report') }}" class="btn btn-sm btn-warning"><i class="fa fa-question"></i><br>Cabinets
                  </a>
                  <a href="{{ route('designers_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Designers
                  </a>
                  <a href="{{ route('locations_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Showrooms
                  </a>
                  <a href="{{ route('promotions_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Promotions
                  </a>
                  <a href="{{ route('finished_job_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Finished Job
                  </a>
                  <a href="{{ route('export_leads') }}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Download Leads Report
                  </a>
                  <a href="{{ route('export_zips') }}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Download Zip Report
                  </a>
                  <a href="#" class="btn btn-sm btn-info" onclick="ShowFrugalReport();"><i class="fa fa-money"></i><br>Frugal Report
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
                <option value="All Job Types">All Job Types</option>
                @foreach($quoteTypes as $quoteType)
                  <option value="{{ $quoteType->name }}">{{ $quoteType->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <a href="#" class="btn btn-primary" onclick="LoadAllReports();"><i class="fa fa-save"></i> Set Date Range for Reports</a>
          </div>
        </div>
    </div>
    <div class="row" id="row_leads">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table leadsTable table-striped mt-2 table-bordered" id="leadsTable">
                    <thead>
                    <th>Lead Type</th>
                    <th>Count</th>
                    <th>Sold</th>
                    <th>Provided</th>
                    <th>Sold From Provided</th>
                    </thead>
                    <tbody id="body_leads_report"></tbody>
                </table>
            </div>
        </div>
    </div>
    <br/>
    <div class="row" id="row_users">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table usersTable table-striped mt-2 table-bordered" id="usersTable">
                    <thead>
                    <th>User</th>
                    <th>Count</th>
                    <th>Sold</th>
                    <th>Provided</th>
                    </thead>
                    <tbody id="body_users_report"></tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row" id="row_frugal" style="display:none;">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table frugalTable table-striped mt-2 table-bordered" id="frugalTable">
                    <thead>
                    <th>Designer</th>
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
                    <tbody id="body_frugal_report"></tbody>
                </table>
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

$(function(){

$('#start').datetimepicker({
                format: 'L'
            });
$('#start').val('01/01/2018');

$('#end').datetimepicker({
                format: 'L'
            });
$('#end').val('12/31/2018');

LoadAllReports();
@if($request->has('tab'))
  @if($request->tab == 'frugal')
    ShowFrugalReport();
  @endif
@endif

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadAllReports()
{
    LoadLeadsReport();
    LoadUsersReport();
    LoadFrugalReport();
}

function LoadLeadsReport()
{
  $.ajax({
          url:"{{ route('get_leads_report') }}",
          type:'GET',
          data:{
                  "start": $('#start').val(),
                  "end": $('#end').val(),
                  "type": $('#type').val()
          },
          beforeSend: function () {
              $('#body_leads_report').html("Loading.... <b><font color='red'>(Give this a minute to load..)</font></b>");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_leads_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalSource(source_id, type, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for <span id="source_name"></span>');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table sourceTable table-striped mt-2" id="sourceTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Lead Created</th>\
                                  </thead>\
                                  <tbody id="body_sources"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetSource(source_id, type, start, end);
}

function GetSource(source_id, type, start, end)
{
  $.ajax({
          url:"{{ route('get_dashboard_source_type') }}",
          type:'GET',
          data:{
                  "source_id": source_id,
                  "type": type,
                  "start": start,
                  "end": end
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_sources').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_sources').html(res.data);
                  $('#source_name').html(res.source_name);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalUser(user_id, type, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for <span id="user_name"></span>');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table userTable table-striped mt-2" id="userTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote</th>\
                                  <th>Type</th>\
                                  </thead>\
                                  <tbody id="body_users"></tbody>\
                              </table>\
                              Total:&nbsp;<span id="total"></span>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetUser(user_id, type, start, end);
}

function GetUser(user_id, type, start, end)
{
  $.ajax({
          url:"{{ route('get_dashboard_user_type') }}",
          type:'GET',
          data:{
                  "user_id": user_id,
                  "type": type,
                  "start": start,
                  "end": end
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_users').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_users').html(res.data);
                  $('#user_name').html(res.user_name);
                  $('#total').html(res.total);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadUsersReport()
{
  $.ajax({
          url:"{{ route('get_users_report') }}",
          type:'GET',
          data:{
                  "start": $('#start').val(),
                  "end": $('#end').val(),
                  "type": $('#type').val()
          },
          beforeSend: function () {
              $('#body_users_report').html("Loading.... <b><font color='red'>(This will take a minute too.)</font></b>");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_users_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadFrugalReport()
{
  $.ajax({
          url:"{{ route('frugal_report') }}",
          type:'GET',
          data:{
                  "type": $('#type').val(),
                  "start": $('#start').val(),
                  "end": $('#end').val()
          },
          beforeSend: function () {
              $('#body_frugal_report').html("Loading.... <b><font color='red'>(Give this a minute to load..)</font></b>");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_frugal_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowLeadUserReport()
{
    $('#row_leads').attr('style', '');
    $('#row_users').attr('style', '');
    $('#row_frugal').attr('style', 'display:none;');
}

function ShowFrugalReport()
{
    $('#row_leads').attr('style', 'display:none;');
    $('#row_users').attr('style', 'display:none;');
    $('#row_frugal').attr('style', '');
}

function ShowModalDesignerFrugalReport(user_name, user_id, month, start, end)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + user_name + ' for month ' + month);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table designerReportTable table-striped mt-2" id="designerReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Amount</th>\
                                  <th>Profit</th>\
                                  </thead>\
                                  <tbody id="body_designer_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetDesignerFrugalReport(user_id, month, start, end);
}

function GetDesignerFrugalReport(user_id, month, start, end)
{
  var profit = 1;
  $.ajax({
          url:"{{ route('get_designers_detail_report') }}",
          type:'GET',
          data:{
                  "user_id": user_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "profit": profit
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_designer_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_designer_report').html(res.data);
                  $('[data-toggle="popover"]').popover();
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

</script>
@endsection
