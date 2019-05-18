@extends('layouts.main', [
'title' => $question->question ?: "Create Question",
'crumbs' => [
    ['text' => "Questionnaire", 'url' => "/admin/questions"],
    ['text' => $question->question ?: "Create Question"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-6">
                <div class="card vendorBody">
                    <div class="card-body">
                        @include('admin.questions.fields', ['question' => $question])
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('javascript')
<script>
$(function(){
    @if($question && $question->contract)
      $('#contract').prop('checked', true);
    @else
      $('#contract').prop('checked', false);
    @endif

    @if($question && $question->small_job)
      $('#small_job').prop('checked', true);
    @else
      $('#small_job').prop('checked', false);
    @endif

    @if($question && $question->on_checklist)
      $('#on_checklist').prop('checked', true);
    @else
      $('#on_checklist').prop('checked', false);
    @endif

    @if($question && $question->on_job_board)
      $('#on_job_board').prop('checked', true);
    @else
      $('#on_job_board').prop('checked', false);
    @endif
});

function SetContract()
{
    if($('#contract').is(':checked'))
    {
        $('#contract').prop('checked', true);
        $('#contract').val('1');
    }
    else
    {
      $('#contract').prop('checked', false);
      $('#contract').val('0');
    }
}

function SetSmallJob()
{
    if($('#small_job').is(':checked'))
    {
        $('#small_job').prop('checked', true);
        $('#small_job').val('1');
    }
    else
    {
      $('#small_job').prop('checked', false);
      $('#small_job').val('0');
    }
}

function SetOnChecklist()
{
    if($('#on_checklist').is(':checked'))
    {
        $('#on_checklist').prop('checked', true);
        $('#on_checklist').val('1');
    }
    else
    {
      $('#on_checklist').prop('checked', false);
      $('#on_checklist').val('0');
    }
}

function SetOnJobBoard()
{
    if($('#on_job_board').is(':checked'))
    {
        $('#on_job_board').prop('checked', true);
        $('#on_job_board').val('1');
    }
    else
    {
      $('#on_job_board').prop('checked', false);
      $('#on_job_board').val('0');
    }
}
</script>
@endsection
