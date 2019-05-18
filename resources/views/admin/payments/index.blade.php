@extends('layouts.main', [
'title' => "Payments",
'crumbs' => [
    ['text' => "Payments"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <form id="paymentForm" action="{{ route('payments.store') }}" method="post">
            {{ csrf_field() }}
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">Payments Data</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Account Type:
                    </label>
                    <div class="col-md-8">
                      <select name="type" id="type" class="form-control">
                        <option value="C">Credit Card</option>
                        <option value="A">ACH/Checking</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Payment Amount:
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="amount" id="amount" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Billing Address:
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="address" id="address" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Suite Number (opt):
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="address2" id="address2" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Billing City:
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="city" id="city" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Billing State:
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="state" id="state" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Billing Zip:
                    </label>
                    <div class="col-md-8">
                      <input type="text" name="zip" id="zip" class="form-control" maxlength="5" />
                    </div>
                  </div>

                  <!-- Credit Card values for Pre-auth. -->
                  <span id="div_cc">
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Name on Card:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="cc_name" id="cc_name" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Card Number:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="cc_number" id="cc_number" class="form-control" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Expiration Date:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="cc_exp" id="cc_exp" class="form-control" maxlength="4" />
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Security Code:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="cc_cvv" id="cc_cvv" class="form-control" maxlength="4" />
                      </div>
                    </div>
                  </span>

                  <!-- Check Processing. -->
                  <span id="div_processing" style="display:none;">
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Authorized Billing Contact:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="ach_name" id="ach_name" class="form-control" />
                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">First and Last Name</p>
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Account Type:
                      </label>
                      <div class="col-md-8">
                        <select name="ach_type" id="ach_type" class="form-control" />
                          <option value="C">Checking</option>
                          <option value="S">Savings</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Routing Number:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="ach_route" id="ach_route" class="form-control" maxlength="9" />
                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">9 Digit Number, far left side of check</p>
                      </div>
                    </div>
                    <div class="form-group form-row ">
                      <label for="terms" class="col-md-4 control-label">
                        Account Number:
                      </label>
                      <div class="col-md-8">
                        <input type="text" name="ach_account" id="ach_account" class="form-control" />
                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Account number after routing number.</p>
                      </div>
                    </div>
                  </span>

                </div>
                <div class="card-footer" style="text-align:center;">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Authorize Account</button>
                </div>
            </div>
          </form>
        </div>
    </div>
@endsection
@section('javascript')
<script>
$(function(){

  $("#type").change(function(){
      if($('#type').val() == 'C')
      {
          $('#div_cc').attr('style', '');
          $('#div_processing').attr('style', 'display:none;');
      }
      else if($('#type').val() == 'A')
      {
          $('#div_cc').attr('style', 'display:none;');
          $('#div_processing').attr('style', '');
      }
  });

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif
});

</script>
@endsection
