@extends('layouts.main', [
'title' => "Payout " . $payout->id . ' | ' . ($payout->user ? $payout->user->name : 'Unassigned User'),
'crumbs' => [
    ['text' => "Payout " . $payout->id]
]])
@section('content')
    <div class="row">
      <div class="col-lg-6">
        <a href="{{ route('payouts.index') }}?id={{ $payout->id }}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Payout</a>
      </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-header bg-primary text-white">Job List</div>
              <form action="{{ route('update_payout', ['id' => $payout->id]) }}" method="post">
                {{ csrf_field() }}
              <div class="card-body">
				<div class="form-group form-row ">
				  <label for="terms" class="col-md-4 control-label">
					Customer Name:
				  </label>
				  <div class="col-md-8">
					<input type="text" id="customer_name" name="customer_name" class="form-control" value="{{ $job->quote->lead->customer->name }}" readonly />
				  </div>
				</div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    User to Pay:
                  </label>
                  <div class="col-md-8">
                    <select name="user_id" id="user_id" class="form-control">
                      <option value="0">--Select User--</option>
                      @foreach($users as $user)
                        <option value="{{ $user->id }}" @php if($payout->user_id == $user->id) echo 'selected'; @endphp>{{ $user->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Total to Pay:
                  </label>
                  <div class="col-md-8">
                    <div class="input-group">
                    <span class="input-group-addon">$</span>
                    <input type="text" id="total" name="total" placeholder="" value="{{ $payout->total }}" class="form-control " id="total" required>
                    </div>
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Vendor Invoice #:
                  </label>
                  <div class="col-md-8">
                    <input type="text" name="invoice" placeholder="" value="{{ $payout->invoice }}" class="form-control " id="invoice">
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Notes:
                  </label>
                  <div class="col-md-8">
                    <textarea type="text" name="notes" class="form-control " id="notes" rows="4">{{ $payout->notes }}</textarea>
                  </div>
                </div>
				<div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Paid:
                  </label>
                  <div class="col-md-8">
                    <select name="paid" id="paid" class="form-control">
                      <option value="0" @php if(!$payout->paid) echo 'selected'; @endphp>No</option>
                      <option value="1" @php if($payout->paid) echo 'selected'; @endphp>Yes</option>
                    </select>
                  </div>
                </div>
				<div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Paid On:
                  </label>
                  <div class="col-md-8">
					<input class="form-control" name="paid_on" type="text" id="paid_on" class="form-control datetimepicker-input" data-toggle="datetimepicker" data-target="#paid_on" />
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Approved:
                  </label>
                  <div class="col-md-8">
                    <select name="approved" id="approved" class="form-control">
                      <option value="0" @php if(!$payout->approved) echo 'selected'; @endphp>No</option>
                      <option value="1" @php if($payout->approved) echo 'selected'; @endphp>Yes</option>
                    </select>
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Frugal Check #:
                  </label>
                  <div class="col-md-8">
                    <input type="text" name="check" placeholder="" value="{{ $payout->check }}" class="form-control " id="check">
                  </div>
                </div>
                <div style="text-align:center;">
                  <button class="btn btn-primary"><i class="fa fa-save"></i> Update Payout</button>
                </div>
              </div>
              <input type="hidden" name="payout_id" id="payout_id" value="{{ $payout->id }}" />
              <div class="card-footer" style="text-align:center;">
                  <a class='btn btn-info' href='{{ route("quote_view", ["id" => $payout->job->quote->id]) }}'>Sold from Quote {{ $payout->job->quote->id }}</a>
                  <a class='btn btn-danger' href='{{ route("delete_payout", ["id" => $payout->id]) }}'>Delete Payout</a>
              </div>
            </form>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card">
              <div class="card-header bg-primary text-white">Items for Payout (<a href="{{ route('payouts.edit', ['id' => $payout->id]) }}">Clear Form</a>)</div>
              <div class="card-body">
                <table class="table frugalTable table-striped mt-2 table-bordered" id="payoutItemTable">
                    <thead>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Delete</th>
                    </thead>
                    <tbody id="body_payout_item"></tbody>
                </table>
              </div>
              <div class="card-body">
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Item:
                  </label>
                  <div class="col-md-8">
                    <input type="text" name="item" class="form-control " id="item" required>
                  </div>
                </div>
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Amount:
                  </label>
                  <div class="col-md-8">
                    <input type="text" name="amount" class="form-control " id="amount" required>
                  </div>
                </div>
              </div>
              <input type="hidden" name="payout_item_id" id="payout_item_id" value="" />
              <div class="card-footer" style="text-align:center;">
                <a href="#" class="btn btn-success" onclick="AddPayoutItem();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                <a href="#" class="btn btn-info" onclick="UpdatePayoutItem();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
              </div>
          </div>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){

  LoadPayoutItemsData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif

  $('#paid_on').datetimepicker({
                    format: 'L'
                });

  $('#paid_on').val('{{ \Carbon\Carbon::parse($payout->paid_on)->format("m/d/Y") }}');
});

function LoadPayoutItemsData()
{
    $.ajax({
            url:"{{ route('display_payout_items') }}",
            type:'GET',
            data:{
                    "payout_id": $('#payout_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#body_payout_item').html(res.data);                    
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function AddPayoutItem()
{
    $.ajax({
            url:"{{ route('payout_items.store') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'item' : $('#item').val(),
                  'amount' : $('#amount').val(),
                  'payout_id' : $('#payout_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadPayoutItemsData();
					$('#total').val(res.total);

                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function EditPayoutItem(payout_item_id)
{
    $.ajax({
            url:"{{ route('get_payout_item') }}",
            type:'GET',
            data:{
                  'payout_item_id' : payout_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html(res.title);
                    $('#item').val(res.item);
                    $('#amount').val(res.amount);
                    $('#payout_item_id').val(res.payout_item_id);
                    $('#btn_update').attr('style', '');
                    $('#btn_add').attr('style', 'display:none;');
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function UpdatePayoutItem()
{
    $.ajax({
            url:"{{ route('update_payout_item') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'item' : $('#item').val(),
                  'amount' : $('#amount').val(),
                  'payout_item_id' : $('#payout_item_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadPayoutItemsData();
					$('#total').val(res.total);
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function DeletePayoutItem(payout_item_id)
{
    $.ajax({
            url:"{{ route('delete_payout_item') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'payout_item_id' : payout_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadPayoutItemsData();
					$('#total').val(res.total);
                }
                else
                {
                    setStatusMessage("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
}

function ClearForm()
{
    $('#item').val('');
    $('#amount').val('');
    $('#payout_item_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
