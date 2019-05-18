@extends('layouts.main', [
'title' => "Purchase Order #" . $po->number . " | " . @$customer->name . " for job " . $po->job_id,
'crumbs' => [
    ['text' => "Purchase Order #" . $po->number]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalAddPoItem();">
                        <i class="fa fa-plus"></i> Add Item
                    </a>
                    @if($po->parent_id == 0)
                        <a href="{{ route('po_child', ['id' => $po->id]) }}" class="btn btn-info mb-2">
                            <i class="fa fa-plus"></i> Spawn Sub-PO
                        </a>
                    @endif
                </div>
                <table class="table poItemTable table-striped mt-2" id="poItemTable">
                    <thead>
                    <th>Quantity</th>
                    <th>Item</th>
                    <th>Status</th>
                    <th>Remove</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script type="text/javascript">

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadData()
{
    $('#poItemTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_po_item') }}",
                  data: {
                            'po_id' : '{{ $po->id }}'
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

function ShowModalAddPoItem()
{
  removeMessageModal();

  $('.modal-title').html('New Purchase Order Item');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <b>You are adding an item to PO: {{ $po->number }}. Please ensure this is from the same vendor so as not to cause confusion.</b>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="qty" class="col-md-4 control-label">\
                                Quantity:\
                              </label>\
                              <div class="col-md-8">\
                                <input type="number" class="form-control" name="qty" id="qty" required>\
                              </div>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="item" class="col-md-4 control-label">\
                                Item:\
                              </label>\
                              <div class="col-md-8">\
                                <textarea class="form-control" name="item" id="item"></textarea>\
                              </div>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="punch_item_id" class="col-md-4 control-label">\
                                From Punch:\
                              </label>\
                              <div class="col-md-8">\
                                <select class="form-control" name="punch_item_id" id="punch_item_id" required>\
                                  <option value="0">No Punch Item</option>\
                                  <?php echo $punchData; ?>\
                                </select>\
                              </div>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="vendor_id" class="col-md-4 control-label">\
                                <?php echo $title; ?>\
                              </label>\
                              <div class="col-md-8">\
                                <?php echo $selectOpts; ?>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <button class="btn btn-primary" onclick="SavePoItem();">Save</button>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                          ');

  $('#myModal').modal('show');
}

function SavePoItem()
{
    var data = {
                "_token":"{{ csrf_token() }}",
                "qty": $('#qty').val(),
                "item": $('#item').val(),
                "punch_item_id": $('#punch_item_id').val(),
                "po_id": '{{ $po->id }}'
            };

    if($("#fft_id").length)
    {
        data.fft_id = $("#fft_id").val();
    }
    else if($("#warranty_id").length)
    {
        data.warranty_id = $("#warranty_id").val();
    }
    else if($("#service_id").length)
    {
        data.service_id = $("#service_id").val();
    }

    $.ajax({
            url:"{{ route('po_item_new') }}",
            type:'POST',
            data: data,
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



function ShowModalDeleteItemConfirm(po_item_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDeleteItem(' + po_item_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDeleteItem(po_item_id)
{
  $.ajax({
          url:"{{ route('po_item_delete') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "po_item_id": po_item_id
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
