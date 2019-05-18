@extends('layouts.main', [
'title' => "Addons | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Addons"]
]])

@section('content')

@php

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

@endphp
        <div class="row">
            <div class="col-lg-6">
              <div class="card groupBody">
                <table class="table table-striped mt-2" id="quoteAddonsTable">
                    <thead>
                      <th>Item</th>
                      <th>Qty</th>
                      <th>Price</th>
                      <th>Ext. Price</th>
                    </thead>
                    <tbody></tbody>
                </table>
              </div>
              <br/>
                <div class="card groupBody">

                    <div class="card-header bg-primary text-white"><b>Quote Addons</b></div>
                    <div class="card-body">

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Item:
                        </label>
                        <div class="col-md-8">
                          <select name="item_id" id="item_id" class="form-control">
                            <option value="">-- Select Addon --</option>
                            @foreach($addons as $addon)
                              <option value="{{ $addon->id }}">{{ $addon->item }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Quantity:
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="qty" id="qty" class="form-control" min="1" value="1" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Price:
                        </label>
                        <div class="col-md-8 btn-group">
                          <span class="input-group-addon">$</span>
                          <input type="text" name="price" id="price" class="form-control" value="0" />
                          <span class="input-group-addon">Leave price at 0 to use default amount.</span>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Description:
                        </label>
                        <div class="col-md-8">
                          <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                          <p class="help-block mt-1 text-muted" style="font-size: 12px;">This will be shown in the contract</p>
                        </div>
                      </div>

                    </div>
                    <input type="hidden" name="quote_addon_id" id="quote_addon_id" value="" />
                    <div class="card-footer" style="text-align:center;">
                        <a href="#" class="btn btn-success" onclick="UpdateAddon();" id="btn_update" style="display:none;"><i class="fa fa-save"></i> Update Addon</a>
                        <a href="#" class="btn btn-danger" onclick="SaveAddon();"><i class="fa fa-save"></i> Save Addon</a>
                        <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info"><i class="fa fa-share"></i> Quote Overview</a>
                    </div>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="card groupBody">
                    <div class="card-header bg-info text-white"><b>Select Customer Responsibilities</b></div>

                    <form id="form_responsibility" role="form" action="{{ route('quote_save_responsibility', ['id' => $quote->id]) }}" method="post">
                    {{ csrf_field() }}
                    <div class="card-body">

                      <div class="form-group form-row ">
                        <table class="table table-striped mt-2" id="quoteResponsibilityTable">
                            <thead>
                              <th></th>
                              <th>Responsibility</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                      </div>

                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Responsibilities</button>
                    </div>
                  </form>
                </div>
            </div>

        </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
var table;
$(function(){
  LoadDataAddons();
  LoadDataResponsibility();

  $('#form_responsibility').on('submit', function(e){
    var form = this;

     // Encode a set of form elements from all pages as an array of names and values
     var params = table.$('input').serializeArray();
     // Iterate over all form elements
      $.each(params, function(){
         // If element doesn't exist in DOM
         if(!$.contains(document, form[this.name])){
            // Create a hidden element
            $(form).append(
               $('<input>')
                  .attr('type', 'hidden')
                  .attr('name', this.name)
                  .val(this.value)
            );
         }
      });
  });

  $('#item_id').select2();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataAddons()
{
    table = $('#quoteAddonsTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_addons') }}",
                  data: {
                          'quote_id': '{{ $quote->id }}'
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

function SaveAddon()
{
  $.ajax({
          url:"{{ route('quote_save_addons') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "quote_id": "{{ $quote->id }}",
                "item_id": $('#item_id').val(),
                "qty": $('#qty').val(),
                "price": $('#price').val(),
                "description": $('textarea#description').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataAddons();
                ClearAddonForm();
                $('#btn_update').attr('style', 'display:none;');
              }
              else if(res.response == "error")
              {
                setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setStatusMessage("danger", "Error", "Save not commited!");
          }
        });
}

function DeleteAddon(quote_addon_id)
{
  $.ajax({
          url:"{{ route('quote_delete_addons') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "id": quote_addon_id,
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataAddons();
              }
              else if(res.response == "error")
              {
                setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setStatusMessage("danger", "Error", "Save not commited!");
          }
        });
}

function EditAddon(quote_addon_id)
{
  $.ajax({
          url:"{{ route('get_quote_addons') }}",
          type:'GET',
          data:{
                "quote_addon_id": quote_addon_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#quote_addon_id').val(res.quote_addon_id);
                  $('#item_id').val(res.item_id).trigger('change');
                  $('#price').val(res.price);
                  $('#qty').val(res.qty);
                  $('textarea#description').val(res.description);
                  $('#btn_update').attr('style', '');
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

function UpdateAddon()
{
  $.ajax({
          url:"{{ route('quote_update_addons') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "quote_id": "{{ $quote->id }}",
                "quote_addon_id": $('#quote_addon_id').val(),
                "item_id": $('#item_id').val(),
                "qty": $('#qty').val(),
                "price": $('#price').val(),
                "description": $('textarea#description').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataAddons();
                ClearAddonForm();
                $('#btn_update').attr('style', 'display:none;');
              }
              else if(res.response == "error")
              {
                setStatusMessage("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setStatusMessage("danger", "Error", "Save not commited!");
          }
        });
}

function ClearAddonForm()
{
    $('#item_id').val('').trigger('change');
    $('#qty').val('1');
    $('#price').val('0');
    $('textarea#description').val('');
}

function LoadDataResponsibility()
{
    $('#quoteResponsibilityTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_responsibility') }}",
                  data: {
                          'quote_id': '{{ $quote->id }}'
                        }
              },
      "scrollX": true,
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

</script>
@endsection
