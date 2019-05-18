@extends('layouts.main', [
'title' => $hardware->name ?: "Create Hardware",
'crumbs' => [
    ['text' => "Hardware", 'url' => "/admin/hardware"],
    ['text' =>  $hardware->sku?: "Create Hardware"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card vendorBody">
                <div class="card-body">
                    @include('admin.hardware.fields', ['hardware' => $hardware])
                </div>
            </div>
        </div>
        @if ($hardware->id)
            <div class="col-lg-6">
                <div class="card vendorBody">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title">Hardware Image</h4>
                    </div>
                    <div class="card-body">
                        @if ($hardware->image)
                            <img
                                    src="/hardware_images/{{ $hardware->image }}"
                                    style="max-width:100%; max-height:100%"
                            >
                        @else
                            No image uploaded.
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
