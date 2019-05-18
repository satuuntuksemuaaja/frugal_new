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
        <div class="col-lg-12">
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
LoadDataJobCabinet();
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  //auto refresh every 30 secs
  @if(!is_null(Request::get('refresh')))
    setTimeout(function() {
    location.reload();
  }, 30000);
  @endif

});

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
</script>
@endsection
