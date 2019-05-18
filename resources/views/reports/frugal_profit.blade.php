@extends('layouts.main', [
'title' => "Frugal Profit Reports",
'crumbs' => [
    ['text' => "Frugal Profit Reports"]
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
                  <a href="{{ route('finished_job_report') }}" class="btn btn-sm btn-info"><i class="fa fa-question"></i><br>Finished Job
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
                    <th>Job</th>
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

</script>
@endsection
