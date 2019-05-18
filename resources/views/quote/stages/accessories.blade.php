@extends('layouts.main', [
'title' => "Accessories | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Accessories"]
]])

@section('content')

@php

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

@endphp
        <div class="row">
            <div class="col-lg-6">
                <div class="card groupBody">

                    <form id='accessoriesForm' method='post' action="{{ route('quote_save_accessories', ['id' => $quote->id]) }}">
                      {{ csrf_field() }}
                    <div class="card-header bg-primary text-white"><b>Accessories</b></div>
                    <div class="card-body">
                        <div class="card groupBody">
                          <table class="table table-striped mt-2" id="accesoriesTable">
                              <thead>
                                <th>Qty</th>
                                <th>SKU</th>
                                <th>Description</th>
                                <th>Price</th>
                              </thead>
                              <tbody></tbody>
                          </table>
                        </div>
                        <br/>
                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Accessories</button>
                    </div>
                  </form>

                </div>
            </div>

            @if (isset($meta['quote_accessories']) && $meta['quote_accessories'])
            <div class="col-lg-6">
                <div class="card groupBody">

                    <div class="card-header bg-primary text-white"><b>Accesories In Quote</b></div>
                    <div class="card-body">
                        <div class="card groupBody">
                          <table class="table table-striped mt-2" id="accesoriesInQuoteTable">
                              <thead>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Delete</th>
                              </thead>
                              <tbody id="body_in_quote"></tbody>
                          </table>
                        </div>
                        <br/>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <br/>
    <div class="row">
      <div class="col-12 text-center">
        <span class="btn-group">
          <?php
            $pass = true;
            if (!isset($meta['progress_accessories'])) $pass = false;
          ?>
          <a href="{{ route('quote_appliances', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Appliances</a>

          @if($pass)
          <a href="{{ route('quote_hardware', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
          @endif

          <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
        </span>
      </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
var table;
$(function(){
  LoadDataAccessories();
  LoadDataAccessoriesInQuote();

  $('#accessoriesForm').on('submit', function(e){
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
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataAccessories()
{
    table = $('#accesoriesTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('quote_display_accessories') }}",
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
        { "width": "10%" },
        {},
        {},
        {}
      ]
    });
}

function LoadDataAccessoriesInQuote()
{
  $.ajax({
          url:"{{ route('quote_display_accessories_in_quote') }}",
          type:'GET',
          data:{
                  'quote_id': '{{ $quote->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                $('#body_in_quote').html(res.data);
              }
              else if(res.response == "error")
              {
              }
          },
          error: function(a, b, c)
          {
          }
        });
}

</script>
@endsection
