@extends('layouts.main', [
'title' => "Payout Manager",
'crumbs' => [
    ['text' => "Payout Manager"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
          <div class="card">
              <div class="card-header bg-primary text-white">Job List - <a href="{{ route('payouts.index') }}?all=true" id="link">Show Archived</a></div>
              <div class="card-body">
                <table class="table table-striped mt-2" id="payoutTable">
                    <thead>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Job Date</th>
                    <th>Signed</th>
                    <th>Contractor</th>
                    <th>Amount</th>
                    <th>Invoice</th>
                    <th>Check</th>
                    <th>Approved</th>
                    <th>Paid</th>
                    </thead>
                    <tbody id="body_payout"></tbody>
                </table>
              </div>
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

  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif

  @if($request->has('all'))
      $('#link').attr('href', '{{ route('payouts.index') }}');
      $('#link').html('Show Unpaid');
  @endif
});

function LoadData()
{
  var all = 0;
  @if($request->has('all'))
    all = 1;
  @endif
  $.ajax({
          url:"{{ route('load_payouts') }}",
          type:'GET',
          data:{
                  "all": all
          },
          beforeSend: function () {
              $('#body_payout').html("Loading.... <b><font color='red'>(Give this a minute to load..)</font></b>");
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  $('#body_payout').html(res.data);
                  @if(isset($request->id))
                    var section = $('a[href="{{ route('payouts.edit', ['id' => $request->id]) }}"]');
                    $("html, body").animate({
                        scrollTop: $(section).offset().top - 200
                    });
                  @endif
              }
          },
          error: function(a, b, c)
          {

          }
        });
}

</script>
@endsection
