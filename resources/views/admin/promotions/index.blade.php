@extends('layouts.main', [
'title' => "Promotion",
'crumbs' => [
    ['text' => "Promotion"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-8">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="promotionTable">
                  <thead>
                  <th>Promotion</th>
                  <th>Type</th>
                  <th>Verbiage</th>
                  <th>Active</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New Promotion</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Promotion Name:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="name" name="name" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Active:
                    </label>
                    <div class="col-md-8">
                      <select id="active" name="active" class="form-control">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Modifier:
                    </label>
                    <div class="col-md-8">
                      <select id="modifier" name="modifier" class="form-control">
                        <option value="GRANITE_SQFT">Granite Price per sqft.</option>
                        <option value="TOTAL_PRICE">Total Kitchen Price</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Condition:
                    </label>
                    <div class="col-md-8">
                      <select id="condition" name="condition" class="form-control">
                        <option value=">">></option>
                        <option value="<"><</option>
                        <option value="=">=</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Qualifier:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="qualifier" name="qualifier" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Discount Amount:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="discount_amount" name="discount_amount" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Contract Verbiage:
                    </label>
                    <div class="col-md-8">
                      <textarea id="verbiage" name="verbiage" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    Example: If you wanted to discount the price per square foot if the price is over 32, then you would set the modifier to Granite Price per sqft, condition >, qualifier 32, discount amount 32.
                  </div>
                </div>
                <input type="hidden" name="promotion_id" id="promotion_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddPromotion();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdatePromotion();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
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
    $('#promotionTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_promotions') }}",
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
        {}
      ]
    });
}

function AddPromotion()
{
    $.ajax({
            url:"{{ route('promotions.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'qualifier' : $('#qualifier').val(),
                  'discount_amount' : $('#discount_amount').val(),
                  'verbiage' : $('textarea#verbiage').val(),
                  'active' : $('#active').val(),
                  'modifier' : $('#modifier').val(),
                  'condition' : $('#condition').val()
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

function EditPromotion(promotion_id)
{
    $.ajax({
            url:"{{ route('get_promotion') }}",
            type:'GET',
            data:{
                  'promotion_id' : promotion_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('#name').val(res.name);
                    $('#qualifier').val(res.qualifier);
                    $('#discount_amount').val(res.discount_amount);
                    $('textarea#verbiage').val(res.verbiage);
                    $('#active').val(res.active);
                    $('#modifier').val(res.modifier);
                    $('#condition').val(res.condition);
                    $('#promotion_id').val(res.promotion_id);
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

function UpdatePromotion()
{
    $.ajax({
            url:"{{ route('update_promotion') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'name' : $('#name').val(),
                  'qualifier' : $('#qualifier').val(),
                  'discount_amount' : $('#discount_amount').val(),
                  'verbiage' : $('textarea#verbiage').val(),
                  'active' : $('#active').val(),
                  'modifier' : $('#modifier').val(),
                  'condition' : $('#condition').val(),
                  'promotion_id' : $('#promotion_id').val()
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

function DeletePromotion(promotion_id)
{
    $.ajax({
            url:"{{ route('delete_promotion') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'promotion_id' : promotion_id
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
    $('#qualifier').val('');
    $('#discount_amount').val('');
    $('textarea#verbiage').val('');
    $('#active').prop('selectedIndex', 0);
    $('#modifier').prop('selectedIndex', 0);
    $('#condition').prop('selectedIndex', 0);
    $('#promotion_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
