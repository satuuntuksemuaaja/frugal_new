@extends('layouts.main', [
'title' => "$customer->name | $title items",
'crumbs' => [
    ['text' => "Job Punches"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('fft_signoff_pdf', ['id' => $fft->id]) }}" class="btn btn-primary mb-2" target="_blank">
                        <i class="fa fa-download"></i> Download Pdf
                    </a>
                </div>
                <table class="table quoteTable table-striped mt-2" id="punchTable">
                    <thead>
                    <th>Item</th>
                    <th>Status</th>
                    <th>Group</th>
                    <th>Office Notes</th>
                    <th>Contractor Notes</th>
                    <th>Completed ?</th>
                    <th>Created</th>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
      </div>
      <br>
      <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><b>Add Punch</b></div>
                <div class="card-body">
                    <div id="div_add_punch_msg"></div>
                    <form id="form_add_job_item" role="form" action="fft/{{ $fft->id }}/item/create" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="form-group form-inline">
                      <label>New Punch List Item: </label>&nbsp;
                      <input type="text" class="form-control" id="item_reference" name="item_reference" required/>
                    </div>

                    <div class="form-group form-inline">
                      <input type="checkbox" class="form-control" id="item_orderable" name="item_orderable" checked>&nbsp; Item must be ordered</input>
                    </div>

                    <div class="form-group form-inline">
                      <input type="checkbox" class="form-control" id="item_replacement" name="item_replacement">&nbsp; Item is a replacement part</input>
                    </div>

                    <div class="form-group form-inline">
                      <input type="checkbox" class="form-control" id="item_shop" name="item_shop">&nbsp; Send to Shop Work</input>
                    </div>

                    <div class="form-group form-inline">
                      <label>Replacement Image #1: </label>&nbsp;
                      <input type="file" class="form-control" id="replacement_img_1" name="replacement_img_1" /><small>If this is a replacement part you must upload at least 1 image.</small>
                    </div>

                    <div class="form-group form-inline">
                      <label>Replacement Image #2: </label>&nbsp;
                      <input type="file" class="form-control" id="replacement_img_2" name="replacement_img_2" />
                    </div>

                    <div class="form-group form-inline">
                      <label>Replacement Image #3: </label>&nbsp;
                      <input type="file" class="form-control" id="replacement_img_3" name="replacement_img_3" />
                    </div>

                    <button class="btn btn-primary" id="btn_save_job_item">Save</button>
                  </form>
                  <br>
                  @php
                    $textSignOff = 'FFT';
                    $type = '';
                    if($request->has('warranty'))
                    {
                        $textSignOff = 'Warranty';
                        $type = '?warranty=1';
                    }
                    elseif($request->has('service'))
                    {
                        $textSignOff = 'Service';
                        $type = '?service=1';
                    }
                    else
                    {
                        $textSignOff = 'FFT';
                    }
                  @endphp
                    <a href="{{ route('sigoff_fft', ['id' => $fft->id]) }}{{ $type }}" class="btn btn-success"><i class="fa fa-arrow-right"></i>
                      {{ $textSignOff }} Sign Off</a>
                </div>
            </div>
        </div>

        @if(!$fft->paid)
        <div class="col-lg-6">
            <div class="card-header bg-danger text-white">Punches are currently not marked as paid.</div>
            <br/>
            <div class="card">
                <div class="card-header"><b>Payment Status</b></div>
                <div class="card-body">

                  <form id="form_fft_pay" role="form" action="{{ route('fft_pay', ['id' => $fft->id]) }}" method="post">
                  {{ csrf_field() }}
                    <div class="form-group form-inline">
                      <label>All Punches Paid? </label>&nbsp;
                      <select class="form-control" id="paid" name="paid">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                      </select>
                    </div>

                    <div class="form-group form-inline">
                      <label>Notes: </label>&nbsp;
                      <textarea id="paid_reason" name="paid_reason" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-primary"><i class="fa fa-check"></i> Save</button>
                  </form>
                </div>
            </div>
        </div>
        @endif

      </div>
@endsection
@section('javascript')
<script type="text/javascript">

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif

  $("#form_add_job_item").submit(function( event ) {
    if($('#item_replacement').is(':checked'))
    {
        if($("#replacement_img_1")[0].files.length == 0)
        {
          setStatusAddPunchMessage('danger', 'Error', 'Please select image on Replacement Image #1.');
          event.preventDefault();
        }
    }

  });

});

function LoadData()
{
    $('#punchTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_punch_items') }}",
                  data: {
                            fft_id: '{{ $fft->id }}'
                        }
              },
      "bDeferRender": true,
      "searching": true,
      "destroy": true,
      "processing": false,
      "serverSide": false,
      //"dom": 'ftipr',
      //"iDisplayLength" : $('#length').val(),
      //"fnInitComplete": function(oSettings, json) {
      //  $('#totalRecord').val(json.iTotalRecords);
      //},
      //"pageLength": parseInt($('#length').val()),
      "drawCallback": function() {
      },
      "fnInitComplete": function(oSettings, json) {
      },
      "aoColumns" : [
        {},
        {},
        {},
        {},
        {},
        {},
        {}
      ]
    });
}

function ShowModalEditReference(job_item_id)
{
    removeMessageModal();

    $('.modal-title').html('Update List Item');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="name" class="col-md-4 control-label">\
                                  Reference:\
                                </label>\
                                <div class="col-md-8">\
                                    <input class="form-control" id="reference" name="reference" type="text" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the reference item</p>\
                                </div>\
                              </div>\
                            \
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetItemReference(\'' + job_item_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetItemReference(job_item_id);
}

function GetItemReference(job_item_id)
{
    $.ajax({
            url:"{{ route('get_job_item') }}",
            type:'GET',
            data:{
                  "job_item_id": job_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#reference').val(res.reference);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetItemReference(job_item_id)
{
    $.ajax({
            url:"{{ route('set_job_item') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_item_id": job_item_id,
                  "reference": $('#reference').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function ShowModalSetGroup(job_item_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Group');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="group_id" class="col-md-4 control-label">\
                                  Group:\
                                </label>\
                                <div class="col-md-8">\
                                    <select class="form-control" name="group_id" id="group_id">\
                                    <option value="">-- Select Group -- </option>\
                                    @foreach($groups as $group)\
                                      <option value="{{ $group->id }}">{{ $group->name }}</option>\
                                    @endforeach\
                                    </select>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the group</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetGroup(\'' + job_item_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetGroup(job_item_id);
}

function GetGroup(job_item_id)
{
    $.ajax({
            url:"{{ route('get_group') }}",
            type:'GET',
            data:{
                  "job_item_id": job_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('#group_id').val(res.group_id);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetGroup(job_item_id)
{
    $.ajax({
            url:"{{ route('set_group') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_item_id": job_item_id,
                  "group_id": $('#group_id').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function ShowModalEditNotes(job_item_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="notes" class="col-md-4 control-label">\
                                  Notes:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea id="notes" class="form-control"></textarea>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the notes</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetNotes(\'' + job_item_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetNotes(job_item_id);
}

function GetNotes(job_item_id)
{
    $.ajax({
            url:"{{ route('get_notes') }}",
            type:'GET',
            data:{
                  "job_item_id": job_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('textarea#notes').val(res.notes);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetNotes(job_item_id)
{
    $.ajax({
            url:"{{ route('set_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_item_id": job_item_id,
                  "notes": $('textarea#notes').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function ShowModalEditContractorNotes(job_item_id)
{
    removeMessageModal();

    $('.modal-title').html('Set Contractor Notes');
    $('.modal-body').html('\
                              <div class="form-group form-row ">\
                                <label for="contractor_notes" class="col-md-4 control-label">\
                                  Contractor Notes:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea id="contractor_notes" class="form-control"></textarea>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the contractor notes</p>\
                                </div>\
                              </div>\
                          ');

    $('.modal-footer').html('\
                              <a href="#" class="btn btn-primary" onclick="SetContractorNotes(\'' + job_item_id + '\')">Save</a>\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>\
                            ');

    GetContractorNotes(job_item_id);
}

function GetContractorNotes(job_item_id)
{
    $.ajax({
            url:"{{ route('get_contractor_notes') }}",
            type:'GET',
            data:{
                  "job_item_id": job_item_id
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                    $('textarea#contractor_notes').val(res.contractor_notes);
                }
                $('#myModal').modal('show');
            },
            error: function(a, b, c)
            {

            }
          });
}

function SetContractorNotes(job_item_id)
{
    $.ajax({
            url:"{{ route('set_contractor_notes') }}",
            type:'POST',
            data:{
                  "_token":"{{ csrf_token() }}",
                  "job_item_id": job_item_id,
                  "contractor_notes": $('textarea#contractor_notes').val()
            },
            success: function (res)
            {
                if(res.response == "success")
                {
                  setMessageModal("success", "Success", res.message);
                  setStatusMessage("success", "Success", res.message);
                  CloseModal();
                  LoadData();
                }
                else if(res.response == "error")
                {
                  setMessageModal("danger", "Error", res.message);
                }
            },
            error: function(a, b, c)
            {
                setMessageModal("danger", "Error", "Save not commited!");
            }
          });
}

function setStatusAddPunchMessage(type, title, message)
{
  $('#div_add_punch_msg').html('\
                                    <div class="alert alert-' + type + ' alert-dismissable">\
                                      <strong>'+ title + '!</strong> ' + message + '\
                                      <a href="#" data-dismiss="alert" aria-label="close">&times;</a>\
                                    </div>\
                                  ');
}

</script>
@endsection
