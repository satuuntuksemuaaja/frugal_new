@extends('layouts.main', [
'title' => "Receiving | Accept Purchase Order Items",
'crumbs' => [
    ['text' => "Receiving"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('buildup') }}" class="btn btn-warning mb-2">
                        <i class="fa fa-arrow-right"></i> Go to Buildup
                    </a>
                </div>
                <table class="table receivingTable table-striped mt-2" id="receivingTable">
                    <thead>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>PO</th>
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
                  url: "{{ route('display_receiving') }}",
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
        {}
      ]
    });
}
</script>
@endsection
