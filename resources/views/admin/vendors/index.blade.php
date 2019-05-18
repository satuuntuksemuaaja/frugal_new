@extends('layouts.main', [
'title' => "Frugal Vendors",
'crumbs' => [
    ['text' => "Vendors"],
]])
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <a href="/admin/vendors/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create
                        Vendor</a>
                    @include('admin.vendors.list', ['active' => true])
                </div>
            </div>
        </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Vendors</h5>
                    </div>
                    @include('admin.vendors.list', ['active' => false])
                </div>
            </div>
        </div>
@endsection