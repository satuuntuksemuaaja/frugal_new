@extends('layouts.main', [
'title' => "FAQs",
'crumbs' => [
    ['text' => "FAQs"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
          <div class="card">
              <div class="card-body">
              </div>
              <table class="table table-striped mt-2" id="faqTable">
                  <thead>
                  <th>Question</th>
                  <th>Quote Type</th>
                  <th>Image</th>
                  <th>Delete</th>
                  </thead>
                  <tbody></tbody>
              </table>
          </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white"><b id="title">New FAQ</b></div>
                <form id="form_faq" action="#" method="post" enctype="multipart/form-data">
                <div class="card-body">
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Question:
                    </label>
                    <div class="col-md-8">
                      <textarea id="question" name="question" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Answer:
                    </label>
                    <div class="col-md-8">
                      <textarea id="answer" name="answer" class="form-control" rows="4"></textarea>
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Image:
                    </label>
                    <div class="col-md-8">
                      <input type="file" id="image" name="image" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Figure:
                    </label>
                    <div class="col-md-8">
                      <input type="text" id="figure" name="figure" class="form-control" />
                    </div>
                  </div>
                  <div class="form-group form-row ">
                    <label for="terms" class="col-md-4 control-label">
                      Quote Type:
                    </label>
                    <div class="col-md-8">
                      <select id="quote_type_id" name="quote_type_id" class="form-control">
                        <option value="">--Select Quote Type--</option>
                        @foreach($quoteTypes as $quoteType)
                          <option value="{{ $quoteType->id }}">{{ $quoteType->name }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <input type="hidden" name="faq_id" id="faq_id" value="" />
                <div class="card-footer" style="text-align:center;">
                    <a href="#" class="btn btn-success" onclick="AddFaq();" id="btn_add"><i class="fa fa-check"></i> Add New</a>
                    <a href="#" class="btn btn-info" onclick="UpdateFaq();" id="btn_update" style="display:none;"><i class="fa fa-check"></i> Update</a>
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

  $("#btn_add").click(function(event) {
    event.preventDefault();
    var form = $('#form_faq')[0];
    var data = new FormData(form);

    if($('#quote_type_id').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please select quote type.');
        return;
    }
    if($('textarea#question').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please fill question.');
        return;
    }
    if($('textarea#answer').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please fill answer.');
        return;
    }

    $.ajax({
            url:"{{ route('faqs.store') }}",
            type: "POST",
            data: data,
            enctype: 'multipart/form-data',
            processData: false,  // Important!
            contentType: false,
            cache: false,
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                }
                else
                {
                    setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
  });

  $("#btn_update").click(function(event) {
    event.preventDefault();
    var form = $('#form_faq')[0];
    var data = new FormData(form);

    if($('#quote_type_id').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please select quote type.');
        return;
    }
    if($('textarea#question').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please fill question.');
        return;
    }
    if($('textarea#answer').val() == '')
    {
        setStatusMessage('danger', 'Error', 'Please fill answer.');
        return;
    }

    $.ajax({
            url:"{{ route('update_faq') }}",
            type: "POST",
            data: data,
            enctype: 'multipart/form-data',
            processData: false,  // Important!
            contentType: false,
            cache: false,
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
                }
                else
                {
                    setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {

            }
          });
  });

});

function LoadData()
{
    $('#faqTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_faqs') }}",
                  data: {

                        }
              },
      "bDeferRender": true,
      "searching": true,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"pageLength": parseInt($('#length').val()),
      "fnInitComplete": function(oSettings, json) {

      },
      "aoColumns" : [
        {},
        {},
        {},
        {}
      ]
    });
}

function EditFaq(faq_id)
{
    $.ajax({
            url:"{{ route('get_faq') }}",
            type:'GET',
            data:{
                  'faq_id' : faq_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#title').html('Edit FAQ');
                    $('textarea#question').val(res.question);
                    $('textarea#answer').val(res.answer);
                    $('#figure').val(res.figure);
                    $('#quote_type_id').val(res.quote_type_id);
                    $('#faq_id').val(res.faq_id);
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

function DeleteFaq(faq_id)
{
    $.ajax({
            url:"{{ route('delete_faq') }}",
            type:'POST',
            data:{
                  '_token':'{{ csrf_token() }}',
                  'faq_id' : faq_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    setStatusMessage("success", "Success", res.message);
                    ClearForm();
                    LoadData();
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
    $('#title').html('New FAQ');
    $('textarea#question').val('');
    $('textarea#answer').val('');
    var $el = $('#image');
    $el.wrap('<form>').closest('form').get(0).reset();
    $el.unwrap();
    $('#figure').val('');
    $('#quote_type_id').prop('selectedIndex', 0);
    $('#faq_id').val('');
    $('#btn_update').attr('style', 'display:none;');
    $('#btn_add').attr('style', '');
}

</script>
@endsection
