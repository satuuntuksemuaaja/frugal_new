@extends('layouts.main', [
'title' => "Cabinet Reports",
'crumbs' => [
    ['text' => "Cabinet Reports"]
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
            <label for="end" class="col-md-1 control-label">
              Vendor:
            </label>
            <div class="col-md-3">
              <select name="vendor_id" id="vendor_id" class="form-control">
                <option value=''>All</option>
                @foreach($vendors as $vendor)
                  <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="form-group form-row ">
            <a href="#" class="btn btn-primary" onclick="LoadCabinetReport();"><i class="fa fa-save"></i> Set Date Range for Reports</a>
          </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                </div>
                <table class="table cabinetsTable table-striped mt-2 table-bordered" id="cabinetsTable">
                    <thead>
                      <th>Cabinet</th>
                      <th>Jan (Quotes-Sold)</th>
                      <th>Feb (Quotes-Sold)</th>
                      <th>Mar (Quotes-Sold) </th>
                      <th>Apr (Quotes-Sold)</th>
                      <th>May (Quotes-Sold)</th>
                      <th>Jun (Quotes-Sold)</th>
                      <th>Jul (Quotes-Sold)</th>
                      <th>Aug (Quotes-Sold)</th>
                      <th>Sep (Quotes-Sold)</th>
                      <th>Oct (Quotes-Sold)</th>
                      <th>Nov (Quotes-Sold)</th>
                      <th>Dec (Quotes-Sold)</th>
                      <th>TTL (Quotes-Sold)</th>
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

$('#start').datetimepicker({
                format: 'L'
            });

$('#end').datetimepicker({
                format: 'L'
            });

LoadCabinetReport();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadCabinetReport()
{
      $('#cabinetsTable')
      .dataTable({
        "ajax": {
                    url: "{{ route('get_cabinets_report') }}",
                    data: {
                              "start": $('#start').val(),
                              "end": $('#end').val(),
                              "vendor_id": $('#vendor_id').val()
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

function ShowModalCabinetReport(cabinet_name, cabinet_id, month, start, end, type)
{
    removeMessageModal();

    $('.modal-title').html('Report for ' + cabinet_name + ' for month ' + month + ' | ' + type);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                              <table class="table cabinetsReportTable table-striped mt-2" id="cabinetsReportTable">\
                                  <thead>\
                                  <th>Customer</th>\
                                  <th>Quote Created</th>\
                                  <th>Job Created</th>\
                                  <th>Total</th>\
                                  </thead>\
                                  <tbody id="body_cabinet_report"></tbody>\
                              </table>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCabinetReport(cabinet_id, month, start, end, type);
}

function GetCabinetReport(cabinet_id, month, start, end, type)
{
  $.ajax({
          url:"{{ route('get_cabinets_detail_report') }}",
          type:'GET',
          data:{
                  "cabinet_id": cabinet_id,
                  "month": month,
                  "start": start,
                  "end": end,
                  "type": type
          },
          beforeSend: function () {
              $('#myModal').modal('show');
              $('#body_cabinet_report').html("Loading....");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_cabinet_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

</script>
@endsection
