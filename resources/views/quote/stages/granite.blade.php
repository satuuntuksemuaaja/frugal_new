@extends('layouts.main', [
'title' => "Granite | " . $quote->lead->customer->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Granite"]
]])

@section('content')

@php
use FK3\Models\Quote;
use FK3\Models\QuoteGranite;
use FK3\Models\Granite;

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

function showForm(QuoteGranite $g = null, Quote $quote)
{
    // Granite Type
    if (!$g) $g = new QuoteGranite;
    $granites = Granite::orderBy('name', 'ASC')->get();
    $opts = [];
    foreach ($granites AS $granite)
    {
        $opts[] = ['val' => $granite->id, 'text' => $granite->name];
    }

    if ($g->granite_id && $g->granite)
    {
        array_unshift($opts, ['val' => $g->granite_id, 'text' => $g->granite->name]);
    }
    else
    {
        array_unshift($opts, ['val' => 0, 'text' => '-- Select Granite -- ']);
    }
    $fields = '';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Location/Description:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="description" id="description" class="form-control" value="' . $g->description . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Granite Type:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<select name="granite_id" id="granite_id" class="form-control" />';

    foreach($opts as $opt)
    {
        $fields .= '<option value="' . $opt['val'] . '">' . $opt['text'] . '</option>';
    }

    $fields .= '</select>';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Customer Picking Slab?';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<select name="picking_slab" id="picking_slab" class="form-control" />';

    $opts = [];
    // Is customer picking slab?
    if ($quote->picking_slab)
        $opts[] = ['val' => $quote->picking_slab, 'text' => $quote->picking_slab];
    else
        $opts[] = ['val' => '', 'text' => '-- Select Option --'];
    $opts[] = ['val' => 'Yes', 'text' => 'Yes'];
    $opts[] = ['val' => 'No', 'text' => 'No'];
    $opts[] = ['val' => 'Undecided', 'text' => 'Undecided'];

    foreach($opts as $opt)
    {
        $fields .= '<option value="' . $opt['val'] . '">' . $opt['text'] . '</option>';
    }

    $fields .= '</select>';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Special Granite?<br/><small><span class="text-danger">WARNING: This will override granite dropdown</span></small>';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="granite_override" id="granite_override" class="form-control" value="' . $g->granite_override . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    // Special granite
    if($quote->job)
    {
        $fields .= '<div class="form-group form-row ">';
        $fields .= '<label for="terms" class="col-md-4 control-label">';
        $fields .= '** Granite Override **<br/><small><span class="text-danger">WARNING: This will override the schedule only</span></small>';
        $fields .= '</label>';
        $fields .= '<div class="col-md-8">';
        $fields .= '<input type="text" name="granite_jo" id="granite_jo" class="form-control" value="' . $g->granite_jo . '" />';
        $fields .= '<p class="help-block mt-1 text-muted" style="font-size: 12px;">ONLY CHANGE JOB SCHEDULE</p>';
        $fields .= '</div>';
        $fields .= '</div>';
    }

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Special Granite Price p/sqft<br/><small><span class="text-danger">WARNING: This will override granite dropdown</span></small>';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="pp_sqft" id="pp_sqft" class="form-control" value="' . $g->pp_sqft . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    // CounterTop Removal Type
    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Countertop Removal Type:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<select name="removal_type" id="removal_type" class="form-control" />';
    $opts = [];
    if ($g->removal_type)
    {
        $opts[] = ['val' => $g->removal_type, 'text' => $g->removal_type];
    }
    $opts[] = ['val' => '', 'text' => 'No Pre-existing Countertops'];
    $opts[] = ['val' => 'Formica', 'text' => 'Formica'];
    $opts[] = ['val' => 'Corian', 'text' => 'Corian'];
    $opts[] = ['val' => 'Granite', 'text' => 'Granite'];
    $opts[] = ['val' => 'Tile', 'text' => 'Tile'];

    foreach($opts as $opt)
    {
        $fields .= '<option value="' . $opt['val'] . '">' . $opt['text'] . '</option>';
    }
    $fields .= '</select>';
    $fields .= '</div>';
    $fields .= '</div>';

    // Countertop Measurements
    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Counter Measurements: <br/><small><b>one number per line</b></small>';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<textarea name="measurements" id="measurements" class="form-control">' . $g->measurements . '</textarea>';
    $fields .= '<p class="help-block mt-1 text-muted" style="font-size: 12px;">At 25.5 inches</p>';
    $fields .= '</div>';
    $fields .= '</div>';

    // Countertop Edge
    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Counter Edge:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<select name="counter_edge" id="counter_edge" class="form-control" />';
    $edges = [];
    if ($g->counter_edge)
    {
        $edges[] = ['val' => $g->counter_edge, 'text' => $g->counter_edge];
    }

    $edges[] = ['val' => '(Standard) Pencil Round - 1/4 Round', 'text' => '(Standard) Pencil Round - 1/4 Round'];
    $edges[] = ['val' => '(Standard) 1/4 Bevel', 'text' => '(Standard) 1/4 Bevel'];
    $edges[] = ['val' => '(Standard) Eased', 'text' => '(Standard) Eased'];
    $edges[] = ['val' => '(Premium) Half Bull Nose ($8/lnft.)', 'text' => '(Premium) Half Bull Nose ($8/lnft.)'];
    $edges[] = ['val' => '(Premium) Half Bevel ($8/lnft.)', 'text' => '(Premium) Half Bevel ($8/lnft.)'];
    $edges[] = ['val' => '(Premium) Full Bull Nose ($12/lnft.)', 'text' => '(Premium) Full Bull Nose ($12/lnft.)'];
    $edges[] = ['val' => '(Premium) 2cm Ogee ($14/lnft.)', 'text' => '(Premium) 2cm Ogee ($14/lnft.)'];
    $edges[] = ['val' => '(Premium) French Ogee ($20/lnft.)', 'text' => '(Premium) French Ogee ($20/lnft.)'];
    $edges[] = ['val' => '(Premium) Dupont ($24/lnft.)', 'text' => '(Premium) Dupont ($24/lnft.)'];
    $edges[] = ['val' => '(Premium) Demi Bullnose ($5/lnft.)', 'text' => '(Premium) Demi Bullnose ($5/lnft.)'];

    foreach($edges as $edge)
    {
        $fields .= '<option value="' . $edge['val'] . '">' . $edge['text'] . '</option>';
    }
    $fields .= '</select>';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Countertop Edge in Linear Ft. <br/><b>(if premium)</b><br/><small>Leave blank if a standard edge is used.</small>';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="counter_edge_ft" id="counter_edge_ft" class="form-control" value="' . $g->counter_edge_ft . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    // Backsplash primaryrmation
    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Backsplash Height in Inches:<br/><small>Leave 0 if no backsplash</small>';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="backsplash_height" id="backsplash_height" class="form-control" value="' . $g->backsplash_height . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Raised Bar Countertop Length:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="raised_bar_length" id="raised_bar_length" class="form-control" value="' . $g->raised_bar_length . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Raised Bar Countertop Depth:';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="raised_bar_depth" id="raised_bar_depth" class="form-control" value="' . $g->raised_bar_depth . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Island Granite (width):';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="island_width" id="island_width" class="form-control" value="' . $g->island_width . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<div class="form-group form-row ">';
    $fields .= '<label for="terms" class="col-md-4 control-label">';
    $fields .= 'Island Granite (length):';
    $fields .= '</label>';
    $fields .= '<div class="col-md-8">';
    $fields .= '<input type="text" name="island_length" id="island_length" class="form-control" value="' . $g->island_length . '" />';
    $fields .= '</div>';
    $fields .= '</div>';

    $fields .= '<input type="hidden" id="g_id" name="g_id" value="' . $g->id . '">';

    return $fields;
}

@endphp

        @if($request->has('granite_id') || $request->has('new'))
        <div class="row">
            <div class="col-lg-12">
                <form id="form_led" role="form" action="{{ route('quote_save_granite', ['id' => $quote->id]) }}?update=yes" method="post">
                {{ csrf_field() }}
                <div class="card groupBody">
                    <div class="card-header bg-primary text-white"><b>Granite Information</b></div>
                    <div class="card-body">
                          @php
                            if($request->has('granite_id'))
                            {
                                // Show these granite options
                                echo showForm(QuoteGranite::find($request->granite_id), $quote);
                            }
                            if($request->has('new'))
                            {
                                // Show empty form.
                                echo showForm(null, $quote);
                            }
                        @endphp
                        <br/>
                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <button name="updateGranite" id="updateGranite" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Update Granite Requirements</button>
                    </div>

                </div>
              </form>
            </div>
        </div>
        <br/>
        @endif

        <div class="row">
            <div class="col-lg-12">
                <div class="card groupBody">
                    <div class="card-header bg-primary text-white"><b>Granite</b></div>
                    <div class="card-body">
                        <div class="card groupBody">
                          <table class="table table-striped mt-2" id="graniteTable">
                              <thead>
                                <th>Location/Description</th>
                                <th>Granite</th>
                                <th>Removal</th>
                                <th>Measurements</th>
                                <th>BS</th>
                                <th>IS W/L</th>
                                <th>RB L/D</th>
                              </thead>
                              <tbody id="body_granite"></tbody>
                          </table>
                        </div>
                        <br/>
                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <a href="{{ route('quote_granite', ['id' => $quote->id]) }}?new=true" class="btn btn-primary"><i class="fa fa-plus"></i> Add New Granite</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <br/>
    <div class="row">
      <div class="col-12 text-center">
        @php
          $pass = false;
          if ($quote->granites()->count() > 0)
          {
              $pass = true;
          }
          if (!$quote->picking_slab)
          {
              echo "<div class='alert alert-danger'>You must select if customer is picking slab or not to continue.</div>";
              $pass = false;
          }
          if ($quote->type->name == 'Cabinet Small Job')
          $pass = true;
        @endphp
        <span class="btn-group">

          @if ($quote->type->name != 'Granite Only')
          <a href="{{ route('quote_cabinets', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Cabinets</a>
          @endif

          @if($pass)
          <a href="{{ route('quote_appliances', ['id' => $quote->id]) }}?moving=true" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
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
  LoadDataGranite();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataGranite()
{
  $.ajax({
          url:"{{ route('quote_display_granite') }}",
          type:'GET',
          data:{
                  'quote_id': '{{ $quote->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                $('#body_granite').html(res.data);
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
