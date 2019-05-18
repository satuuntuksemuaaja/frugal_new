@extends('layouts.main', [
'title' => "Countertops",
'crumbs' => [
    ['text' => "Countertops", 'url' => "/admin/countertops"],
]])
@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <a href="/admin/countertops/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create Countertop</a>
                         @include('admin.countertops.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Countertop Options</h5>
                    </div>

                    @include('admin.countertops.list', ['active' => false])
                </div>
            </div>


    </div>
@endsection
