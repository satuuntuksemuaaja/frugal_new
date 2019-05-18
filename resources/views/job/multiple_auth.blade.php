@extends('layouts.main', [
'title' => "Multiple Customer Job Authorizations",
'crumbs' => [
    ['text' => "Multiple Customer Job Authorizations"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <table class="table jobTable table-striped mt-2" id="jobTable">
                      <thead>
                      <th>Client</th>
                      <th></th>
                      </thead>
                      <tbody>
                      @foreach($jobs as $job)
                        {{ $job->quote->lead->customer->name }}
                      @endforeach
                      </tbody>
                  </table>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <a href="#" class="btn btn-primary" onclick=""><i class="fa fa-plus"></i> Add New Item</a>
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
        <div class="col-lg-12">
            <div class="card" id="div_card_auth_not_sign" @php if(count($jobs) == count($auths)) echo 'hidden'; @endphp>
                <div class="card-body bg-danger text-white">
                  <h2><i class="fa fa-times-circle-o"></i> Some or No Job Authorizations have not been signed</h2>
                  This authorization has either not yet been sent to the customer for approval or signed. If you are done with all items that should be signed off on, please click Send to Customer below.
                  <br><br>
                  <a href="#" class="btn btn-success" onclick="SendAuthorizationRequest('{{ $job->id }}');"><i class="fa fa-arrow-right"></i> Send Authorization Request to Customer</a>
                  &nbsp;
                  <a href="#" class="btn btn-primary" onclick="ShowModalAuthSign('{{ $job->id }}');"><i class="fa fa-edit"></i> Have Customer Sign Now</a>
                </div>
            </div>
            <div class="card" id="div_card_auth_sign" @php if(count($jobs) < count($auths)) echo 'hidden'; @endphp>
                <div class="card-body bg-success text-white">
                  <h2><i class="fa fa-check-square-o"></i> Authorizations Signed</h2>
                  All Job Authorizations and items have been signed off on by the customer.
                  <br><br>
                  <a href="#" class="btn btn-danger" onclick="RemoveAuthSign('{{ $job->id }}');"><i class="fa fa-times"></i> Remove Signature</a>
                </div>
                <div class="card-body bg-info text-white">
                  <h2><i class="fa fa-bell"></i> Signature Found</h2>
                </div>
                <br>
                <div class="form-group form-row " style="text-align:center; display: inline-block;">
                  Signed by:
                </div>
                <div class="form-group form-row " style="text-align:center; display: inline-block;">
                  <canvas id="signature_pad" class="signature_pad" width="auto" height="150" style="border:0px solid #000000;"></canvas>
                  <hr/>
                  @foreach($jobs as $job)
                    {{ $job->quote->lead->customer->name }}
                    <br/>
                  @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){
  //LoadData();

  //Initialize signature
  var canvas = document.querySelector("canvas");
  signaturePad = new SignaturePad(canvas, {
                                            penColor: '#145394'
                                        });
  signaturePad.fromData(JSON.parse('<?php echo $auth->signature; ?>'));
  signaturePad.off();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif


});

function LoadData()
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
                LoadData();
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

function LoadDataItemAuthSign()
{
    $('#authSignTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_job_auth_sign_items') }}",
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
        {}
      ]
    });
}

var signaturePad = '';
function ShowModalAuthSign(job_id)
{
    @if(is_null($request->show_sign))
      var table = $('#authTable').DataTable();
      if ( ! table.data().count() )
      {
          setStatusMessage("danger", "Error", 'Please add at least 1 item to signature.');
          return;
      }
    @endif
    removeMessageModal();

    $('.modal-title').html('Customer Job Authorization | {{ $customer->name }}');
    $('.modal-body').html('\
                              <div class="form-group form-row " style="display: inline-block;">\
                              <h4>Frugal Kitchens Job Authorization Request</h4>\
                              I, {{ $customer->name }}, hereby confirm that as of {{ \Carbon\Carbon::now()->format('m/d/y h:i a') }}, I authorize the special items listed below to be handled by Frugal Kitchens.\
                              </div>\
                              <div class="form-group form-row ">\
                              <table class="table authSignTable table-striped mt-2" id="authSignTable">\
                                  <thead>\
                                  <th>Item</th>\
                                  </thead>\
                                  <tbody></tbody>\
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

    LoadDataItemAuthSign();

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
            url:"{{ route('save_job_auth_sign') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_id": job_id,
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

function RemoveAuthSign(job_id)
{
  $.ajax({
          url:"{{ route('remove_job_auth_sign') }}",
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

</script>
@endsection
