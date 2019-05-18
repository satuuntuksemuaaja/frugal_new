@extends('layouts.main', [
'title' => "Responsibility",
'crumbs' => [
    ['text' => "Responsibility"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="responsibilityTable">
                  <thead>
                  <th>Name</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Responsibility</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Responsibility:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="name" name="name" class="form-control" />
                    </div>
                  </div>
                </div>
                <input type="hidden" name="responsibility_id" id="responsibility_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddResponsibility();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdateResponsibility();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
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
    $('#responsibilityTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_responsibilities') }}",
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

function AddResponsibility()
{
    $.ajax({
            url:"{{ route('responsibilities.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val()
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

function EditResponsibility(responsibility_id)
{
    $.ajax({
            url:"{{ route('get_responsibility') }}",
            type:'GET',
            data:{
                  'responsibility_id' : responsibility_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('#name').val(res.name);
                    $('#responsibility_id').val(res.responsibility_id);
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

function UpdateResponsibility()
{
    $.ajax({
            url:"{{ route('update_responsibility') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'responsibility_id' : $('#responsibility_id').val()
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

function DeleteResponsibility(responsibility_id)
{
    $.ajax({
            url:"{{ route('delete_responsibility') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'responsibility_id' : responsibility_id
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
    $('#responsibility_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
