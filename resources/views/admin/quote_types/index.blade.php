@extends('layouts.main', [
'title' => "Quote Types",
'crumbs' => [
    ['text' => "Quote Types"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title">Active Quote Types</h5>
                        <a href="/admin/quote_types/create" class="btn btn-white btn-sm">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        @include('admin.quote_types.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Quote Types</h5>
                    </div>
                    @include('admin.quote_types.list', ['active' => false])
                </div>
            </div>


    </div>
@endsection