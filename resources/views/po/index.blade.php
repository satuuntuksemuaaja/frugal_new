@extends('layouts.main', [
'title' => "Purchase Orders | Manage Purchase Orders",
'crumbs' => [
    ['text' => "Purchase Orders"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalAddPo();">
                        <i class="fa fa-plus"></i> Create Purchase Order
                    </a>
                    <a href="#" class="btn btn-info mb-2" onclick="LoadData(true);">
                        <i class="fa fa-refresh"></i> Show Old Purchase Orders
                    </a>
                    <a href="{{ route('po_export') }}" target="_blank" class="btn btn-success mb-2">
                        <i class="fa fa-download"></i> Export POs
                    </a>
                </div>
                <table class="table poTable table-striped mt-2" id="poTable">
                    <thead>
                    <th>#</th>
                    <th>Title</th>
                    <th>Customer</th>
                    <th>Vendor</th>
                    <th>Type</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th>Ordered On</th>
                    <th>Company Invoice</th>
                    <th>Projected Ship Date</th>
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
  LoadData(false);

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadData(bool)
{
    $('#poTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_po') }}",
                  data: {
                            "old": bool
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
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

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

function ShowModalDeleteConfirm(po_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + po_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(po_id)
{
    $.ajax({
            url:"{{ route('delete_po') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "po_id": po_id
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

function ShowModalArchiveConfirm(po_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoArchive(' + po_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoArchive(po_id)
{
    $.ajax({
            url:"{{ route('set_po_archived') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "po_id": po_id
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

function ShowModalType(po_id)
{
  removeMessageModal();

  $('.modal-title').html('Type of Purchase Order');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="description" class="col-md-4 control-label">\
                                Type:\
                              </label>\
                              <div class="col-md-8">\
                                <input type="text" class="form-control" name="po_type" id="po_type" required>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <button class="btn btn-primary" onclick="SetType(' + po_id + ');">Save</button>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                          ');

  GetPoType(po_id);
}

function GetPoType(po_id)
{
  $.ajax({
          url:"{{ route('get_po_type') }}",
          type:'GET',
          data:{
                "po_id": po_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#po_type').val(res.type);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetType(po_id)
{
  $.ajax({
          url:"{{ route('set_po_type') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "po_id": po_id,
                "type": $('#po_type').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                $('#po_type_' + po_id).html(res.type);
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

function ShowModalCompanyInvoice(po_id)
{
  removeMessageModal();

  $('.modal-title').html('Change Company Invoice');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="description" class="col-md-4 control-label">\
                                Invoice:\
                              </label>\
                              <div class="col-md-8">\
                                <input type="text" class="form-control" name="company_invoice" id="company_invoice" required>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <button class="btn btn-primary" onclick="SetCompanyInvoice(' + po_id + ');">Save</button>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                          ');

  GetCompanyInvoice(po_id);
}

function GetCompanyInvoice(po_id)
{
  $.ajax({
          url:"{{ route('get_po_company_invoice') }}",
          type:'GET',
          data:{
                "po_id": po_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#company_invoice').val(res.company_invoice);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetCompanyInvoice(po_id)
{
  $.ajax({
          url:"{{ route('set_po_company_invoice') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "po_id": po_id,
                "company_invoice": $('#company_invoice').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                $('#po_company_invoice_' + po_id).html(res.company_invoice);
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

function ShowModalProjectedShip(po_id)
{
  removeMessageModal();

  $('.modal-title').html('Change Projected Ship');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <label for="description" class="col-md-4 control-label">\
                                Projected Ship:\
                              </label>\
                              <div class="col-md-8">\
                                <input type="text" class="form-control" name="projected_ship" id="projected_ship" required>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <button class="btn btn-primary" onclick="SetProjectedShip(' + po_id + ');">Save</button>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                          ');

  GetProjectedShip(po_id);
}

function GetProjectedShip(po_id)
{
  $.ajax({
          url:"{{ route('get_po_projected_ship') }}",
          type:'GET',
          data:{
                "po_id": po_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#projected_ship').val(res.projected_ship);
              }
              $('#myModal').modal('show');
          },
          error: function(a, b, c)
          {

          }
        });
}

function SetProjectedShip(po_id)
{
  $.ajax({
          url:"{{ route('set_po_projected_ship') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "po_id": po_id,
                "projected_ship": $('#projected_ship').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                $('#po_projected_ship_' + po_id).html(res.projected_ship);
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

function ShowModalAddPo()
{
  removeMessageModal();

  $('.modal-title').html('New Purchase Order');
  $('.modal-body').html('\
                            <div class="form-group form-row ">\
                              <b>You are creating a new purchase order. The purchase order will remain in the draft stage until it is considered submitted to the vendor for purchase.</b>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="description" class="col-md-4 control-label">\
                                PO Description:\
                              </label>\
                              <div class="col-md-8">\
                                <input type="text" class="form-control" name="title" id="title" required>\
                              </div>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="customer_id" class="col-md-4 control-label">\
                                Select Customer:\
                              </label>\
                              <div class="col-md-8">\
                                <select class="form-control" name="customer_id" id="customer_id" required>\
                                  <option value="0">-- Internal Purchase Order -- </option>\
                                  @foreach($customers as $customer)\
                                    <option value="{{ $customer->id }}">({{ $customer->id }}) {{ $customer->name }}</option>\
                                  @endforeach\
                                </select>\
                              </div>\
                            </div>\
                            \
                            <div class="form-group form-row ">\
                              <label for="vendor_id" class="col-md-4 control-label">\
                                Select Vendor:\
                              </label>\
                              <div class="col-md-8">\
                                <select class="form-control" name="vendor_id" id="vendor_id" required>\
                                  <option value="">-- Select Vendor -- </option>\
                                  @foreach($vendors as $vendor)\
                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>\
                                  @endforeach\
                                </select>\
                              </div>\
                            </div>\
                            \
                        ');

  $('.modal-footer').html('\
                            <button class="btn btn-primary" onclick="SavePo();">Save</button>\
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                          ');

  $('#myModal').modal('show');
}

function SavePo()
{
  $.ajax({
          url:"{{ route('save_po') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "title": $('#title').val(),
                "customer_id": $('#customer_id').val(),
                "vendor_id": $('#vendor_id').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setMessageModal("success", "Success", res.message);
                setStatusMessage("success", "Success", res.message);
                CloseModal();
                LoadData();
                window.location = res.redirect_url;
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

function ConfirmPo(po_id)
{
  $.ajax({
          url:"/po/" + po_id + "/confirm",
          type:'GET',
          data:{

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                $('#span_status_pri_' + res.po_id).html('confirmed');
                $('#span_status_sec_' + res.po_id).remove();
              }
              else if(res.response == "error")
              {
                //setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              //setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function OrderPo(po_id)
{
  $.ajax({
          url:"/po/" + po_id + "/order",
          type:'GET',
          data:{

          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                $('#span_status_pri_' + res.po_id).html('ordered');
                $('#a_status_' + res.po_id).html('confirm');
                $('#a_status_' + res.po_id).attr('onclick', 'ConfirmPo("' + res.po_id + '");');
              }
              else if(res.response == "error")
              {
                //setMessageModal("danger", "Error", res.message);
              }
          },
          error: function(a, b, c)
          {
              //setMessageModal("danger", "Error", "Save not commited!");
          }
        });
}

function DoDeleteFile(quote_id, file_id)
{
    $.ajax({
            url:"{{ route('quote_delete_file') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "file_id": file_id,
                  "quote_id": quote_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  LoadDataFiles(quote_id);
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
