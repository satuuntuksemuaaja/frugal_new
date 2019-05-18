@extends('layouts.main', [
'title' => "Dynamic",
'crumbs' => [
    ['text' => "Dynamic"]
]])
@section('content')
  <form action="{{ route('dynamic.store') }}" method="post">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b>Designer Amounts</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      < 35 Items:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dL35" name="dL35" class="form-control" value="{{ $quote->getSetting('dL35') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 35 and <= 55:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG35L55" name="dG35L55" class="form-control" value="{{ $quote->getSetting('dG35L55') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 55 and <= 65:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG55L65" name="dG55L65" class="form-control" value="{{ $quote->getSetting('dG55L65') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 65 and <= 75:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG65L75" name="dG65L75" class="form-control" value="{{ $quote->getSetting('dG65L75') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 75 and <= 85:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG75L85" name="dG75L85" class="form-control" value="{{ $quote->getSetting('dG75L85') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 85 and <= 94:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG85L94" name="dG85L94" class="form-control" value="{{ $quote->getSetting('dG85L94') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 94 and <= 110:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG94L110" name="dG94L110" class="form-control" value="{{ $quote->getSetting('dG94L110') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 110:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="dG110" name="dG110" class="form-control" value="{{ $quote->getSetting('dG110') }}" />
                    </div>
                  </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b>Frugal Amounts</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      < 35 Items:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fL35" name="fL35" class="form-control" value="{{ $quote->getSetting('fL35') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 35 and <= 55:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG35L55" name="fG35L55" class="form-control" value="{{ $quote->getSetting('fG35L55') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 55 and <= 65:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG55L65" name="fG55L65" class="form-control" value="{{ $quote->getSetting('fG55L65') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 65 and <= 75:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG65L75" name="fG65L75" class="form-control" value="{{ $quote->getSetting('fG65L75') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 75 and <= 85:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG75L85" name="fG75L85" class="form-control" value="{{ $quote->getSetting('fG75L85') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 85 and <= 94:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG85L94" name="fG85L94" class="form-control" value="{{ $quote->getSetting('fG85L94') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 94 and <= 110:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG94L110" name="fG94L110" class="form-control" value="{{ $quote->getSetting('fG94L110') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      > 110:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fG110" name="fG110" class="form-control" value="{{ $quote->getSetting('fG110') }}" />
                    </div>
                  </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white"><b>Vendor Payout Amounts</b></div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      For Electrician:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fElectrician" name="fElectrician" class="form-control" value="{{ $quote->getSetting('fElectrician') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      For Plumber:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="fPlumber" name="fPlumber" class="form-control" value="{{ $quote->getSetting('fPlumber') }}" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      XML Ignore List:
                    </label>
                    <div class="col-md-8">
                      <textarea id="xmlignore" name="xmlignore" class="form-control" rows="4">{{ $quote->getSetting('xmlignore') }}</textarea>
                    </div>
                  </div>
                </div>
            </div>
        </div>

    </div>
    <br/>
    <div class="row">
      <div class="col-lg-12">
        <div class="form-group form-row ">
          <label for="terms" class="col-md-6 control-label" style="text-align:right;">
            Common Sense Page:
          </label>
          <div class="col-md-6" style="text-align:left;">
            <textarea id="commonSense" name="commonSense" class="form-control" rows="6">{{ $quote->getSetting('commonSense') }}</textarea>
          </div>
        </div>
      </div>
    </div>
    <br/>
    <div class="row">
      <div class="col-lg-12" style="text-align:center;">
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Modifications</button>
      </div>
    </div>
  </form>
    <br/>
@endsection
@section('javascript')
<script>
$(function(){

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif
});

</script>
@endsection
