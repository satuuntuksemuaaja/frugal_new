@extends('layouts.main', [
'title' => "Questionnaire",
'crumbs' => [
    ['text' => "Questionnaire"],
]])
@section('content')
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <a href="/admin/questions/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create Question</a>
                        @include('admin.questions.list', ['active' => true])
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card ">
                    <div class="card-header bg-danger  d-flex align-items-center justify-content-between">
                        <h5 class="card-title text-white">Inactive Question</h5>
                    </div>
                    @include('admin.questions.list', ['active' => false])
                </div>
            </div>
    </div>
@endsection
