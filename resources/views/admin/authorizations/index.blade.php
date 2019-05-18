@extends('layouts.main', [
'title' => "Authorizations",
'crumbs' => [
    ['text' => "Authorizations"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="authorizationTable">
                  <thead>
                  <th>Item</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Item</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Item:
                    </label>
                    <div class="col-md-8">
                      <textarea id="item" name="item" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="authorization_id" id="authorization_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddAuthorization();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdateAuthorization();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
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
    $('#authorizationTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_authorizations') }}",
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
        {}
      ]
    });
}

function AddAuthorization()
{
    $.ajax({
            url:"{{ route('authorizations.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'item' : $('textarea#item').val()
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

function EditAuthorization(authorization_id)
{
    $.ajax({
            url:"{{ route('get_authorization') }}",
            type:'GET',
            data:{
                  'authorization_id' : authorization_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('textarea#item').val(res.item);
                    $('#authorization_id').val(res.authorization_id);
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

function UpdateAuthorization()
{
    $.ajax({
            url:"{{ route('update_authorization') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'item' : $('textarea#item').val(),
                  'authorization_id' : $('#authorization_id').val()
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

function DeleteAuthorization(authorization_id)
{
    $.ajax({
            url:"{{ route('delete_authorization') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'authorization_id' : authorization_id
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
    $('textarea#item').val('')
    $('#authorization_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
