@extends('layouts.main', [
'title' => "Addons",
'crumbs' => [
    ['text' => "Addons"],
]])
@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <a href="/admin/addons/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create Addon</a>
                        @include('admin.addons.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Addons</h5>
                    </div>
                    @include('admin.addons.list', ['active' => false])
                </div>
            </div>
        </div>
@endsection
