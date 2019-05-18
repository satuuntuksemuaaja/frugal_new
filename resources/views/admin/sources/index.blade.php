@extends('layouts.main', [
'title' => "Lead Sources",
'crumbs' => [
    ['text' => "Lead Sources"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                    <a href="/admin/lead_sources/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create Lead Source</a>
                    @include('admin.sources.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h4 class="card-title text-white">Inactive Sources</h4>
                    </div>
                    @include('admin.sources.list', ['active' => false])
                </div>
            </div>


    </div>
@endsection