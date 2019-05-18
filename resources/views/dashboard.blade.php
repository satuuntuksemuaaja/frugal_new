@extends('layouts.main', [
'title' => 'Dashboard | Designer Totals',
'crumbs' => [
    ['text' => "Dashboard"]
]])
@section('content')
<div class="container-fluid">
    <div class="card-body card-colors row">
        <div class="col-lg-12 col-md-6">
            <div class="card">
                <div class="card-body">

                  <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                      <a class="nav-link active" id="weekly-tab" data-toggle="tab" href="#weekly" role="tab" aria-controls="weekly" aria-selected="true"><i class="fa fa-calendar"></i> Weekly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="monthly-tab" data-toggle="tab" href="#monthly" role="tab" aria-controls="monthly" aria-selected="false"><i class="fa fa-calendar"></i> Monthly</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" id="yearly-tab" data-toggle="tab" href="#yearly" role="tab" aria-controls="yearly" aria-selected="false"> <i class="fa fa-calendar"></i> Yearly</a>
                    </li>
                  </ul>
                  <div class="tab-content" id="myTabContent">

                    <div class="tab-pane fade show active" id="weekly" role="tabpanel" aria-labelledby="weekly-tab">
                      <br/>
                      <table class="table weeklyTable table-striped mt-2" id="weeklyTable">
                          <thead>
                          <th>Item</th>
                          <th>Mon</th>
                          <th>Tue</th>
                          <th>Wed</th>
                          <th>Thu</th>
                          <th>Fri</th>
                          <th>Sat</th>
                          <th>Sun</th>
                          </thead>
                          <tbody id="body_weekly"></tbody>
                      </table>
                    </div>

                    <div class="tab-pane fade" id="monthly" role="tabpanel" aria-labelledby="monthly-tab">
                      <br/>
                      <table class="table monthlyTable table-striped mt-2" id="monthlyTable">
                          <thead>
                          <th>Item</th>
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
                          <th>YTD</th>
                          </thead>
                          <tbody id="body_monthly"></tbody>
                      </table>
                    </div>

                    <div class="tab-pane fade" id="yearly" role="tabpanel" aria-labelledby="yearly-tab">
                      <br/>
                      <li><a href="#" onclick="LoadDataYearly(2018);">Show yearly for 2018</a></li>
                      <li><a href="#" onclick="LoadDataYearly(2017);">Show yearly for 2017</a></li>
                      <li><a href="#" onclick="LoadDataYearly(2016);">Show yearly for 2016</a></li>
                      <br/>
                      <b>Year: </b><span id="year" name="year"></span>
                      <table class="table yearlyTable table-striped mt-2" id="yearlyTable">
                          <thead>
                          <th>Item</th>
                          <th>Total</th>
                          </thead>
                          <tbody id="body_yearly"></tbody>
                      </table>
                    </div>

                  </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){
LoadDataWeekly();
LoadDataMonthly();
LoadDataYearly(2018);

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(Request::get('upload_file') == 1)
    ShowModalDrawing({{ Request::get('quote_id') }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif

});

function LoadDataWeekly()
{
  $.ajax({
          url:"{{ route('get_dashboard_weekly') }}",
          type:'GET',
          data:{

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_weekly').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadDataMonthly()
{
  $.ajax({
          url:"{{ route('get_dashboard_monthly') }}",
          type:'GET',
          data:{

          },
          beforeSend: function () {
              $('#body_monthly').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_monthly').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function LoadDataYearly(year)
{
  $.ajax({
          url:"{{ route('get_dashboard_yearly') }}",
          type:'GET',
          data:{
                  "year": year
          },
          beforeSend: function () {
              $('#year').html("Loading....");
              $('#body_yearly').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_yearly').html(res.data);
                  $('#year').html(year);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function ShowModalLeadUpdates(status, user_id, startDate, endDate)
{
    removeMessageModal();

    $('.modal-title').html('Detail');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table leadUpdatesTable table-striped mt-2" id="leadUpdatesTable">\
                                  <thead>\
                                  <th>Lead</th>\
                                  <th>Updated</th>\
                                  <th>Status</th>\
                                  <th>Quote</th>\
                                  <th>Commision Amount</th>\
                                  </thead>\
                                  <tbody id="body_lead_updates"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetLeadUpdates(status, user_id, startDate, endDate);
}

function GetLeadUpdates(status, user_id, startDate, endDate)
{
  $.ajax({
          url:"{{ route('get_dashboard_lead_updates') }}",
          type:'GET',
          data:{
                  "startDate": startDate,
                  "endDate": endDate,
                  "status": status,
                  "user_id": user_id
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_lead_updates').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_lead_updates').html(res.data);
              }
          },
          error: function(a, b, c)
          {

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

</script>
@endsection
