@extends('layouts.main', [
'title' => $type = ($quote->final) ? "Final Quote" : "Initial Quote" .  ' | ' . @$customer->name. "'s " . $quoteType->name,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Quotes"]
]])

@section('content')

@php
use FK3\vl\quotes\QuoteGeneratorNew;
use FK3\Models\Quote;

$pass = true;
$notifications = FK3\vl\quotes\QuoteGeneratorNew::getNotifications($quote, $pass);

$quote = Quote::find($quote->id);
$quoteGenerator = new QuoteGeneratorNew($quote, true);

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

function GenerateTableRows($rows, $countHeader = 0)
{
    $finalRows = '';
    for($x = 0; $x < count($rows); $x++)
    {
        $color = '';
        $substract = 0;
        if($countHeader > 0 &&  ($countHeader < count($rows[$x])))
        {
            $color = 'style="background-color:' . $rows[$x][$countHeader] . '"';
            $substract = 1;
        }

        $finalRows .= '<tr ' . $color . '>';
        for($z = 0; $z < (count($rows[$x]) - $substract); $z++)
        {
            $finalRows .= '<td>';
            $finalRows .= $rows[$x][$z];
            $finalRows .= '</td>';
        }
        $finalRows .= '</tr>';
    }

    return $finalRows;
}
@endphp

<div class="row">

      <div class="col-lg-6">
          <div class="row">
            <div class="big-icons-buttons clearfix margin-bottom">
                <div class="btn-group">

                    @if(Auth::user()->group_id == 13)
                      <a data-toggle="modal" data-target="#files" href="#" onclick="ShowModalDrawing('{{ $quote->id }}');" class="btn btn-sm btn-info"><i class="fa fa-file-image-o"></i><br>Drawings/Files
                      </a>
                    @endif

                    <a href="{{ route('quote_financing', ['id' => $quote->id]) }}" class="btn btn-sm btn-primary">
                      <i class="fa fa-money"></i><br>Review Financing
                    </a>

                    @if ($pass && $quote->paperwork)
                      <a href="{{ route('quote_contract', ['id' => $quote->id]) }}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i><br>Download PDF
                      </a>
                    @endif

                    @if ($quote->final && !$quote->accepted)
                    @php $meta = unserialize($quote->meta); @endphp
                      @if (isset($meta['meta']['finance']))
                        <a href="/quote/$quote->id/convert" class="btn btn-sm btn-success">
                          <i class="fa fa-check"></i><br>Convert Sold
                        </a>
                      @endif
                    @endif

                    <a href="#" onclick="ShowModalArchiveConfirm('{{ $quote->id }}')" class="btn btn-sm btn-warning" title="Archive Quote"><i class="fa fa-exclamation"></i><br>Decline/Archive
                    </a>

                    <a href="#" onclick="ShowModalDeleteConfirm('{{ $quote->id }}')" class="btn btn-sm btn-danger" title="Delete Quote"><i class="fa fa-trash-o"></i><br>Delete Quote
                    </a>

                    @if (Auth::user()->superuser)
                    <a href="#" class="btn btn-sm btn-info" onclick="ShowModalQuoteDebug();">
                      <i class="fa fa-question"></i><br>Quote Debug
                    </a>

                    <a href="#" class="btn btn-sm btn-primary" onclick="ShowModalQuoteSnapshot('{{ $quote->id }}');">
                      <i class="fa fa-search"></i><br>Snapshots
                    </a>
                    @endif

                    @if($quote->final)
                      @php $prev = Quote::whereLeadId($quote->lead_id)->whereFinal(0)->first(); @endphp
                        @if($prev)
                          <a href="{{ route('quote_view', ['id' => $prev]) }}" class="btn btn-sm btn-success"><i class="fa fa-arrow-left"></i><br>See Initial
                          </a>
                        @endif
                    @else
                          @php $final = Quote::whereLeadId($quote->lead_id)->whereFinal(1)->first(); @endphp
                            @if($final)
                              <a href="{{ route('quote_view', ['id' => $final]) }}" class="btn btn-sm btn-success"><i class="fa fa-arrow-right"></i><br>See Final
                              </a>
                            @endif
                    @endif

                    @if($job)
                      <a href="/job/{{ $job->id }}/schedules" class="btn btn-sm btn-success"><i class="fa fa-code -fork"></i><br>Job Schedule
                      </a>
                    @endif

              </div>
          </div>
        </div>

        <div class="row">
          <div>
            @php echo $notifications; @endphp
            <br/>
            <div class="card-header bg-primary text-white">Quote Details | <small>Quote Type and Cabinet Information</small></div>
            <div class="card groupBody">

              <table class="table table-striped mt-2" id="quoteDetailsTable">
                  <tbody>
                  @php
                    $rows = $quoteGenerator->getQuoteDetails();
                    echo GenerateTableRows($rows);
                  @endphp
                  </tbody>
              </table>

            </div>
            <br/>
            <div class="card-header bg-primary text-white">Cabinet Order(s) <a href="{{ route('quote_cabinets', ['id' => $quote->id]) }}" class="text-white"><small>(click to edit)</small></a></div>
            <div class="card groupBody">

              <table class="table table-striped mt-2" id="quoteCabinetsTable">
                <thead>
                    <th>Item</th>
                    <th>Details</th>
                    <th>Xml</th>
                    <th>Price</th>
                    <th>Total</th>
                </thead>
                <tbody>
                  @php
                    $rows = $quoteGenerator->getCabinets();
                    echo GenerateTableRows($rows);
                  @endphp
                </tbody>
              </table>

            </div>
            <br/>
            <div class="card-header bg-primary text-white">Granite Options | <small>Granite, Island, and Countertops</small></a></div>
            <div class="card groupBody">

              <table class="table table-striped mt-2" id="quoteGranitesTable">
                @php
                  $headers = $quoteGenerator->getGraniteHeader();
                  echo GenerateTableHeaders($headers);
                @endphp
                <tbody>
                  @php
                    $rows = $quoteGenerator->getGranite();
                    echo GenerateTableRows($rows);
                  @endphp
                </tbody>
              </table>

            </div>
            <br/>
            <div class="card-header bg-primary text-white">Tile Configurations | <a class="text-white" href="/quote/{{ $quote->id }}/led"><small>Add Multiple Tiles</small></a></div>
            <div class="card groupBody">

              <table class="table table-striped mt-2" id="quoteTilesTable">
                @php
                  $headers = $quoteGenerator->getTileHeader();
                  echo GenerateTableHeaders($headers);
                @endphp
                <tbody>
                  @php
                    $rows = $quoteGenerator->getTile();
                    echo GenerateTableRows($rows);
                  @endphp
                </tbody>
              </table>

            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
          <div class="row">
              <h1>Grand Total: <small> $<span id="grand_total">0</span></small></h1>
          </div>
          <div class="card-header bg-primary text-white">Sinks, Appliances, Accessories and Hardware</small></div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quoteAppliancesTable">
              @php
                $headers = $quoteGenerator->getAppliancesHeader();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getAppliances();
                  echo GenerateTableRows($rows);
                @endphp
              </tbody>
            </table>

          </div>
          <br/>
          <div class="card-header bg-primary text-white">Additional Items and Requirements</small></div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quoteAdditionalInfoTable">
              @php
                $headers = $quoteGenerator->getAdditionalInfoHeader();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getAdditionalInfo();
                  echo GenerateTableRows($rows, count($headers));
                @endphp
              </tbody>
            </table>

          </div>
          <br/>
          <div class="card-header bg-primary text-white">Questions and Answers <a href="/quote/{{ $quote->id }}/questionaire" class="text-white">(click to edit)</a></div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quoteQuestionsTable">
              @php
                $headers = $quoteGenerator->getQuestionsHeader();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getQuestions();
                  echo GenerateTableRows($rows, count($headers));
                @endphp
              </tbody>
            </table>

          </div>
          <br/>
          <div class="card-header bg-primary text-white">Addons <a href="/quote/{{ $quote->id }}/addons" class="text-white">(click to edit)</a></div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quoteAddonsTable">
              @php
                $headers = $quoteGenerator->getAddonsHeader();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getAddons();
                  echo GenerateTableRows($rows);
                @endphp
              </tbody>
            </table>

          </div>
          <br/>
          <div class="card-header bg-primary text-white">Customer Responsibilities <a href="/quote/{{ $quote->id }}/addons" class="text-white">(click to edit)</a></div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quoteResponsiblitiesTable">
              @php
                $headers = $quoteGenerator->getCustomerResponsibilityHeader();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getCustomerResponsibility();
                  echo GenerateTableRows($rows);
                @endphp
              </tbody>
            </table>

          </div>
          <br/>
          <div class="card-header bg-warning text-white">Custom Payout Modifiers Responsibilities</div>
          <div class="card groupBody">

            <table class="table table-striped mt-2" id="quotePayoutTable">
              @php
                $headers = $quoteGenerator->getPayoutHeaders();
                echo GenerateTableHeaders($headers);
              @endphp
              <tbody>
                @php
                  $rows = $quoteGenerator->getPayouts();
                  echo GenerateTableRows($rows, count($headers));
                @endphp
              </tbody>
            </table>

          </div>

    </div>
</div>

@endsection


@section('javascript')
<script type="text/javascript">
$(function(){
$('#grand_total').html('{{ number_format($quoteGenerator->total, 2) }}');

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif

  @if($request->get('upload_file') == 1)
    ShowModalDrawing({{ $request->quote_id }});
    setMessageModal('success', 'Success', 'File Uploaded.')
  @endif
});

function ShowModalDrawing(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('File/Designs');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table fileTable table-striped mt-2" id="fileTable">\
                                    <thead>\
                                    <th></th>\
                                    <th>Description</th>\
                                    <th>Uploaded By</th>\
                                    <th>Attach to Contractors</th>\
                                    <th>Delete</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              \
                              <form id="form_upload_file" role="form" action="{{ url('quotes') }}/' + quote_id + '/upload_file" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  File Description:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="description" id="description" required>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="file" class="col-md-4 control-label">\
                                  Select File:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" class="form-control" name="file" id="file" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select the files</p>\
                                </div>\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary">Upload</button>\
                              \
                              </form>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    LoadDataFiles(quote_id);
}

function LoadDataFiles(quote_id)
{
    $('#fileTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_quote_files') }}",
                  data: {
                          'quote_id': quote_id
                        }
              },
      "bDeferRender": true,
      "searching": false,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "fnInitComplete": function(oSettings, json) {
        $('#myModal').modal('show');
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

function ShowModalArchiveConfirm(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoArchive(' + quote_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoArchive(quote_id)
{
    $.ajax({
            url:"{{ route('set_quote_archived') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  window.location = '{{ route("quotes.index") }}';
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
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

function ShowModalDeleteConfirm(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + quote_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(quote_id)
{
    $.ajax({
            url:"{{ route('quote_delete') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  window.location = '{{ route("quotes.index") }}';
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
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

function ShowModalQuoteDebug()
{
    removeMessageModal();

    <?php
      $rows = $quoteGenerator->debug;
      $rowsfinal = str_replace("'", "\'", GenerateTableRows($rows));
      $rowsfinal = str_replace('"', '\"', $rowsfinal);
    ?>

    $('.modal-title').html('Quote Debugger');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <table class="table fileTable table-striped mt-2" id="fileTable">\
                                  <thead>\
                                  <th>Item</th>\
                                  <th>Amount</th>\
                                  <th>Total</th>\
                                  </thead>\
                                  <tbody>\
                                   <?php echo $rowsfinal; ?>\
                                  </tbody>\
                              </table>\
                            </div>\
                        ');
    $('.modal-footer').html('<button class="btn btn-default" onclick="CloseModal();">Close</button>');
    $('#myModal').modal('show');
}

function ShowModalEditQuoteType(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Quote Type');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="location" class="col-md-4 control-label">\
                                  Designer:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="quote_type_id" id="quote_type_id">\
                                    <option value="">-- Select Type -- </option>\
                                    @foreach($quoteTypes as $quoteType)\
                                      <option value="{{ $quoteType->id }}">{{ $quoteType->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the type</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetType(\'' + quote_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetType(quote_id);
}

function GetType(quote_id)
{
    $.ajax({
            url:"{{ route('get_quote_type') }}",
            type:'GET',
            data:{
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#quote_type_id').val(res.quote_type_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetType(quote_id)
{
    $.ajax({
            url:"{{ route('set_quote_type') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": quote_id,
                  "quote_type_id": $('#quote_type_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  $('#quote_type').html(res.name);
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

function ShowModalEditQuoteTitle(quote_id)
{
  removeMessageModal();

  $('.modal-title').html('Set Title');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="location" class="col-md-4 control-label">\
                                Title:\
                              </label>\
                              <div class="col-md-8">\
                                  <input type="text" class="form-control" name="quote_title_modal" id="quote_title_modal" />\
                                      <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the title</p>\
                              </div>\
                            </div>\
                        ');

  $('.modal-footer').html('\
                            <a href="#" class="btn btn-primary" onclick="SetTitle(\'' + quote_id + '\')">Save</a>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                          ');

  GetTitle(quote_id);
}

function GetTitle(quote_id)
{
  $.ajax({
          url:"{{ route('get_quote_title') }}",
          type:'GET',
          data:{
                "quote_id": quote_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#quote_title_modal').val(res.title);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetTitle(quote_id)
{
  $.ajax({
          url:"{{ route('set_quote_title') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "quote_id": quote_id,
                "quote_title": $('#quote_title_modal').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                $('#quote_title').html(res.title);
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

function ShowModalCabinetXml(quote_id, cabinet_id)
{
    removeMessageModal();

    $('.modal-title').html('Cabinet Item Lists');
    $('.modal-body').html('\
                              <div class="form-group form-row " id="cabinet_xml_cab_name">\
                              </div>\
                              <div class="form-group form-row " id="cabinet_xml_lists" style="text-align:left;">\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetCabinetXml(quote_id, cabinet_id);
}

function GetCabinetXml(quote_id, cabinet_id)
{
    $.ajax({
            url:"{{ route('get_quote_cabinet_xml') }}",
            type:'GET',
            data:{
                  "quote_id": quote_id,
                  "cabinet_id": cabinet_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#cabinet_xml_cab_name').html(res.cab_name);
                    $('#cabinet_xml_lists').html(res.data);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });

}

function ShowModalQuoteSnapshot(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Snapshots');
    $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <table class="table fileTable table-striped mt-2" id="fileTable">\
                                  <thead>\
                                  <th>Timestamp</th>\
                                  <th>Contract Link</th>\
                                  <th>Debug</th>\
                                  </thead>\
                                  <tbody id="body_quote_snapshot"></tbody>\
                              </table>\
                            </div>\
                        ');
    $('.modal-footer').html('<button class="btn btn-default" onclick="CloseModal();">Close</button>');

    GetQuoteSnapshot(quote_id);
}

function GetQuoteSnapshot(quote_id)
{
    $.ajax({
            url:"{{ route('get_quote_snapshots') }}",
            type:'GET',
            data:{
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#body_quote_snapshot').html(res.cab_name);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });

}

</script>
@endsection
