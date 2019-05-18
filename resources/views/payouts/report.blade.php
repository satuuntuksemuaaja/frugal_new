@extends('layouts.main', [
'title' => "Payout for " . $user->name . " | Create Report",
'crumbs' => [
    ['text' => "Create Report"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
              <form method='post' action='{{ route('create_report_payout', ['id' => $user->id]) }}'>
                {{ csrf_field() }}
              <div class="card-body">
                <table class="table table-striped mt-2" id="payoutReportTable">
                    <thead>
                    <th><input type="checkbox" id="checkAll"></input></th>
                    <th>Customer</th>
                    <th>Start Date</th>
                    <th>Paid On</th>
                    <th>Check #</th>
                    <th>Total</th>
                    <th>Items</th>
                    </thead>
                    <tbody id="body_payout_report"></tbody>
                </table>
              </div>
              <div class="card-footer">
                  <input type='submit' class='btn btn-success' value='Create Report'>
              </div>
            </form>
          </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){

  LoadData();

  $('#checkAll').change(function () {
    if($('#checkAll').is(':checked')) $('input:checkbox[name^="p_"]').prop('checked', true);
    else $('input:checkbox[name^="p_"]').prop('checked', false);
 });

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

function LoadData()
{
  $.ajax({
          url:"{{ route('get_report_payouts') }}",
          type:'GET',
          data:{
                  "user_id": "{{ $user->id }}"
          },
          beforeSend: function () {
              $('#body_payout_report').html("Loading.... <b><font color='red'>(Give this a minute to load..)</font></b>");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_payout_report').html(res.data);
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

</script>
@endsection
