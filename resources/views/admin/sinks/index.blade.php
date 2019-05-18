@extends('layouts.main', [
'title' => "Sinks",
'crumbs' => [
    ['text' => "Sinks"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-header bg-primary text-white"><b>Active Sink</b></div>
              <div class="card-body">
                <table class="table table-striped mt-2" id="sinkTable">
                    <thead>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Material</th>
                    <th>Delete</th>
                    </thead>
                    <tbody></tbody>
                </table>
              </div>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Sink</b></div>
                <form id="form_sink" action="#" method="post" enctype="multipart/form-data">
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Sink Name:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="name" name="name" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Price:
                    </label>
                    <div class="col-md-8 input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                      </div>
                      <input type="text" id="price" name="price" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Material:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="material" name="material" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Image:
                    </label>
                    <div class="col-md-8">
                      <input type="file" id="image" name="image" class="form-control" />
                      <img src="" id="image_preview" name="image_preview" width="200px;" height="200px" style="display:none;" />
                    </div>
                  </div>
                </div>
                <input type="hidden" name="sink_id" id="sink_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <button href="#" class="btn btn-success" id="btn_add"><i class="fa fa-check"></i> Add New</button>
                    <button href="#" class="btn btn-info" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</button>
                    <a href="#" class="btn btn-danger" id="btn_redeactivate" onclick="RedeactivateSink();" style="display:none;"><i class="fa fa-save"></i> <span id="span_btn_redeactivate">Reactivate</span></a>
                </div>
              </form>
            </div>
            <br/>
            <div class="card">
                <div class="card-header bg-danger text-white"><b>Inactive Sink</b></div>
                <div class="card-body">
                  <table class="table table-striped mt-2" id="inactiveSinkTable">
                      <thead>
                      <th>Name</th>
                      <th>Price</th>
                      <th>Material</th>
                      <th>Delete</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){

  LoadData();
  LoadDataInactive();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif

  $("#btn_add").click(function(event) {
    event.preventDefault();
    var form = $('#form_sink')[0];
    var data = new FormData(form);

    if($('#name').val() == '' || $('#price').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please input name and price.');
        return;
    }

    $.ajax({
            url:"{{ route('sinks.store') }}",
            type: "POST",
            data: data,
            enctype: 'multipart/form-data',
            processData: false,  // Important!
            contentType: false,
            cache: false,
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                    LoadDataInactive();
                }
                else
                {
                    setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
  });

  $("#btn_update").click(function(event) {
    event.preventDefault();
    var form = $('#form_sink')[0];
    var data = new FormData(form);

    if($('#name').val() == '' || $('#price').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please input name and price.');
        return;
    }

    $.ajax({
            url:"{{ route('update_sink') }}",
            type: "POST",
            data: data,
            enctype: 'multipart/form-data',
            processData: false,  // Important!
            contentType: false,
            cache: false,
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                    LoadDataInactive();
                }
                else
                {
                    setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
  });

});

function LoadData()
{
    $('#sinkTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_sinks') }}",
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
        { width: "30%" },
        {},
        {}
      ]
    });
}

function LoadDataInactive()
{
    $('#inactiveSinkTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_inactive_sinks') }}",
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
        { width: "30%" },
        {},
        {}
      ]
    });
}

function EditSink(sink_id)
{
    $.ajax({
            url:"{{ route('get_sink') }}",
            type:'GET',
            data:{
                  'sink_id' : sink_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html('Edit ' + res.name);
                    $('#name').val(res.name);
                    $('#price').val(res.price);
                    $('#material').val(res.material);
                    if(res.image)
                    {
                      $('#image_preview').attr('src', '{{ url("") }}/storage/' + res.image);
                      $('#image_preview').attr('style', '');
                    }
                    else
                    {
                      $('#image_preview').attr('src', '');
                      $('#image_preview').attr('style', 'display:none;');
                    }
                    $('#sink_id').val(res.sink_id);
                    $('#btn_update').attr('style', '');
                    $('#btn_add').attr('style', 'display:none;');
                    $('#btn_redeactivate').attr('style', '');
                    $('#btn_redeactivate').attr('onclick', 'RedeactivateSink(' + res.sink_id + ')');
                    if(res.active == '1') $('#span_btn_redeactivate').html('Deactivate');
                    else $('#span_btn_redeactivate').html('Reactivate');
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function DeleteSink(sink_id)
{
    $.ajax({
            url:"{{ route('delete_sink') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'sink_id' : sink_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                    LoadDataInactive();
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function RedeactivateSink(sink_id)
{
    $.ajax({
            url:"{{ route('redeactivate_sink') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'sink_id' : sink_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                    LoadDataInactive();
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ClearForm()
{
    $('#title').html('New Sink');
    $('#name').val('');
    $('#price').val('');
    $('#material').val('');
    var $el = $('#image');
    $el.wrap('<form>').closest('form').get(0).reset();
    $el.unwrap();
    $('#image_preview').attr('src', '');
    $('#image_preview').attr('style', 'display:none;');
    $('#sink_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
    $('#btn_redeactivate').attr('style', 'display:none;');
}

</script>
@endsection
