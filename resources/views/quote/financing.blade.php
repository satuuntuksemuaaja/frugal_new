@extends('layouts.main', [
'title' => "Financing Options",
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Financing Options"]
]])

@section('content')

@php
$meta = unserialize($quote->meta);
$meta = $meta['meta'];

$fields = [];
$all = $no = $partial = 'primary';

if (isset($meta['finance']))
{
    $all = ($meta['finance']['type'] == 'all') ? 'success' : 'primary';
    $no = ($meta['finance']['type'] == 'none') ? 'success' : 'primary';
    $partial = ($meta['finance']['type'] == 'partial') ? 'success' : 'primary';
}
@endphp
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">

                    <form id="form_100_financing" role="form" action="{{ route('quote_financing_save', ['id' => $quote->id, 'type' => 'all']) }}" method="post">
                    {{ csrf_field() }}
                    <div class="card-header bg-{{ $all }} text-white"><b>100% Financing</b></div>
                    <div class="card-body">

                        This form is only to be used if the customer requests <b>100%</b> financing. This assumes that the customer is putting no money down.
                        <br/><br/>
                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Select Financing Terms:
                        </label>
                        <div class="col-md-8">
                          <select class="form-control" name="terms" id="terms">
                          <option value="12">0% for 12 Months (Wells Fargo)</option>
                          <option value="65">0% for 12 Months (Wells Fargo)</option>
                          @if($quote->final)
                            <option value="12G">0% for 12 Months (GreenSky)</option>
                            <option value="84G">9.9% for 84 Months (GreenSky)</option>
                          @endif
                          </select>
                        </div>
                      </div>

                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-money"></i> Set 100% Financing Option</button>
                    </div>
                  </form>
                </div>
            </div>

@php
$nocredit = (isset($meta['finance']['no_credit']) && $meta['finance']['no_credit'] > 0) ? $meta['finance']['no_credit'] : '0.00';
$nocash = (isset($meta['finance']['no_cash']) && $meta['finance']['no_cash'] > 0) ? $meta['finance']['no_cash'] : '0.00';
@endphp

            <div class="col-lg-4">
                <div class="card groupBody">

                    <div class="card-header bg-{{ $no }} text-white"><b>No Financing</b></div>
                    <div class="card-body">
                      <form id="form_100_financing" role="form" action="{{ route('quote_financing_save', ['id' => $quote->id, 'type' => 'none']) }}" method="post">
                      {{ csrf_field() }}

                        This form is only to be used if the customer requests <b>NO</b> financing. This assumes that the customer is paying the total price via Cash, Check, or Credit Card.
                        <br/><br/>
                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Select Method of Payment (IF 100%):
                        </label>
                        <div class="col-md-8">
                          <select class="form-control" name="terms" id="terms">
                          <option value="credit">Credit Card</option>
                          <option value="cash">Cash/Check</option>
                          <option value="split">Split Payment</option>
                          </select>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Total (if split) payment in Cash/Check:
                        </label>
                        <div class="col-md-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            <input id="no_cash" type="text" class="form-control" name="no_cash" placeholder="0.00" value="0.00">
                          </div>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Total (if split) payment in Credit:
                        </label>
                        <div class="col-md-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            <input id="no_credit" type="text" class="form-control" name="no_credit" placeholder="0.00" value="0.00">
                          </div>
                        </div>
                      </div>

                    </div>
                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-money"></i> Set No Financing Option</button>
                    </div>

                  </form>

                </div>
            </div>


@php
$dp = (isset($meta['finance']['downpayment']) && $meta['finance']['downpayment'] > 0) ? $meta['finance']['downpayment'] : '0.00';
$downcredit = (isset($meta['finance']['down_credit']) && $meta['finance']['down_credit'] > 0) ? $meta['finance']['down_credit'] : '0.00';
$downcash = (isset($meta['finance']['down_cash']) && $meta['finance']['down_cash'] > 0) ? $meta['finance']['down_cash'] : '0.00';
@endphp
            <div class="col-lg-4">
                <div class="card groupBody">

                    <div class="card-header bg-{{ $partial }} text-white"><b>Partial Financing</b></div>
                    <div class="card-body">
                      <form id="form_partial_financing" role="form" action="{{ route('quote_financing_save', ['id' => $quote->id, 'type' => 'partial']) }}" method="post">
                      {{ csrf_field() }}

                      This form is only to be used if the customer requests <b>partial</b> financing. This assumes that the customer is putting down some money and financing the remainder of the balance.
                      <br/><br/>
                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Total Down Payment Amount:
                        </label>
                        <div class="col-md-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            <input id="downpayment" type="text" class="form-control" name="downpayment" placeholder="0.00" value="{{ $dp }}">
                          </div>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Down payment in Cash:
                        </label>
                        <div class="col-md-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            <input id="down_cash" type="text" class="form-control" name="down_cash" placeholder="0.00" value="{{ $downcash }}">
                          </div>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Down payment in Credit:
                        </label>
                        <div class="col-md-8">
                          <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-dollar"></i></span>
                            <input id="down_credit" type="text" class="form-control" name="down_credit" placeholder="0.00" value="{{ $downcredit }}">
                          </div>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="terms" class="col-md-4 control-label">
                          Select Financing Terms For Remainder
                        </label>
                        <div class="col-md-8">
                            <select class="form-control" name="terms" id="terms">
                              <option value="12">0% for 12 Months (Wells Fargo)</option>
                              <option value="65">0% for 12 Months (Wells Fargo)</option>
                              @if($quote->final)
                                <option value="12G">0% for 12 Months (GreenSky)</option>
                                <option value="84G">9.9% for 84 Months (GreenSky)</option>
                              @endif
                            </select>
                          </div>
                        </div>
                      </div>

                    </div>

                    <div class="card-footer" style="text-align:center;">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-money"></i> Set Partial Financing Option</button>
                    </div>

                  </form>

                </div>
            </div>

        </div>
    </div>
    <div class="row">
      <div class="col-12 text-center">
        <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
      </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){
  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{{ session("error") }}');
  @endif
});

</script>
@endsection
