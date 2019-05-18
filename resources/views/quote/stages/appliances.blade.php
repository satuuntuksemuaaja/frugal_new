@extends('layouts.main', [
'title' => "Appliances | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Appliances"]
]])

@section('content')

@php

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

@endphp
        <div class="row">
            <div class="col-lg-6">
                <div class="card groupBody">

                    <div class="card-header bg-primary text-white"><b>Sink Requirements</b></div>
                    <div class="card-body">
                        <div class="card groupBody">
                          <table class="table table-striped mt-2" id="quoteSinkTable">
                              <thead>
                                <th>Sink</th>
                                <th>Delete</th>
                              </thead>
                              <tbody id="body_sink"></tbody>
                          </table>
                        </div>
                        <br/>
                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <a href="#" class="btn btn-danger" onclick="ShowModalAddSink({{ $quote->id }});"><i class="fa fa-plus"></i> Add Sink</a>
                    </div>

                </div>
            </div>

            <div class="col-lg-6">
                <div class="card groupBody">
                    <div class="card-header bg-info text-white"><b>Appliances</b></div>

                    <form id="form_appliance" role="form" action="{{ route('quote_save_appliance', ['id' => $quote->id]) }}" method="post">
                    {{ csrf_field() }}
                    <div class="card-body">

                      <div class="form-group form-row ">
                        <table class="table table-striped mt-2" id="quoteApplianceTable">
                            <thead>
                              <th></th>
                              <th>Appliance</th>
                              <th>Additional Cost</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                      </div>

                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <input type="hidden" name="appliances" value="Y">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Appliances</button>
                    </div>
                  </form>
                </div>
            </div>

        </div>
    </div>
    <br/>
    <div class="row">
      <div class="col-12 text-center">
        <span class="btn-group">
          <a href="{{ route('quote_granite', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Granites</a>
          <a href="{{ route('quote_accessories', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
          <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
        </span>
      </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
var table;
$(function(){
  LoadDataSink();
  LoadDataAppliance();

  $('#form_appliance').on('submit', function(e){
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

function LoadDataSink()
{
    $.ajax({
            url:"{{ route('quote_get_sink_data', ['id' => $quote->id]) }}",
            type:'GET',
            data:{
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  $('#body_sink').html(res.data);
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

function ShowModalAddSink(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Add Sink to Quote');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="sink" class="col-md-4 control-label">\
                                Sink Type:\
                              </label>\
                              <div class="col-md-8">\
                                  <select class="form-control" name="sink_id" id="sink_id">\
                                  @foreach($sinks as $sink)\
                                    <option value="{{ $sink->id }}">{{ $sink->name }}</option>\
                                  @endforeach\
                                  </select>\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the sink type</p>\
                              </div>\
                            </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveSink(' + quote_id + ')"><i class="fa fa-plus"></i> Add Sink</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveSink(quote_id)
{
  $.ajax({
          url:"{{ route('quote_save_sink', ['id' => $quote->id]) }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "sink_id": $('#sink_id').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadDataSink();
              }
              else if(res.response == "error")
              {
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function LoadDataAppliance()
{
    table = $('#quoteApplianceTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('quote_display_appliances') }}",
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
        {}
      ]
    });
}

</script>
@endsection
