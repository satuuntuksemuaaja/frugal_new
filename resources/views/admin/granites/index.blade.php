@extends('layouts.main', [
'title' => "Granite",
'crumbs' => [
    ['text' => "Granite"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-body">
                <table class="table table-striped mt-2" id="graniteTable">
                    <thead>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Removal Price</th>
                    <th>Delete</th>
                    </thead>
                    <tbody></tbody>
                </table>
              </div>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Granite</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Granite Name:
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
                      Removal Price:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="removal_price" name="removal_price" class="form-control" />
                    </div>
                  </div>
                </div>
                <input type="hidden" name="granite_id" id="granite_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddGranite();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdateGranite();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){

  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif
});

function LoadData()
{
    $('#graniteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_granites') }}",
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
        {}
      ]
    });
}

function AddGranite()
{
    $.ajax({
            url:"{{ route('granites.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'price' : $('#price').val(),
                  'removal_price' : $('#removal_price').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
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

function EditGranite(granite_id)
{
    $.ajax({
            url:"{{ route('get_granite') }}",
            type:'GET',
            data:{
                  'granite_id' : granite_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('#name').val(res.name);
                    $('#price').val(res.price);
                    $('#removal_price').val(res.removal_price);
                    $('#granite_id').val(res.granite_id);
                    $('#btn_update').attr('style', '');
                    $('#btn_add').attr('style', 'display:none;');
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

function UpdateGranite()
{
    $.ajax({
            url:"{{ route('update_granite') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'price' : $('#price').val(),
                  'removal_price' : $('#removal_price').val(),
                  'granite_id' : $('#granite_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
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

function DeleteGranite(granite_id)
{
    $.ajax({
            url:"{{ route('delete_granite') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'granite_id' : granite_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
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
    $('#name').val('');
    $('#price').val('');
    $('#removal_price').val('');
    $('#granite_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
