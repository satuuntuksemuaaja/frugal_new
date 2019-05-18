@extends('layouts.main', [
'title' => $title,
'crumbs' => [
    ['text' => "Quotes", 'url' => "/quotes"],
    ['text' =>  "Start"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-12">
                <div class="card groupBody">
                    <div class="card-body">

                      <div class="form-group form-row ">
                          <p>
                            <b>
                              Need to redo a cabinet or XML? Simply click the trash can and delete the appropriate cabinet and click Add Cabinet to redo the cabinet entry.
                            </b>
                          </p>
                      </div>

                      <table class="table cabinetTable table-striped mt-2" id="cabinetTable">
                          <thead>
                          <th>Description</th>
                          <th>Cabinets</th>
                          <th>List Price</th>
                          <th>Color</th>
                          <th>In. Off Floor</th>
                          <th>Remove</th>
                          </thead>
                          <tbody></tbody>
                      </table>

                      <a href="#" class="btn btn-primary uiblock !important" onclick="ShowModalAddCabinet();"><i class="fa fa-plus"></i> Add Cabinet</a>

                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('javascript')
<script type="text/javascript">
$(function(){

});

function ShowModalAddCabinet()
{
    removeMessageModal();

    $('.modal-title').html('Add Cabinet to Quote');
    $('.modal-body').html('\
                              <form id="form_create_cabinet" role="form" action="{{ route('save_cabinet', ['id' => $quote->id]) }}" method="post" enctype="multipart/form-data">\
                              {{ csrf_field() }}\
                              \
                              <div class="form-group form-row ">\
                                <b>You are adding a cabinet to this quote. There are no longer primary and secondary cabinets. This system now supports unlimited cabinet orders.</b>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="xml" class="col-md-4 control-label">\
                                  Pro Kitchens XML:\
                                </label>\
                                <div class="col-md-8">\
                                  <input type="file" name="xml" id="xml">\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="cabinet_name:" class="col-md-4 control-label">\
                                  Cabinet Name:\
                                </label>\
                                <div class="col-md-8">\
                                    <input type="text" class="form-control" name="cabinet_name" id="cabinet_name">\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Only if there is no XML file.</p>\
                                </div>\
                              </div>\
                              \
                              <div class="form-group form-row ">\
                                <label for="cabinet_list:" class="col-md-4 control-label">\
                                  Cabinet List:\
                                </label>\
                                <div class="col-md-8">\
                                    <textarea class="form-control" name="cabinet_list" id="cabinet_list"></textarea>\
                                        <p class="help-block mt-1 text-muted" style="font-size: 12px;">Only if there is no XML file.</p>\
                                </div>\
                              </div>\
                              \
                              <button type="submit" class="btn btn-primary" onclick="SaveCabinet();">Upload</button>\
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
