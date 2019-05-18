@extends('layouts.main', [
'title' => $appliance->name ?: "Create Appliance",
'crumbs' => [
    ['text' => "Appliances", 'url' => "/admin/appliances"],
    ['text' =>  $appliance->name ?: "Create Appliance"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-body">
                        @include('admin.appliances.fields', ['appliance' => $appliance])
                    </div>
                </div>
            </div>
            @if ($appliance->id)
                @include('admin.partials.percentages', ['model' => $appliance])
            @endif
        </div>
    </div>
@endsection
