@extends('layouts.main', [
'title' => "Stores",
'crumbs' => [
    ['text' => "Stores"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('stores.create') }}" class="btn btn-primary mb-2">
                        <i class="fa fa-plus"></i> Create Store
                    </a>
                </div>
                <table class="table leadTable table-striped mt-2" id="locationTable">
                    <thead>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Number</th>
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
    $('#locationTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_locations') }}",
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
        {}
      ]
    });
}

</script>
@endsection
