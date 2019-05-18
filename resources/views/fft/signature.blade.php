@extends('layouts.main', [
'title' => "Customer Signature | Confirm Punch Items for Contract",
'crumbs' => [
    ['text' => "Customer Signature"]
]])
@section('content')

@php
  $type = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "Frugal Final Inspection");
@endphp
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body text-center">
                  <h1>{{ $type }}....For Your Peace of Mind</h1>
                  <p><h5>I, {{ $customer->name }}, hereby confirm that as of {{ \Carbon\Carbon::now()->format('m/d/y h:i a') }} the items listed below have been agreed upon to be addressed by Frugal Kitchens and Cabinets. These items could take up to 4-6 weeks to arrive at our warehouse. Frugal Kitchens will call you as soon as your items have been received to complete your project. Work will be completed once all parts have been received. By signing this agreement, I understand if additional items are requested after signing that additional costs may be incurred at the customer's expense.</h5></p>

                  <p><h5>The following items have been listed to be addressed and all items noted were found in a 'best effort' estimation by Frugal Kitchens and Cabinets in an effort to complete this job in an exemplary manner.</h5></p>
                </div>
            </div>
        </div>
        <div class="col-lg-12">

            <div class="card" id="div_card_signature_found" @php if($fft->signature == '') echo 'hidden'; @endphp>
              <div class="card-body bg-info text-white">
                <h2><i class="fa fa-bell"></i> Signature Found</h2>
                A signature was found for this {{ $type }} item and was signed by {{ $customer->name }} on {{ \Carbon\Carbon::parse($fft->signoff_stamp)->format('Y-m-d h:i:s a') }}. If additional items were found and are to be completed under the initial agreement, the customer can sign again and the items to this date will be added to the contract. You can download the <a href="{{ route('fft_signature_pdf', ['id' => $fft->id]) }}" terget="_blank">pdf here</a>
              </div>
              <div class="card-body text-center">
                <div class="form-group form-row " style="text-align:center; display: inline-block;">
                  Signed by:
                </div>
                <br>
                <div class="form-group form-row " style="text-align:center; display: inline-block;">
                  <canvas id="signature_pad" class="signature_pad" width="400" height="300" style="border:0px solid #000000;"></canvas>
                  <hr/>
                  {{ $customer->name }}
                </div>
            </div>
          </div>

        </div>

        <div class="col-lg-12">
          <div class="card">
            <div class="card-body">
                <div class="card-body text-center">
                  <table class="table jobItemTable table-striped mt-2" id="jobItemTable">
                      <thead>
                      <th>Item</th>
                      <th>Found On</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                  <br>
                  Draw signature below
                  <br>
                  <div class="form-group form-row " style="display: inline-block;">
                    <canvas id="signature_pad_new" class="signature_pad_new" width="400" height="300" style="border:1px solid #000000;"></canvas>
                  </div>
                  <br/>
                  <div class="form-group form-row " style="display: inline-block;">
                    <span id="btn_clear" class="btn btn-warning" onclick="ClearSignature();">Clear</span>
                  </div>
                </div>
                <div class="card-body text-center">
                  <a href="#" class="btn btn-primary" onclick="SaveFftSign('{{ $fft->id }}')">I accept the terms of this agreements</a>
                </div>
            </div>
          </div>
        </div>
  </div>
@endsection

@section('javascript')
<script type="text/javascript">
var signaturePadNew = '';
$(function(){
  LoadData();

  //Initialize signature
  @if($fft->signature != '')
  var canvas = document.querySelector("#signature_pad");
  signaturePad = new SignaturePad(canvas, {
                                            penColor: '#145394'
                                        });
  signaturePad.fromData(JSON.parse('<?php echo $fft->signature; ?>'));
  signaturePad.off();
  @endif

  var canvas_new = document.querySelector("#signature_pad_new");
  signaturePadNew = new SignaturePad(canvas_new, {
                                            penColor: '#145394'
                                        });

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadData()
{
    $('#jobItemTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_fft_job_items') }}",
                  data: {
                            'job_id': '{{ $job->id }}'
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
      "aoColumns" : [
        {},
        {}
      ]
    });
}

function ClearSignature()
{
    signaturePadNew.clear();
}

function SaveFftSign(fft_id)
{
    if (signaturePadNew.isEmpty())
    {
        setStatusMessage('danger', 'Error', 'Please provide a signature first.');
        return;
    }
    var signature = JSON.stringify(signaturePadNew.toData());
    var signature_img = signaturePadNew.toDataURL();
    $.ajax({
            url:"{{ route('save_fft_signature') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "fft_id": fft_id,
                  "signature": signature,
                  "signature_img": signature_img
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  location.reload();
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

</script>
@endsection