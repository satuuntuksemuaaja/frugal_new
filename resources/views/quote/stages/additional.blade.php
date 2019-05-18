@extends('layouts.main', [
'title' => "Additional Items | " . $quote->lead->customer->name . " | " . (($quote->final) ? "Final" : "Initial") . " Quote",
'crumbs' => [
    ['text' => "Additional", 'url' => "/quotes"],
    ['text' =>  "Additional"]
]])

@section('content')

@php

$meta = unserialize($quote->meta);
$meta = $meta['meta'];

@endphp
<div class="row">
    <div class="col-lg-12">
      <div class="card groupBody">
        <form id='questionaireForm' method='post' action='{{ route("save_quote_additional", ["id" => $quote->id]) }}'>
          {{ csrf_field() }}
          <div class="card-header bg-primary text-white"><b>Additional Requirements</b></div>
          <div class="card-body">

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                For <strong>Miscellaneous items</strong>, Enter each additional item per line like:
                (<b>Item 1 - 450.00</b>)
              </label>
              <div class="col-md-8">
                <textarea name="quote_misc" id="quote_misc" class="form-control">{{ (isset($meta['quote_misc'])) ? $meta['quote_misc'] : null }}</textarea>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                For <strong>Plumbing items</strong>,  Enter each additional item per line like:
                (<b>Item 1 - 450.00</b>)
              </label>
              <div class="col-md-8">
                <textarea name="quote_plumbing_extras" id="quote_plumbing_extras" class="form-control">{{ (isset($meta['quote_plumbing_extras'])) ? $meta['quote_plumbing_extras'] : null }}</textarea>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                For <strong>Electrical items</strong>,  each additional item per line like:
                (<b>Item 1 - 450.00</b>)
              </label>
              <div class="col-md-8">
                <textarea name="quote_electrical_extras" id="quote_electrical_extras" class="form-control">{{ (isset($meta['quote_electrical_extras'])) ? $meta['quote_electrical_extras'] : null }}</textarea>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                For <strong>Installer items</strong>, Enter each additional item per line like:
                (<b>Item 1 - 450.00</b>)
              </label>
              <div class="col-md-8">
                <textarea name="quote_installer_extras" id="quote_installer_extras" class="form-control">{{ (isset($meta['quote_installer_extras'])) ? $meta['quote_installer_extras'] : null }}</textarea>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                Special Requirements/Instructions
              </label>
              <div class="col-md-8">
                <textarea name="quote_special" id="quote_special" class="form-control">{{ (isset($meta['quote_special'])) ? $meta['quote_special'] : null }}</textarea>
                <p class="help-block mt-1 text-muted" style="font-size: 12px;">The information listed here will appear on page 6 of the contract to the customer</p>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                Coupon (if any)
              </label>
              <div class="col-md-8">
                <input type="text" name="quote_coupon" id="quote_coupon" class="form-control" value="{{ (isset($meta['quote_coupon'])) ? $meta['quote_coupon'] : null }}" />
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                Additional Discount Amount (if any)
              </label>
              <div class="col-md-8">
                <input type="text" name="quote_discount" id="quote_discount" class="form-control" value="{{ (isset($meta['quote_discount'])) ? $meta['quote_discount'] : null }}" />
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
                Discount Reason (if additional discounts)
              </label>
              <div class="col-md-8">
                <textarea name="quote_discount_reason" id="quote_discount_reason" class="form-control">{{ (isset($meta['quote_discount_reason'])) ? $meta['quote_discount_reason'] : null }}</textarea>
              </div>
            </div>

            <div class="form-group form-row ">
              <label for="terms" class="col-md-4 control-label">
              Select Promotion
              </label>
              <div class="col-md-8">
                <select name="promotion_id" id="promotion_id" class="form-control">
                  <option value="0">-- Select Promotion --</option>
                  @foreach($promotions as $promotion)
                    <option value="{{ $promotion->id }}" @php if($quote->promotion_id == $promotion->id) echo "selected"; @endphp>{{ $promotion->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>

          </div>
        <div class="card-footer" style="text-align:center;">
            <button type="submit" class="btn btn-danger"><i class="fa fa-save"></i> Save Requirements</button>
        </div>
      </form>
      </div>
      <br/>
    </div>
  </div>
  <br/>
  @php
  $pass = true;
  @endphp
  <div class="row">
    <div class="col-12 text-center">
      <span class="btn-group">
        <a href="{{ route('quote_hardware', ['id' => $quote->id]) }}" class="btn btn-warning btn-lg"><i class="fa fa-arrow-left"></i> Review Hardware</a>
        @if ($pass && ($quote->type != 'Cabinet Only' && $quote->type != 'Cabinet and Install' && $quote->type != 'Builder'))
        <a href="{{ route('quote_questionaire', ['id' => $quote->id]) }}" class="btn btn-success btn-lg"><i class="fa fa-arrow-right"></i> Next</a>
        @endif
        <a href="{{ route('quote_view', ['id' => $quote->id]) }}" class="btn btn-info btn-lg"><i class="fa fa-share"></i> Quote Overview</a>
      </span>
    </div>
  </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){

  @if(session('success'))
    setStatusMessage('success', 'Success', '{!! session("success") !!}');
  @endif
  @if(session('error'))
    setStatusMessage('danger', 'Error', '{!! session("error") !!}');
  @endif
});
</script>
@endsection
