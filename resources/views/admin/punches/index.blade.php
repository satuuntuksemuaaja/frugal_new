@extends('layouts.main', [
'title' => "Punch List Questions",
'crumbs' => [
    ['text' => "Punch List Questions"],
]])
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <a href="/admin/punches/create" class="btn btn-primary mb-2">
                        <i class="fa fa-plus"></i> Create Punch Question
                    </a>
                    @include('admin.punches.list', ['active' => true])
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card ">
                <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                    <h5 class="card-title text-white">Inactive Punch List Questions</h5>
                </div>
                @include('admin.punches.list', ['active' => false])
            </div>
        </div>
    </div>
@endsection
