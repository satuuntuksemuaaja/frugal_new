@extends('layouts.main', [
'title' => "#" . $po->number . " | " . $customer->name,
'crumbs' => [
    ['text' => "Receiving #" . $po->number]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('receiving') }}" class="btn btn-warning mb-2">
                        <i class="fa fa-arrow-left"></i> Go Back
                    </a>
                </div>
                <table class="table receivingTable table-striped mt-2" id="receivingTable">
                    <thead>
                    <th>Quantity</th>
                    <th>Item</th>
                    <th>Status</th>
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
    $('#receivingTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_receiving_po') }}",
                  data: {
                            'po_id': '{{ $po->id }}'
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
        {}
      ]
    });
}
</script>
@endsection
