@extends('layouts.main', [
'title' => 'Cabinets | ' . $customer->name . "'s " . @$quoteType->name,
'crumbs' => [
    ['text' => "Cabinet", 'url' => "/cabinet"],
    ['text' =>  "Cabinet"]
]])

@section('content')

<div class="row">

  <div class="col-lg-12">

      <div class="card-body bg-info text-white">
        <h3><i class="fa fa-bell"></i></h3>
        Need to redo a cabinet or XML? Simply click the trash can and delete the appropriate cabinet and click Add Cabinet to redo the cabinet entry.
      </div>

      <div class="card">
        <div class="card-body">
            <a href="#" class="btn btn-primary" onclick="ShowModalAddCabinet('{{ $quote->id }}');"><i class="fa fa-plus"></i> Add Cabinet</a>
            <span id="span_next_step">@php echo $next; @endphp</span>
            <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info"><i class="fa fa-share"></i> Quote Overview</a>
        </div>
        <table class="table table-striped mt-2" id="quoteCabinetsTable">
            <thead>
                <th>Description</th>
                <th>Cabinets</th>
                <th>List Price</th>
                <th>Color</th>
                <th>In. Off Floor</th>
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
  LoadDataQuoteDetails();
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadDataQuoteDetails()
{
  $('#quoteCabinetsTable')
  .dataTable({
    "ajax": {
                url: "{{ route('display_quote_cabinets') }}",
                data: {
                        "quote_id": "{{ $quote->id }}"
                      }
            },
    "bDeferRender": true,
    "searching": false,
    "destroy": true,
    "processing": false,
    "serverSide": false,
    "paging":   false,
    "ordering": false,
    "info":     false,
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
      {}
    ]
  });
}

function ShowModalAddCabinet(quote_id)
{
    removeMessageModal();

    $('.modal-title').html('Add Cabinet to Quote');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <b>You are adding a cabinet to this quote. There are no longer primary and secondary cabinets. This system now supports unlimited cabinet orders.</b>\
                              </div>\
                              \
                              <form id="form_upload_file" role="form" action="{{ url('quotes') }}/' + quote_id + '/upload_file_cabinet" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="xml" class="col-md-4 control-label">\
                                  Pro Kitchens XML:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" class="form-control" name="xml" id="xml">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Cabinet Name:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="name" id="name">\
                                  <p class="help-block mt-1 text-muted" style="font-size: 12px;">Only if there is no XML file.</p>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="xml" class="col-md-4 control-label">\
                                  Cabinet List:\
                                </label>\
                                <div class="col-md-8">\
                                  <textarea class="form-control" name="list" id="list" rows="4"></textarea>\
                                  <p class="help-block mt-1 text-muted" style="font-size: 12px;">Only if there is no XML file.</p>\
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

    $('#myModal').modal('show');
}

function ShowModalCabinetEdit(quote_cabinet_id)
{
    removeMessageModal();

    $('.modal-title').html('Edit Cabinet');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="cabinet_id" class="col-md-4 control-label">\
                                  Select Cabinet:\
                                </label>\
                                <div class="col-md-8">\
                                  <select class="form-control" name="cabinet_id" id="cabinet_id">\
                                    <option value="0">-- Select Cabinet --</option>\
                                    @foreach($cabinets as $cabinet)\
                                      <option value="{{ $cabinet->id }}">{{ $cabinet->frugal_name }}</option>\
                                    @endforeach\
                                  </select>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="price" class="col-md-4 control-label">\
                                  List Price:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="price" id="price">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="inches" class="col-md-4 control-label">\
                                  In. off Floor:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="inches" id="inches" value="0">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  Description:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="description" id="description">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row " id="div_color">\
                                <label for="color" class="col-md-4 control-label">\
                                  Color:\
                                </label>\
                                <div class="col-md-8">\
                                  <select name="color" id="color" class="form-control">\
                                  </select>\
                                </div>\
                              </div>\
                              <div class="form-group form-row ">\
                                <label for="are_we_removing_cabinets" class="col-md-4 control-label">\
                                  Customer Removing Cabinets?\
                                </label>\
                                <div class="col-md-8">\
                                  <select class="form-control" name="are_we_removing_cabinets" id="are_we_removing_cabinets">\
                                    <option value="0">No</option>\
                                    <option value="1">Yes</option>\
                                  </select>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveQuoteCabinet(' + quote_cabinet_id + ');">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    GetQuoteCabinetData(quote_cabinet_id);
}

function GetQuoteCabinetData(quote_cabinet_id)
{
  $.ajax({
          url:"{{ route('get_quote_cabinet_data') }}",
          type:'GET',
          data:{
                "quote_cabinet_id": quote_cabinet_id
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#cabinet_id').val(res.cabinet_id);
                  $('#price').val(res.price);
                  $('#color').html(res.color);
                  if(res.color == '') $('#div_color').hide();
                  $('#inches').val(res.inches);
                  $('#description').val(res.description);
                  $('#are_we_removing_cabinets').val(res.are_we_removing_cabinets);

                  $('#myModal').modal('show');
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

function SaveQuoteCabinet(quote_cabinet_id)
{
  $.ajax({
          url:"{{ route('save_quote_cabinet_data') }}",
          type:'POST',
          data:{
                "_token":"{{ csrf_token() }}",
                "quote_cabinet_id": quote_cabinet_id,
                "cabinet_id": $('#cabinet_id').val(),
                "price": $('#price').val(),
                "color": $('#color').val(),
                "inches": $('#inches').val(),
                "description": $('#description').val(),
                "are_we_removing_cabinets": $('#are_we_removing_cabinets').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                setStatusMessage("success", "Success", res.message);
                LoadDataQuoteDetails();
                CheckNextStep();
                CloseModal();
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

function CheckNextStep()
{
  $.ajax({
          url:"{{ route('check_next_step') }}",
          type:'GET',
          data:{
                "quote_id": '{{ $quote->id }}'
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#span_next_step').html(res.data);
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

function ShowModalCabinetDeleteConfirm(quote_cabinet_id)
{
    removeMessageModal();

    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure?</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + quote_cabinet_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(quote_cabinet_id)
{
    $.ajax({
            url:"{{ route('delete_quote_cabinet_data') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_cabinet_id": quote_cabinet_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadDataQuoteDetails();
                  CheckNextStep();
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
