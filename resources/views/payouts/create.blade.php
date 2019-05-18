@extends('layouts.main', [
'title' => "Create Payout",
'crumbs' => [
    ['text' => "Create Payout"]
]])
@section('content')
    <div class="row">
      <div class="col-lg-6">
        <a href="{{ route('payouts.index') }}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> Back to Payout</a>
      </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-header bg-primary text-white">Create Payout</div>
              <form action="{{ route('payouts.store') }}" method="post">
                {{ csrf_field() }}
              <div class="card-body">
                <div class="form-group form-row ">
                  <label for="terms" class="col-md-4 control-label">
                    Group:
                  </label>
                  <div class="col-md-8">
                    <select name="group_id" id="group_id" class="form-control">
                      @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>
              <input type="hidden" name="job_id" id="job_id" value="{{ $job_id }}" />
              <div class="card-footer" style="text-align:center;">
                  <button class="btn btn-success" id="btn_add"><i class="fa fa-plus"></i> Create Payout</button>
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

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  @if(session('error'))
    setStatusMessage('error', 'Error', '{{ session("error") }}');
  @endif
});

</script>
@endsection
