@extends('layouts.main', [
'title' => $vendor->name ?: "Create Vendor",
'crumbs' => [
    ['text' => "Vendors", 'url' => "/admin/vendors"],
    ['text' =>  $vendor->name ?: "Create Appliance"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-body">
                        @include('admin.vendors.fields', ['vendor' => $vendor])
                    </div>
                </div>
            </div>
        </div>

@endsection