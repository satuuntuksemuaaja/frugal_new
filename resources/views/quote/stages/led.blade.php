@extends('layouts.main', [
'title' => "LED and Tile | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "LED and Tile"]
]])

@section('content')

@php

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

@endphp
        <div class="row">
            <div class="col-lg-6">
                <div class="card groupBody">

                    <form id="form_led" role="form" action="{{ route('quote_save_led', ['id' => $quote->id]) }}" method="post">
                    {{ csrf_field() }}
                    <div class="card-header bg-primary text-white"><b>LED Requirements</b></div>
                    <div class="card-body">

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many 12" LED Strip Lights are needed?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_12" id="quote_led_12" class="form-control" value="{{ (isset($meta['quote_led_12'])) ? $meta['quote_led_12'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many 60" LED Strip Lights are needed?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_60" id="quote_led_60" class="form-control" value="{{ (isset($meta['quote_led_60'])) ? $meta['quote_led_60'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many transformers are needed (if cabinets are not connected together
                            and there is no attic above or unfinished basement below there is 1 transformer per separate location).
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_transformers" id="quote_led_transformers" class="form-control" value="{{ (isset($meta['quote_led_transformers'])) ? $meta['quote_led_transformers'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many connections are needed for LED strip lights?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_connections" id="quote_led_connections" class="form-control" value="{{ (isset($meta['quote_led_connections'])) ? $meta['quote_led_connections'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many couplers are needed?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_couplers" id="quote_led_couplers" class="form-control" value="{{ (isset($meta['quote_led_couplers'])) ? $meta['quote_led_couplers'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many switches need to be added?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_switches" id="quote_led_switches" class="form-control" value="{{ (isset($meta['quote_led_switches'])) ? $meta['quote_led_switches'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many feet of LED strip light is being installed?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_led_feet" id="quote_led_switches" class="form-control" value="{{ (isset($meta['quote_led_feet'])) ? $meta['quote_led_feet'] : null }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How many Puck Lights?
                        </label>
                        <div class="col-md-8">
                          <input type="number" name="quote_puck_lights" id="quote_puck_lights" class="form-control" value="{{ (isset($meta['quote_puck_lights'])) ? $meta['quote_puck_lights'] : null }}" />
                        </div>
                      </div>

                    </div>
                    <input type="hidden" name="led" id="led" value="Y" />
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save LED Information</button>
                    </div>
                  </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card groupBody">
                  <div class="card-body">
                    <table class="table table-striped mt-2" id="quoteTileTable">
                        <thead>
                          <th>Description</th>
                          <th>Counter</th>
                          <th>BS</th>
                          <th>Pattern</th>
                          <th>Sealed</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                  </div>
                </div>
                <br/>

                @php
                use FK3\Models\QuoteTile;
                if ($request->has('tile'))
                    $tile = QuoteTile::find($request->get('tile'));
                else $tile = new QuoteTile;
                @endphp

                <div class="card groupBody">
                    <div class="card-header bg-info text-white"><b>Tile Requirements</b></div>
                    <div class="card-body">

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Description
                        </label>
                        <div class="col-md-8">
                          <input type="text" name="description" id="description" class="form-control" value="{{ $tile->description }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Linear Feet of Counter?
                        </label>
                        <div class="col-md-8">
                          <input type="text" name="linear_feet_counter" id="linear_feet_counter" class="form-control" value="{{ $tile->linear_feet_counter }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          How tall is backsplash?
                        </label>
                        <div class="col-md-8">
                          <input type="text" name="backsplash_height" id="backsplash_height" class="form-control" value="{{ $tile->backsplash_height }}" />
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Straight or Pattern?
                        </label>
                        <div class="col-md-8">
                          <select name="pattern" id="pattern" class="form-control">
                            <option value="Straight" @php if($tile->pattern == 'Straight') echo 'selected'; @endphp>Straight</option>
                            <option value="Pattern" @php if($tile->pattern == 'Pattern') echo 'selected'; @endphp>Pattern</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Will tile be sealed?
                        </label>
                        <div class="col-md-8">
                          <select name="sealed" id="sealed" class="form-control">
                            <option value="Yes" @php if($tile->sealed == 'Yes') echo 'selected'; @endphp>Yes</option>
                            <option value="No" @php if($tile->sealed == 'No') echo 'selected'; @endphp>No</option>
                          </select>
                        </div>
                      </div>

                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <a href="#" class="btn btn-danger" onclick="SaveTile();"><i class="fa fa-save"></i> Save Tile Information</a>
                        <a href="#" class="btn btn-info" onclick="ClearTileForm();">Clear Tile Form</a>
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
          <a href="{{ route('quote_questionaire', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Questions</a>
          <a href="{{ route('quote_addons', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Add Addons</a>
          <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
        </span>
      </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){
  LoadDataTiles();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataTiles()
{
    $('#quoteTileTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_tiles') }}",
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
        {},
        {}
      ]
    });
}

function SaveTile()
{
  $.ajax({
          url:"{{ route('save_quote_tiles') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "quote_id": "{{ $quote->id }}",
                "description": $('#description').val(),
                "linear_feet_counter": $('#linear_feet_counter').val(),
                "backsplash_height": $('#backsplash_height').val(),
                "pattern": $('#pattern').val(),
                "sealed": $('#sealed').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataTiles();
                ClearTileForm();
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

function DeleteTile(tile_id)
{
  $.ajax({
          url:"{{ route('delete_quote_tiles') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "id": tile_id,
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataTiles();
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

function ClearTileForm()
{
    $('#description').val('');
    $('#linear_feet_counter').val('');
    $('#backsplash_height').val('');
    $('#pattern').val('Straight');
    $('#sealed').val('Yes');
}

</script>
@endsection
