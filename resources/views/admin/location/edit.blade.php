@extends('layouts.main', [
'title' => "Update Store",
'crumbs' => [
    ['text' => "Stores", 'url' => "/admin/stores"],
    ['text' =>  "Update"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card groupBody">
                    <div class="card-body">

                      <form id="form_update_location" role="form" action="{{ route('stores.update', ['id' => $location->id]) }}" method="post">
                      {{ csrf_field() }}

                      <input name="_method" type="hidden" value="PUT">

                      <div class="form-group form-row ">
                        <label for="name" class="col-md-4 control-label">
                          Name:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="name" type="text" id="name" value="{{ $location->name }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the name of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          Address:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="address" type="text" id="address" value="{{ $location->address }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the address of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="address" class="col-md-4 control-label">
                          City:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="city" type="text" id="city" value="{{ $location->city }}" required>
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the city of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="state" class="col-md-4 control-label">
                          State:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="state" type="text" id="state" value="{{ $location->state }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the state of the store</p>
                        </div>
                      </div>

                      <div class="form-group form-row ">
                        <label for="state" class="col-md-4 control-label">
                          Number:
                        </label>
                        <div class="col-md-8">
                            <input class="form-control" name="number" type="text" id="number" value="{{ $location->number }}">
                                <p class="help-block mt-1 text-muted" style="font-size: 12px;">Enter the number of the store</p>
                        </div>
                      </div>

                      <input class="btn btn-primary uiblock !important" data-message="Updating Store.." name="" type="submit" value="Update Store">

                      <input class="btn btn-danger uiblock !important" style="float:right;" data-message="Destroying Store.." name="" value="Destroy Store" onclick="ShowModalDeleteConfirm('{{ $location->id }}')">

                    </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
<script>
function ShowModalDeleteConfirm(location_id)
{
    $('.modal-title').html('Confirmation');
    $('.modal-body').html('<p><font color="red">Are you sure? This action cannot be undone.</font></p>');
    $('.modal-footer').html('<button class="btn btn-danger" onclick="DoDelete(' + location_id + ');">Yes</button><button class="btn btn-default" onclick="CloseModal();">Cancel</button>');
    $('#myModal').modal('show');
}

function DoDelete(location_id)
{
    window.location = '{{ url("admin/locations") }}/' + location_id + '/destroy';
}
</script>
@endsection
