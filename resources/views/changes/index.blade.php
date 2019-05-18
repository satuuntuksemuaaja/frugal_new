@extends('layouts.main', [
'title' => "Change Orders | Modify Job Parameters",
'crumbs' => [
    ['text' => "Change Orders"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalAddChange();">
                        <i class="fa fa-plus"></i> Add Change Order
                    </a>
                    <a href="#" class="btn btn-warning mb-2" onclick="LoadData(true);">
                        <i class="fa fa-refresh"></i> Show All Orders
                    </a>
                </div>
                <table class="table changeTable table-striped mt-2" id="changeTable">
                    <thead>
                    <th>#</th>
                    <th>Job</th>
                    <th>Created</th>
                    <th>By</th>
                    <th>Sent</th>
                    <th>Signed</th>
                    <th>Parts Ordered</th>
                    <th>Items</th>
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
    $('#changeTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_changes') }}",
                  data: {
                            "all": bool
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

function ShowModalAddChange()
{
    removeMessageModal();

    $('.modal-title').html('New Change Order');
    $('.modal-body').html('\
                              <h4>You are adding a new change order. This should only be done once a job has been started and a new item is added mid-job. Customers will be emailed a copy of the change order once complete to sign.</h4><h5><b>NOTE:</b> Once a customer signs a change order it is locked and no other items can be added. If the customer requests additional items after the change order has been signed, a new (additional) change order will need to be created.</h5>\
                              <form id="form_create_new_change" role="form" action="{{ route('create_changes') }}" method="post">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="notes" class="col-md-4 control-label">\
                                  Job:\
                                </label>\
                                <div class="col-md-8">\
                                    <select id="job_id" name="job_id" class="form-control" required>\
                                      <option value="">-- Select Job --</option>\
                                        @php echo $selectJob; @endphp\
                                    </select>\
                                </div>\
                              </div>\
                              <button type="submit" class="btn btn-primary">Save</button>\
                              </form>\
                              \
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    $('#myModal').modal('show');
}

</script>
@endsection
