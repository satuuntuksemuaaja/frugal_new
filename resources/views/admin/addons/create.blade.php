@extends('layouts.main', [
'title' => $addon->item ?: "Create Addon",
'crumbs' => [
    ['text' => "Addons", 'url' => "/admin/addons"],
    ['text' =>  $addon->item ?: "Create Addon"]
]])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card vendorBody">
                    <div class="card-body">
                        @include('admin.addons.fields', ['addon' => $addon])
                    </div>
                </div>
            </div>
            @if ($addon->id)
                @include('admin.partials.percentages', ['model' => $addon])
            @endif
        </div>
    </div>
@endsection

@section('javascript')
<script>
$(function(){
    @if($addon && $addon->automatic)
      $('#automatic').prop('checked', true);
    @else
      $('#automatic').prop('checked', false);
    @endif
});
function SetAutomatic()
{
    if($('#automatic').is(':checked'))
    {
        $('#automatic').prop('checked', true);
        $('#automatic').val('1');
    }
    else
    {
      $('#automatic').prop('checked', false);
      $('#automatic').val('0');
    }
}
</script>
@endsection
