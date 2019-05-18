@extends('layouts.main', [
'title' => "Customer Manager",
'crumbs' => [
    ['text' => "Customer Manager"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="/customers/create" class="btn btn-primary mb-2">
                        <i class="fa fa-plus"></i> Create Customer
                    </a>
                </div>
                <table class="table customerTable mt-2" id="customerTable">
                    <thead>
                    <th>Name</th>
                    <th>Address</th>
                    <th>E-mail #1</th>
                    <th>E-mail #2</th>
                    <th>E-mail #3</th>
                    <th>Home</th>
                    <th>Mobile</th>
                    <th>Alternate</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadData()
{
    $('#customerTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_customer') }}",
                  data: {

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
        {}
      ]
    });
}

function ShowModalNotes(customer_id, customer_name)
{
    removeMessageModal();

    $('.modal-title').html('Job Notes | ' + customer_name);
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <table class="table leadTable table-striped mt-2" id="noteTable">\
                                    <thead>\
                                    <th>Quote</th>\
                                    <th>Time</th>\
                                    <th>Notes</th>\
                                    <th>User</th>\
                                    </thead>\
                                    <tbody></tbody>\
                                </table>\
                              </div>\
                              <hr>\
                              <div class="form-group form-row ">\
                                <label for="quote_id" class="col-md-4 control-label">\
                                  Quote:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="quote_id" id="quote_id" required></select>\
                                    <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select the quotes</p>\
                                </div>\
                              </div>\
                              <div class="form-group form-row ">\
                                <label for="notes" class="col-md-4 control-label">\
                                  Notes:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" name="notes" id="notes" required></textarea>\
                                    <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the notes</p>\
                                </div>\
                              </div>\
                              \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SaveNotes();">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    LoadDataNotes(customer_id);
    LoadDataQuotes(customer_id);
}

function LoadDataNotes(customer_id)
{
    $('#noteTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_customer_job_notes') }}",
                  data: {
                          'customer_id': customer_id
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
        {}
      ]
    });
}

function LoadDataQuotes(customer_id)
{
    $.ajax({
            url:"{{ route('customer_get_customer_quotes') }}",
            type:'GET',
            data:{
                  "customer_id": customer_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  $('#quote_id').html(res.data);
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

function SaveNotes(customer_id)
{
    $.ajax({
            url:"{{ route('save_customer_job_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "quote_id": $('#quote_id').val(),
                  "notes": $('textarea#notes').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  $('textarea#notes').val('');
                  LoadDataNotes(res.customer_id);
                  LoadDataQuotes(res.customer_id);
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
