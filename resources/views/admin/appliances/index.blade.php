@extends('layouts.main', [
'title' => "Appliances",
'crumbs' => [
    ['text' => "Appliances"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body ">
                        <a href="/admin/appliances/create" class="mb-2 btn btn-primary">
                            <i class="fa fa-plus"></i> Create New Appliance
                        </a>
                        @include('admin.appliances.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Appliances</h5>
                    </div>
                    @include('admin.appliances.list', ['active' => false])
                </div>
            </div>
        </div>
@endsection