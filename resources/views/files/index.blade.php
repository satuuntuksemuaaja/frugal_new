@extends('layouts.main', [
'title' => "Files",
'crumbs' => [
    ['text' => "Files"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <a href="#" class="btn btn-primary mb-2" onclick="ShowModalAddFile();">
                        <i class="fa fa-plus"></i> Add File
                    </a>
                </div>
                <div class="card-body">
                  <table class="table fileTable table-striped mt-2" id="fileTable">
                      <thead>
                      <th></th>
                      <th>Description</th>
                      <th>Uploaded By</th>
                      <th>Delete</th>
                      </thead>
                      <tbody></tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
.popover {
  top: 0;
  left: 0;
  z-index: 9999;
  max-width: 800px;
  padding: 1px;
  text-align: left;
  white-space: normal;
  background-color: #ffffff;
  border: 1px solid #ccc;
  border: 1px solid rgba(0, 0, 0, 0.2);
  -webkit-border-radius: 6px;
     -moz-border-radius: 6px;
          border-radius: 6px;
  -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
     -moz-box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
          box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
  -webkit-background-clip: padding-box;
     -moz-background-clip: padding;
          background-clip: padding-box;
}
</style>
@endsection

@section('javascript')
<script type="text/javascript">

$(function(){
  LoadData();

  @if(session('success'))
    setStatusMessage('success', 'Success', '{{ session("success") }}');
  @endif
});

function LoadData()
{
    $('#fileTable')
    .dataTable({
      "ajax": {
                  url: "{{ route('display_files') }}",
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
      "aoColumns" : [
        {},
        {},
        {},
        {}
      ]
    });
}

function ShowModalAddFile()
{
    removeMessageModal();

    $('.modal-title').html('Add File');
    $('.modal-body').html('\
                              <form id="form_upload_file" role="form" action="{{ route('upload_file') }}" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <label for="description" class="col-md-4 control-label">\
                                  File Description:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="text" class="form-control" name="description" id="description" required>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="file" class="col-md-4 control-label">\
                                  Select File:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" class="form-control" name="file" id="file" required>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Select the files</p>\
                                </div>\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary">Upload</button>\
                              \
                              </form>\
                          ');

    $('.modal-footer').html('\
                              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
                            ');

    $('#myModal').modal('show');
}

</script>
@endsection
