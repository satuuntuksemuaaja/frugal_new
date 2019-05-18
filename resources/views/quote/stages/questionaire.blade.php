@extends('layouts.main', [
'title' => "Quoestionaire | " . $quote->lead->customer->name . " | " . (($quote->final) ? "Final" : "Initial") . " Quote",
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Questionaire"]
]])

@section('content')

@php

use FK3\Models\QuestionCategory;
use FK3\Models\QuoteQuestionAnswer;

function hasThisVendor($quote, $id)
{
	foreach ($quote->cabinets AS $cabinet)
	{
		if ($cabinet->cabinet->vendor_id == $id)
		{
			return true;
		}

	}
	return false;
}

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

function LoadQuestion($quote)
{
  foreach (QuestionCategory::all() AS $category)
  {
    $rows[] = ["<b>$category->name</b>", null];
    foreach ($category->questions AS $question)
    {
        if (!$question->active) continue;
      if ($quote->type == 'Cabinet Small Job' &&  ! $question->small_job)
      {
        continue;
      }

      if ($quote->final && $question->stage == 'I')
      {
        continue;
      }

      if ( ! $quote->final && $question->stage == 'F')
      {
        continue;
      }

      if ($question->vendor_id > 0 &&  ! hasThisVendor($quote, $question->vendor_id))
      {
        continue;
      }
      // don't ask. literally.
      $answer = QuoteQuestionAnswer::whereQuoteId($quote->id)->whereQuestionId($question->id)->first();
      $answer = ($answer) ? $answer->answer : null;
      if ( ! $answer && $question->response_type != 'yes/no')
      {
        $pass = false;
      }

      if ($question->response_type == 'yes/no')
      {
        $selected = ($answer == 'Y') ? "<option value='Y'>Yes</option>" : "<option value='N'>No</option>";
        if (!$answer)
            $selected = "<option value=''>--</option>";
        $rows[] = [$question->question, "
          <select name='question_$question->id' class='form-control'>
          {$selected}
          <option value='Y'>Yes</option>
          <option value='N'>No</option>",
        ];
      }
      else
      {
        $rows[] = [$question->question, "<input type='text' name='question_$question->id' class='form-control' value='$answer'>"];
      }

    } // fe question
  } // fe cat

  return $rows;
}

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

$pass = true;

@endphp
<div class="row">
    <div class="col-lg-12">
      <div class="card groupBody">
        <div class="card-header bg-info text-white"><b>Questionaire</b></div>
        <form id='questionaireForm' method='post' action='{{ route("save_quote_questionaire", ["id" => $quote->id]) }}'>
          {{ csrf_field() }}
        <div class="card-body">
          <table class="table table-striped mt-2" id="quoteQuestionaireTable">
              @php
                echo GenerateTableHeaders(['Question', 'Answer']);
              @endphp
              <tbody>
                @php
                  echo GenerateTableRows(LoadQuestion($quote));
                @endphp
              </tbody>
          </table>
        </div>
        <div class="card-footer" style="text-align:center;">
            <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Questionaire</button>
        </div>
      </div>
      <br/>
    </div>
  </div>
  <br/>
  @php
  $pass = true;
  if ( ! isset($meta['progress_questionaire']))
  {
  	$pass = false;
  }
  @endphp
  <div class="row">
    <div class="col-12 text-center">
      <span class="btn-group">
        <a href="{{ route('quote_additional', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Additional Requirements</a>
        @if ($pass && $quote->type != 'Cabinet Small Job')
        <a href="{{ route('quote_led', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
        @endif
        <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
      </span>
    </div>
  </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){
  LoadDataAddons();
  LoadDataResponsibility();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataAddons()
{
    $('#quoteAddonsTable')
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
                  $('#item_id').val(res.item_id);
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
    $('#item_id').val('');
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
