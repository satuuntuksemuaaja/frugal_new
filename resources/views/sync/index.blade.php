@extends('layouts.locked', [
'title' => "Sync DB to FK2",
'crumbs' => [
    ['text' => "Sync DB to FK2"]
]])
@section('content')

  <div class="row">
      <div class="col-lg-12">
          <div class="card">
              <div class="card-body">
                  This button will do synchronizing database from fk2.
              </div>
          </div>
      </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" id="btn_sync" class="btn btn-primary" onclick="ShowModalConfirmation();">
                        <i class="fa fa-refresh"></i> Sync Now!
                    </a>
                </div>
            </div>
        </div>
      </div>
@endsection
@section('javascript')
<script type="text/javascript">

$(function(){

@if(session('success'))
  setStatusMessage('success', 'Success', '{{ session("success") }}');
@endif

});

function ShowModalConfirmation()
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('\
                            <h4><font color="red">Warning! This will truncate all current tables and re-insert all datas on FK2. This operation make takes some minutes (depend on total of datas and the server execution speed). This action cannot be undone.</font></h4><br/>\
                            Are you sure?\
                            \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="DoSync();">Yes</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">No</button>\
                            ');

    $('#myModal').modal('show');
}

function DoSync()
{
  CloseModal();
  setStatusMessage("info", "Info", 'Synchronizing run on background...this may take some minutes to finish...');
  $.ajax({
          url:"{{ route('do_sync_database') }}",
          type:'GET',
          data:{
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
              }
              else if(res.response == "error")
              {
                setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setStatusMessage("danger", "Error", "Sync error!");
          }
        });
}
</script>
@endsection
