@extends('layouts.main', [
'title' => "Punch List Questions",
'crumbs' => [
    ['text' => "Punch List Questions", 'url' => "/admin/punches"],
    ['text' => $punch->name ?: "Create Punch"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    @include('admin.punches.fields', ['punch' => $punch])
                </div>
            </div>
        </div>
    </div>
@endsection
