@extends('layouts.main', [
'title' => "Hardware | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Hardware"]
]])

@section('content')

@php

use FK3\Models\Hardware;

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

function GenerateTableHeaders($headers)
{
    $finalHeaders = '<thead>';
    for($x = 0; $x < count($headers); $x++)
    {
        $finalHeaders .= '<th>';
        $finalHeaders .= $headers[$x];
        $finalHeaders .= '</th>';
    }
    $finalHeaders .= '</thead>';

    return $finalHeaders;
}

function GenerateTableRows($rows)
{
    $finalRows = '';
    for($x = 0; $x < count($rows); $x++)
    {
        $finalRows .= '<tr>';
        for($z = 0; $z < count($rows[$x]); $z++)
        {
            $finalRows .= '<td>';
            $finalRows .= $rows[$x][$z];
            $finalRows .= '</td>';
        }
        $finalRows .= '</tr>';
    }

    return $finalRows;
}

function LoadQuotePull($meta, $quote)
{
    $rows = [];
    foreach ($meta['quote_pulls'] AS $pull => $qty)
    {
        $hardware = Hardware::find($pull);
        $rows[] = [$hardware->sku, $qty, (isset($meta['quote_pulls_location']))? $meta['quote_pulls_location'][$pull]: null, "<a href='/quote/$quote->id/hardware/pull/$pull/delete'>x</a>"];
    }

  return $rows;
}

function LoadPull($meta, $quote)
{
    $pullStore = (isset($meta['quote_pulls'])) ? $meta['quote_pulls'] : [];
    $pullLocation = (isset($meta['quote_pulls_location'])) ? $meta['quote_pulls_location'] : [];
    $rows = [];

    $pulls = Hardware::where('description', 'like', '%pull%')->orderBy('sku', 'ASC')->get();
    foreach ($pulls AS $pull)
    {
        $value = (isset($pullStore[$pull->id])) ? $pullStore[$pull->id] : 0;
        $location = (isset($pullLocation[$pull->id])) ? $pullLocation[$pull->id] : '';
        $rows[] = ["<input type='input' name='pull_$pull->id' value='$value'><br/>Location: <input type='input' name='location_$pull->id' value='" . $location . "'>",
            $pull->sku,
            $pull->description
        ];
    }
    return $rows;
}

function LoadQuoteKnob($meta, $quote)
{
    $rows = [];
    foreach ($meta['quote_knobs'] AS $knob => $qty)
    {
        $hardware = Hardware::find($knob);
        $rows[] = [$hardware->sku, $qty, (isset($meta['quote_knobs_location']))? $meta['quote_knobs_location'][$knob]: null, "<a href='/quote/$quote->id/hardware/knob/$knob/delete'>x</a>"];
    }
    return $rows;
}

function LoadKnob($meta, $quote)
{
    $knobStore = (isset($meta['quote_knobs'])) ? $meta['quote_knobs'] : [];
    $knobLocation = (isset($meta['quote_knobs_location'])) ? $meta['quote_knobs_location'] : [];
    $rows = [];

    $knobs = Hardware::where('description', 'like', '%knob%')->orderBy('sku', 'ASC')->get();
    foreach ($knobs AS $knob)
    {
        $value = (isset($knobStore[$knob->id])) ? $knobStore[$knob->id] : 0;
        $location = (isset($knobLocation[$knob->id])) ? $knobLocation[$knob->id] : '';
        $rows[] = ["<input type='input' name='knob_$knob->id' value='$value'><br/>Location: <input type='input' name='location_$knob->id' value='" . $location . "'>",
            $knob->sku,
            $knob->description
        ];
    }
    return $rows;
}

@endphp
        <div class="row">
            <div class="col-lg-6">
                <div class="card groupBody">
                  <div class="card-header bg-primary text-white">To add a location to your knobs or pulls add a colon after the quantity. For instance: 1:Some Place</div>
                </div>
                <br/>
                <div class="card groupBody">

                  <form id="form_pulls" role="form" action="{{ route('quote_hardware_save', ['id' => $quote->id]) }}" method="post">
                  {{ csrf_field() }}
                    <div class="card-header bg-primary text-white"><b>Select Pulls</b></div>
                    <div class="card-body">
                          @if (isset($meta['quote_pulls']) && $meta['quote_pulls'])
                          <h4>Pulls In Quote</h4>
                          <table class="table table-striped mt-2" id="pullInQuoteTable">
                              <thead>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Location</th>
                                <th>Delete</th>
                              </thead>
                              <tbody>
                                @php
                                  echo GenerateTableRows(LoadQuotePull($meta, $quote));
                                @endphp
                              </tbody>
                          </table>
                          @endif
                      <table class="table table-striped mt-2" id="pullTable">
                          <thead>
                            <th>Qty</th>
                            <th>SKU</th>
                            <th>Description</th>
                          </thead>
                          <tbody>
                            @php
                              echo GenerateTableRows(LoadPull($meta, $quote));
                            @endphp
                          </tbody>
                      </table>
                    </div>
                    <input type='hidden' name='pulls' value='Y'>
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Pulls</button>
                    </div>
                  </form>
                </div>
            </div>

            <div class="col-lg-6">
              <div class="card groupBody">

                <form id="form_knobs" role="form" action="{{ route('quote_hardware_save', ['id' => $quote->id]) }}" method="post">
                {{ csrf_field() }}
                  <div class="card-header bg-primary text-white"><b>Select Knobs</b></div>
                  <div class="card-body">
                        @if (isset($meta['quote_knobs']) && $meta['quote_knobs'])
                        <h4>Knobs In Quote</h4>
                        <table class="table table-striped mt-2" id="knobInQuoteTable">
                            <thead>
                              <th>Item</th>
                              <th>Qty</th>
                              <th>Location</th>
                              <th>Delete</th>
                            </thead>
                            <tbody>
                              @php
                                echo GenerateTableRows(LoadQuoteKnob($meta, $quote));
                              @endphp
                            </tbody>
                        </table>
                        @endif
                    <table class="table table-striped mt-2" id="knobTable">
                        <thead>
                          <th>Qty</th>
                          <th>SKU</th>
                          <th>Description</th>
                        </thead>
                        <tbody>
                          @php
                            echo GenerateTableRows(LoadKnob($meta, $quote));
                          @endphp
                        </tbody>
                    </table>
                  </div>
                  <input type='hidden' name='knobs' value='Y'>
                  <div class="card-footer" style="text-align:center;">
                      <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Knobs</button>
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
          @php
          $pass = true;
          if (!isset($meta['progress_knobs']) || !isset($meta['progress_pulls'])) $pass = false;
          @endphp

          <a href="{{ route('quote_accessories', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Accessories</a>

          @if($pass)
          <a href="{{ route('quote_additional', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
          @endif
          <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
        </span>
      </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){
  $('#pullTable').dataTable();
  $('#knobTable').dataTable();
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif

  //check if location is filled if qty filled
  $("#form_pulls").submit(function(e){
    $("input[name^=pull_]").each(function(){
      var name = $(this).attr('name');
      var number = name.replace("pull_", "");
      var value = $(this).val();
      if(value > 0)
      {
          if($("input[name^=location_" + number + "]").val() == '')
          {
              setStatusMessage('danger', 'Error', 'Please fill all location fields on pulls that has quantity > 0.');
              e.preventDefault();
              window.scrollTo(0, 0);
              return;
          }
      }
    });
  });

  $("#form_knobs").submit(function(e){
    $("input[name^=knob_]").each(function(){
      var name = $(this).attr('name');
      var number = name.replace("knob_", "");
      var value = $(this).val();
      if(value > 0)
      {
          if($("input[name^=location_" + number + "]").val() == '')
          {
              setStatusMessage('danger', 'Error', 'Please fill all location fields on knobs that has quantity > 0.');
              e.preventDefault();
              window.scrollTo(0, 0);
              return;
          }
      }
    });
  });

});

</script>
@endsection
