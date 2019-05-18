@extends('layouts.main', [
'title' => "Accessories",
'crumbs' => [
    ['text' => "Accessories"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-8">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="accessoryTable">
                  <thead>
                  <th>SKU</th>
                  <th>Name</th>
                  <th>Vendor</th>
                  <th>Description</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Accessory</b></div>
                <form id="form_accessory" action="#" method="post" enctype="multipart/form-data">
                  <div class="card-body">
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        SKU:
                      </label>
                      <div class="col-md-8">
                        <input type="text" id="sku" name="sku" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Name:
                      </label>
                      <div class="col-md-8">
                        <input type="text" id="name" name="name" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Description:
                      </label>
                      <div class="col-md-8">
                        <textarea id="description" name="description" class="form-control" rows="4"></textarea>
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
                        Image:
                      </label>
                      <div class="col-md-8">
                        <input type="file" id="image" name="image" class="form-control" />
                        <img src="" id="image_preview" name="image_preview" width="200px;" height="200px" style="display:none;" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Vendor:
                      </label>
                      <div class="col-md-8">
                        <select id="vendor_id" name="vendor_id" class="form-control" />
                            <option value="">--Select Vendor--</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="on_site" name="on_site" data-parsley-multiple="groups" data-parsley-mincheck="2" value="Y">
                          <label class="custom-control-label" for="on_site">Accessory installed on site?</label>
                      </div>
                    </div>
                  </div>
                  <input type="hidden" name="accessory_id" id="accessory_id" value="" />
                  <div class="card-footer" style="text-align:center;">
                      <button href="#" class="btn btn-success" id="btn_add"><i class="fa fa-check"></i> Add New</button>
                      <button href="#" class="btn btn-info" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</button>
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

  $("#btn_add").click(function(event) {
    event.preventDefault();
    var form = $('#form_accessory')[0];
    var data = new FormData(form);

    if($('#vendor_id').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please select vendor.');
        return;
    }

    $.ajax({
            url:"{{ route('accessories.store') }}",
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
    var form = $('#form_accessory')[0];
    var data = new FormData(form);

    if($('#vendor_id').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please select vendor.');
        return;
    }

    $.ajax({
            url:"{{ route('update_accessory') }}",
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
    $('#accessoryTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_accessories') }}",
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
        {},
        {}
      ]
    });
}

function EditAccessory(accessory_id)
{
    $.ajax({
            url:"{{ route('get_accessory') }}",
            type:'GET',
            data:{
                  'accessory_id' : accessory_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html('Edit ' + res.name);
                    $('#sku').val(res.sku);
                    $('#name').val(res.name);
                    $('textarea#description').val(res.description);
                    $('#price').val(res.price);
                    if(res.image)
                    {
                      $('#image_preview').attr('src', '{{ url("") }}/app/' + res.image);
                      $('#image_preview').attr('style', '');
                    }
                    else
                    {
                      $('#image_preview').attr('src', '');
                      $('#image_preview').attr('style', 'display:none;');
                    }
                    $('#vendor_id').val(res.vendor_id);
                    if(res.on_site == '1') $('#on_site').prop('checked', true);
                    else $('#on_site').prop('checked', false);
                    $('#accessory_id').val(res.accessory_id);
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

function DeleteAccessory(accessory_id)
{
    $.ajax({
            url:"{{ route('delete_accessory') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'accessory_id' : accessory_id
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
    $('#title').html('New Accessory');
    $('#sku').val('');
    $('#name').val('');
    $('textarea#description').val('');
    $('#price').val('');
    var $el = $('#image');
    $el.wrap('<form>').closest('form').get(0).reset();
    $el.unwrap();
    $('#image_preview').attr('src', '');
    $('#image_preview').attr('style', 'display:none;');
    $('#vendor_id').prop('selectedIndex', 0);
    $('#on_site').prop('checked', false);
    $('#accessory_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
