@extends('layouts.main', [
'title' => "Build Status | Buildup Manager",
'crumbs' => [
    ['text' => "Buildup"]
]])
@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('receiving') }}" class="btn btn-warning btn-lg">
                        <i class="fa fa-arrow-right"></i> Go to Receiving
                    </a>
                    <a href="{{ route('shop') }}" class="btn btn-danger btn-lg">
                        <i class="fa fa-wrench"></i> Shop Work
                    </a>
                    @if(is_null(Request::get('refresh')))
                    <a href="{{ route('buildup') }}?refresh=true" class="btn btn-info btn-lg">
                        <i class="fa fa-refresh"></i> Start Refresh (30 sec)
                    </a>
                    @else
                    <a href="{{ route('buildup') }}" class="btn btn-info btn-lg">
                        <i class="fa fa-refresh"></i> Cancel Refresh
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <a href="#" class="btn btn-primary" onclick="ShowModalAddShopWork();">
                        <i class="fa fa-plus"></i> Add Shop Work
                    </a>
                    <a href="{{ route('buildup') }}" class="btn btn-info">
                        <i class="fa fa-arrow-right"></i> Back to Buildup
                    </a>
                </div>
            </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                  <table class="table table-striped mt-2" id="jobSoldTable">
                      <thead>
                      <th>Job</th>
                      <th>Sold</th>
                      <th>Starts On</th>
                      <th>Cabinets</th>
                      <th>Build Up</th>
                      <th>Load Status</th>
                      <th>Notes</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                  <table class="table table-striped mt-2" id="jobCabinetTable">
                      <thead>
                      <th>Job</th>
                      <th>Cabinet</th>
                      <th>Notes</th>
                      <th>Status</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script type="text/javascript">

$(function(){
LoadDataJobSold();
LoadDataJobCabinet();
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(Request::get('upload_file') == 1)
    ShowModalDrawing({{ Request::get('quote_id') }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif

  //auto refresh every 30 secs
  @if(!is_null(Request::get('refresh')))
    setTimeout(function() {
    location.reload();
  }, 30000);
  @endif

});

function LoadDataJobSold()
{
    $('#jobSoldTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('buildup_job_sold') }}",
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
        {}
      ]
    });
}

function ShowModalDrawing(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('File/Designs');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table fileTable table-striped mt-2" id="fileTable">\
                                    <thead>\
                                    <th></th>\
                                    <th>Description</th>\
                                    <th>Uploaded By</th>\
                                    <th>Attach to Contractors</th>\
                                    <th>Delete</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                              <form id="form_upload_file" role="form" action="{{ url('build') }}/' + quote_id + '/upload_file" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  File Description:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="description" id="description" required>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="file" class="col-md-4 control-label">\
                                  Select File:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" class="form-control" name="file" id="file" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select the files</p>\
                                </div>\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary">Upload</button>\
                              \
                              </form>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataFiles(quote_id);
}

function LoadDataFiles(quote_id)
{
    $('#fileTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_files') }}",
                  data: {
                          'quote_id': quote_id
                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      "ordering": false,
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
        {},
        {}
      ]
    });
}

function ShowModalBuildNote(job_id)
{
    removeMessageModal();

    $('.modal-title').html('Add Buildup Note');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="note" class="col-md-4 control-label">\
                                Enter Note:\
                              </label>\
                              <div class="col-md-8">\
                                <textarea class="form-control" name="note" id="note" required></textarea>\
                              </div>\
                            </div>\
                            \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveBuildupNote(' + job_id + ')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveBuildupNote(job_id)
{
    $.ajax({
            url:"{{ route('save_buildup_note') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": job_id,
                  "note": $('textarea#note').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadDataJobSold();
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

function LoadDataJobCabinet()
{
    $('#jobCabinetTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('buildup_job_cabinet') }}",
                  data: {

                        }
              },
      "bDeferRender": true,
      "searching": true,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      "ordering": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "createdRow": function( row, data, dataIndex){
          if( data[4] ==  'blue'){ $(row).css('background-color', '#dff3f9'); }
      },
      "fnInitComplete": function(oSettings, json) {

      },
      "aoColumns" : [
        { "orderable": false },
        { "orderable": false },
        { "orderable": false },
        { "orderable": false }
      ]
    });
}

function ShowModalAddShopWork()
{
    removeMessageModal();

    $('.modal-title').html('Create a new Shop Work Order');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="note" class="col-md-4 control-label">\
                                Select Job:\
                              </label>\
                              <div class="col-md-8">\
                                <select class="form-control" name="job_id" id="job_id">\
                                  <option value="">--Select Job--</option>\
                                  <?php echo $jobOpt; ?>\
                                </select>\
                              </div>\
                            </div>\
                            \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveShopWork()">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveShopWork()
{
  $.ajax({
          url:"{{ route('save_shop') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "job_id": $('#job_id').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                window.location = res.url;
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

function ShowModalCabinetNotes(shop_cabinet_id)
{
    removeMessageModal();

    $('.modal-title').html('Cabinet Notes');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="note" class="col-md-4 control-label">\
                                Notes:\
                              </label>\
                              <div class="col-md-8">\
                                <textarea id="notes" name="notes" class="form-control"></textarea>\
                              </div>\
                            </div>\
                            \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveCabinetNotes(' + shop_cabinet_id + ')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCabinetNotes(shop_cabinet_id);
}

function GetCabinetNotes(shop_cabinet_id)
{
  $.ajax({
          url:"{{ route('get_cabinet_notes') }}",
          type:'GET',
          data:{
                "shop_cabinet_id": shop_cabinet_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('textarea#notes').val(res.notes);
                  $('#myModal').modal('show');
              }
              else if(res.response == "error")
              {
                //setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              //setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function SaveCabinetNotes(shop_cabinet_id)
{
  $.ajax({
          url:"{{ route('save_cabinet_notes') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "shop_cabinet_id": shop_cabinet_id,
                "notes": $('textarea#notes').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataJobCabinet();
                CloseModal();
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

function DoDeleteFile(quote_id, file_id)
{
    $.ajax({
            url:"{{ route('quote_delete_file') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "file_id": file_id,
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  LoadDataFiles(quote_id);
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
