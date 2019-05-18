@extends('layouts.main', [
'title' => "Send Job Schedule",
'crumbs' => [
    ['text' => "Send Job Schedule"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                  <textarea id="summernote" name="summernote" class="form-control">{{ $body }}</textarea>
                </div>
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="description" class="col-md-4 control-label">
                      E-mail Address to CC: (if Any)
                    </label>
                    <div class="col-md-8">
                      <input type="text" class="form-control" name="cc" id="cc">
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="description" class="col-md-4 control-label">
                      CC Frugal User:
                    </label>
                    <div class="col-md-8">
                      <select class="form-control" name="user_id" id="user_id">
                          <option value="">-- Select Frugal User --</option>
                          @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                          @endforeach
                      </select>
                    </div>
                  </div>
                </div>
            </div>
            <br/>
            <a href="#" class="btn btn-success btn-lg" onclick="DoSend();"><i class="fa fa-arrow-right"></i> Send to Customer</a>
        </div>
    </div>
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){

  $('#summernote').summernote();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif


});

function DoSend()
{
  $.ajax({
          url:"{{ route('job_final_send_schedule', ['id' => $job->id]) }}",
          type:'POST',
          data:{
                '_token':'{{ csrf_token() }}',
                'body' : $('textarea#summernote').val(),
                'cc' : $('#cc').val(),
                'user_id' : $('#user_id').val()
          },
          success: function (res)
          {
              if(res.response == "success")
              {
                  setStatusMessage("success", "Success", res.message);
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

</script>
@endsection
