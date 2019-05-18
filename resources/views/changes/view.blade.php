@extends('layouts.main', [
'title' => "Change Order | " . $customer->name,
'crumbs' => [
    ['text' => "Change Order"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <a href="#" class="btn btn-primary" onclick="ShowModalAddItem('{{ $order->id }}');"><i class="fa fa-plus"></i> Add New Item</a>
                </div>
                <div class="card-body">
                  <table class="table authTable table-striped mt-2" id="authTable">
                      <thead>
                      <th>Item</th>
                      <th>Price</th>
                      <th>Added By</th>
                      <th>Added On</th>
                      <th>Orderable ?</th>
                      <th>Ordered On</th>
                      <th>Ordered By</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                  &nbsp;&nbsp;&nbsp;<b>Total: </b>$ <span id="total_price"></span>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card" id="div_card_auth_not_sign" @php if($order->signature != '') echo 'hidden'; @endphp>
                <div class="card-body bg-danger text-white">
                  <h2><i class="fa fa-times-circle-o"></i> Change order has not been signed</h2>
                  This change order has either not yet been sent to the customer for approval or signed. If you are done with this change order, please click Send to Customer below.
                  <br><br>
                  <a href="{{ route('changes_send', ['id' => $order->id]) }}" class="btn btn-success"><i class="fa fa-arrow-right"></i> Send Change Order to Customer</a>
                  &nbsp;
                  <a href="#" class="btn btn-primary" onclick="ShowModalAuthSign('{{ $order->id }}');"><i class="fa fa-edit"></i> Have Customer Sign Now</a>
                </div>
            </div>
            <div class="card" id="div_card_auth_sign" @php if($order->signature == '') echo 'hidden'; @endphp>
                <div class="card-body bg-success text-white">
                  <h2><i class="fa fa-check-square-o"></i> Authorizations Signed</h2>
                  All items have been signed off on by the customer.
                  <br><br>
                  <a href="#" class="btn btn-danger" onclick="RemoveAuthSign('{{ $order->id }}');"><i class="fa fa-times"></i> Remove Signature</a>
                </div>
                <div class="card-body bg-info text-white">
                  <h2><i class="fa fa-bell"></i> Signature Found</h2>
                  A signature was found for this change order, and was signed by {{ $customer->name }} on {{ \Carbon\Carbon::parse($order->sign_on)->format('m/d/Y h:i a') }}. If additional items need to be added, please create a new change order request.
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
  LoadData();

  @if(!is_null($request->show_sign))
    ShowModalAuthSign('{{ $order->id }}');
  @endif

  //Initialize signature
  var canvas = document.querySelector("canvas");
  signaturePad = new SignaturePad(canvas, {
                                            penColor: '#145394'
                                        });
  signaturePad.fromData(JSON.parse('<?php echo $order->signature; ?>'));
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
                  url: "{{ route('display_changes_items') }}",
                  data: {
                            'order_id': '{{ $order->id }}'
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
        $('#total_price').html(json['totalPrice']);
      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

function ShowModalAddItem(order_id)
{
    removeMessageModal();

    $('.modal-title').html('New Item');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  Description:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" name="description" id="description" required></textarea>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="price" class="col-md-4 control-label">\
                                  Price: (enter in dollars and cents):\
                                </label>\
                                <div class="col-md-8">\
                                  <div class="input-group">\
                                    <span class="input-group-addon">$</span>\
                                    <input type="text" name="price" placeholder="" class="form-control " id="price">\
                                  </div>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="orderable" class="col-md-4 control-label">\
                                  Needs to be ordered?:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="orderable" id="orderable">\
                                      <option value="0">No</option>\
                                      <option value="1">Yes</option>\
                                    </select>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveItem(\'' + order_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#myModal').modal('show');
}

function SaveItem(order_id)
{
    $.ajax({
            url:"{{ route('save_detail_item') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "order_id": order_id,
                  "description": $('textarea#description').val(),
                  "price": $('#price').val(),
                  "orderable": $('#orderable').val()
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
                  url: "{{ route('display_order_auth_sign_items') }}",
                  data: {
                            'order_id': '{{ $order->id }}'
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
        $('#total_price_auth').html(json['totalPrice']);
      },
      "aoColumns" : [
        {},
        {}
      ]
    });
}

var signaturePad = '';
function ShowModalAuthSign(order_id)
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

    $('.modal-title').html('Customer Signature | Confirm Change Order Request');
    $('.modal-body').html('\
                              <div class="form-group form-row " style="display: inline-block;">\
                              <h4>Change Order #{{ $order->id }} for {{ $customer->name }}</h4>\
                              I, {{ $customer->name }}, hereby confirm that as of {{ \Carbon\Carbon::now()->format('m/d/y h:i a') }}the items listed below have been agreed upon to be added/modified by Frugal Kitchens and Cabinets for my job. By signing this agreement, I understand that I am agreeing to pay for, in full, the items listed below.<br>\
                              <b>Note</b>: 50% of the change order total(s) is due before work begins.\
                              </div>\
                              <div class="form-group form-row ">\
                              <table class="table authSignTable table-striped mt-2" id="authSignTable">\
                                  <thead>\
                                  <th>Item</th>\
                                  <th>Price</th>\
                                  </thead>\
                                  <tbody></tbody>\
                              </table>\
                              </div>\
                              <div class="form-group form-row " style="display: inline-block;">\
                                <h3>Change Order Total: $ <span id="total_price_auth"></span></h3>\
                              </div>\
                              <div class="form-group form-row " style="margin:auto; text-align:center; display:block;">\
                                <a href="/change/' + order_id + '/decline" class="btn btn-danger"><i class="fa fa-times"></i> Decline Change Order</a>\
                              </div>\
                              \
                              <br>\
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
                              <a href="#" class="btn btn-primary" onclick="SaveAuthSign(\'' + order_id + '\')">I accept the terms of this agreement.</a>\
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

function SaveAuthSign(order_id)
{
    if (signaturePad.isEmpty())
    {
        setMessageModal('danger', 'Error', 'Please provide a signature first.');
        return;
    }
    var signature = JSON.stringify(signaturePad.toData());
    var signature_img = signaturePad.toDataURL();
    $.ajax({
            url:"{{ route('save_order_auth_sign') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "order_id": order_id,
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
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function RemoveAuthSign(order_id)
{
  $.ajax({
          url:"{{ route('remove_order_auth_sign') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "order_id": order_id
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

function ShowModalDeleteConfirm(order_detail_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + order_detail_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(order_detail_id)
{
    $.ajax({
            url:"{{ route('delete_changes_items') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "order_detail_id": order_detail_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
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

</script>
@endsection
