@extends('layouts.main', [
'title' => "Customer Job Authorizations | " . $customer->name,
'crumbs' => [
    ['text' => "Customer Job Authorizations"]
]])
@section('content')
@php
  use FK3\Models\Authorization;
@endphp
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <h5>Signature</h5>
                  <a href="#" class="btn btn-primary" onclick="ShowModalAuthSign();"><i class="fa fa-edit"></i> Have Customer Sign Now</a>
                  <a href="#" class="btn btn-danger" style="float:right;" onclick="RemoveSelectedAuthSign();"><i class="fa fa-times"></i> Remove Selected Signature</a>
                  <table class="table jobTable table-striped mt-2" id="jobTable">
                      <thead>
                      <th>Select</th>
                      <th>Type</th>
                      <th>Status</th>
                      </thead>
                      <tbody>
                      @foreach($jobs as $job)
                        @php
                          $auth = Authorization::where('job_id', $job->id)->where('signed_on', '<>', null)->first();
                        @endphp
                      <tr>
                          <td>
                            <input type="checkbox" class="form-control" id="check_{{ $job->id }}" name="check_{{ $job->id }}" value="{{ $job->id }}" onclick="AddRemoveJobSelected(this);" />
                          </td>
                          <td>
                            <a href="#" onclick="ShowJobItems('{{ $job->id }}', '{{ $job->quote->type->name }} - {{ $job->quote->id }}')">{{ $job->quote->type->name }} - {{ $job->quote->id }}</a>
                          </td>
                          <td>
                            @php
                              if($auth) echo 'Signed on ' . \Carbon\Carbon::parse($auth->signed_on)->format('m/d/Y h:i a');
                              else echo '<a href="#" class="btn btn-success" onclick="SendAuthorizationRequest(' . $job->id . ');"><i class="fa fa-arrow-right"></i> Send Authorization Request to Customer</a><br/><small>No signature found</small>';
                            @endphp
                          </td>
                      </tr>
                      @endforeach
                      </tbody>
                  </table>
                </div>
            </div>
        </div>
        <div id="div_job_items" class="col-lg-12" style="display:none;">
            <div class="card">
                <div class="card-body">
                  <h5>Job Items <span id="span_job_item"></span></h5>
                    <a href="#" id="btn_add_new_item" class="btn btn-primary" onclick="ShowModalAddItem('');"><i class="fa fa-plus"></i> Add New Item</a>
                </div>
                <div class="card-body">
                  <table class="table authTable table-striped mt-2" id="authTable">
                      <thead>
                      <th>Item</th>
                      <th>Remove</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                </div>
            </div>
        </div>
        <div class="col-lg-12" id="div_auth" style="display:none;">
          <div class="card" id="div_card_auth_not_sign">
              <div class="card-body bg-danger text-white">
                <h2><i class="fa fa-times-circle-o"></i> Job Authorizations have not been signed</h2>
                This authorization has either not yet been sent to the customer for approval or signed. If you are done with all items that should be signed off on, please click Send to Customer above.
              </div>
          </div>
          <div class="card" id="div_card_auth_sign">
              <div class="card-body bg-info text-white">
                <h2><i class="fa fa-bell"></i> Signature Found</h2>
                A signature was found for the authorizations requested, and was signed by {{ $customer->name }} on <span id="span_sign_on"></span>
              </div>
              <br>
              <div class="form-group form-row " style="text-align:center; display: inline-block;">
                Signed by:
              </div>
              <div class="form-group form-row " style="text-align:center; display: inline-block;">
                <canvas id="signature_pad" class="signature_pad" width="auto" height="150" style="border:0px solid #000000;"></canvas>
                <hr/>
                {{ $customer->name }}
              </div>
          </div>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){

  //Initialize signature
  var canvas = document.querySelector("canvas");
  signaturePad = new SignaturePad(canvas, {
                                            penColor: '#145394'
                                        });

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif


});

function ShowJobItems(job_id, title)
{
    $('#div_job_items').attr('style', '');
    $('#btn_add_new_item').attr('onclick', 'ShowModalAddItem(' + job_id + ');');
    $('#span_job_item').html('| ' + title);
    LoadJobItems(job_id);
    LoadJobAuthStatus(job_id);
}

function LoadJobItems(job_id)
{
  $('#authTable')
  .dataTable({
    "ajax": {
                url: "{{ route('display_job_auth_items') }}",
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

function LoadJobAuthStatus(job_id)
{
    $.ajax({
            url:"{{ route('customer_get_job_auth_status', ['id' => $customer->id]) }}",
            type:'GET',
            data:{
                  "job_id": job_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    if(res.message == 'no sign found')
                    {
                        $('#div_card_auth_not_sign').attr('style', '');
                        $('#div_card_auth_sign').attr('style', 'display:none;');
                        $('#div_auth').attr('style', '');
                    }
                    else
                    {
                        $('#div_card_auth_not_sign').attr('style', 'display:none;');
                        $('#div_card_auth_sign').attr('style', '');
                        $('#div_auth').attr('style', '');

                        $('#span_sign_on').html(res.signed_on);
                        signaturePad.fromData(JSON.parse(res.signature));
                        signaturePad.off();
                    }
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

function ShowModalAddItem(job_id)
{
    removeMessageModal();

    $('.modal-title').html('New Item');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="auth_item" class="col-md-4 control-label">\
                                  Authorization Item:\
                                </label>\
                                <div class="col-md-8">\
                                    <select name="auth_item" id="auth_item" class="form-control">\
                                        <option value="">--Select an Authorization Item--</option>\
                                        @foreach($authLists as $authList)\
                                          <option value="{{ $authList->item }}">{{ $authList->item }}</option>\
                                        @endforeach\
                                    </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="new_auth_item" class="col-md-4 control-label">\
                                  Or Enter Manual Item:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" name="new_auth_item" id="new_auth_item" required></textarea>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveItem(\'' + job_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveItem(job_id)
{
    $.ajax({
            url:"{{ route('save_job_auth_item') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": job_id,
                  "auth_item": $('#auth_item').val(),
                  "new_auth_item": $('textarea#new_auth_item').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadJobItems();
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

function DeleteAuthItem(item_id)
{
  $.ajax({
          url:"{{ route('delete_job_auth_items') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "item_id": item_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadJobItems();
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

function ShowModalAuthSign(job_id)
{
    if(arrJobs.length == 0)
    {
        setStatusMessage("danger", "Error", "Please select at least 1 job to sign!");
        return;
    }
    removeStatusMessage();

    $('.modal-title').html('Customer Job Authorization | {{ $customer->name }}');
    $('.modal-body').html('\
                              <div class="form-group form-row " style="display: inline-block;">\
                              <h4>Frugal Kitchens Job Authorization Request</h4>\
                              I, {{ $customer->name }}, hereby confirm that as of {{ \Carbon\Carbon::now()->format('m/d/y h:i a') }}, I authorize the jobs listed below to be handled by Frugal Kitchens.\
                              </div>\
                              <div class="form-group form-row ">\
                              <table class="table jobSignTable table-striped mt-2" id="jobSignTable">\
                                  <thead>\
                                  <th>Type</th>\
                                  </thead>\
                                  <tbody id="div_job_row"></tbody>\
                              </table>\
                              </div>\
                              \
                              <div class="form-group form-row " style="display: inline-block;">\
                                <canvas id="signature_pad" class="signature_pad" width="auto" height="150" style="border:1px solid #000000;"></canvas>\
                              </div>\
                              <br/>\
                              <div class="form-group form-row " style="display: inline-block;">\
                                <a href="#" id="btn_clear" class="btn btn-warning" onclick="ClearSignature();">Clear</a>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveAuthSign(\'' + job_id + '\')">I approve of these request</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    var canvas = document.querySelector("canvas");
    signaturePad = new SignaturePad(canvas, {
                                              penColor: '#145394'
                                          });

    LoadDataJobAuthSign();

    $('#myModal').modal('show');
}

function ClearSignature()
{
    signaturePad.clear();
}

function SaveAuthSign(job_id)
{
    if (signaturePad.isEmpty())
    {
        setMessageModal('danger', 'Error', 'Please provide a signature first.');
        return;
    }
    var signature = JSON.stringify(signaturePad.toData());
    $.ajax({
            url:"{{ route('save_customer_jobs_auth_sign') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_ids": arrJobs,
                  "signature": signature
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
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function RemoveSelectedAuthSign(job_id)
{
  if(arrJobs.length == 0)
  {
      setStatusMessage("danger", "Error", "Please select at least 1 job to sign!");
      return;
  }
  removeStatusMessage();

  $.ajax({
          url:"{{ route('remove_customer_jobs_auth_sign') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "job_ids": arrJobs
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
                setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function LoadDataJobAuthSign()
{
    $.ajax({
            url:"{{ route('customer_get_job_auth_sign', ['id' => $customer->id]) }}",
            type:'GET',
            data:{
                  "job_ids": arrJobs
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                   $('#div_job_row').html(res.data);
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

function SendAuthorizationRequest(job_id)
{
  $.ajax({
          url:"{{ route('send_job_auth') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "job_id": job_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
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

var arrJobs = [];
function AddRemoveJobSelected(element)
{
    if($("#" + element.id).is(':checked'))
    {
       arrJobs.push($("#" + element.id).val());
    }
    else
    {
       removeItem(arrJobs, $("#" + element.id).val());
    }
}

function removeItem(array, item)
{
    for(var i in array){
        if(array[i]==item){
            array.splice(i,1);
            break;
        }
    }
}

</script>
@endsection
