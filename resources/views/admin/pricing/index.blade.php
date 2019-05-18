@extends('layouts.main', [
'title' => "Pricing",
'crumbs' => [
    ['text' => "Pricing"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-8">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="pricingTable">
                  <thead>
                  <th>Extra Line</th>
                  <th>Price</th>
                  <th>Goes To</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Pricing</b></div>
                <form id="form_accessory" action="#" method="post" enctype="multipart/form-data">
                  <div class="card-body">
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Extra Name:
                      </label>
                      <div class="col-md-8">
                        <input type="text" id="name" name="name" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Price:
                      </label>
                      <div class="col-md-8">
                        <input type="text" id="price" name="price" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Money Goes To:
                      </label>
                      <div class="col-md-8">
                        <select id="group_id" name="group_id" class="form-control" />
                            <option value="0">--Select Group--</option>
                            @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="extra_id" id="extra_id" value="" />
                  <div class="card-footer" style="text-align:center;">
                      <a href="#" class="btn btn-success" id="btn_add" onclick="AddPricing()"><i class="fa fa-check"></i> Add New</a>
                      <a href="#" class="btn btn-info" id="btn_update" onclick="UpdatePricing()" style="display:none;"><i class="fa fa-check"></i> Update</a>
                  </div>
                </form>
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
    $('#pricingTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_pricing') }}",
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

function AddPricing()
{
    $.ajax({
            url:"{{ route('pricing.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'price' : $('#price').val(),
                  'group_id' : $('#group_id').val()
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

function EditPricing(extra_id)
{
    $.ajax({
            url:"{{ route('get_pricing') }}",
            type:'GET',
            data:{
                  'extra_id' : extra_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html('Edit ' + res.name);
                    $('#name').val(res.name);
                    $('#price').val(res.price);
                    $('#group_id').val(res.group_id);
                    $('#extra_id').val(res.extra_id);
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

function UpdatePricing()
{
    $.ajax({
            url:"{{ route('update_pricing') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'price' : $('#price').val(),
                  'group_id' : $('#group_id').val(),
                  'extra_id' : $('#extra_id').val()
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

function DeletePricing(extra_id)
{
    $.ajax({
            url:"{{ route('delete_pricing') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'extra_id' : extra_id
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
    $('#title').html('New Pricing');
    $('#name').val('');
    $('#price').val('');
    $('#group_id').prop('selectedIndex', 0);
    $('#extra_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
