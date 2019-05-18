@extends('layouts.main', [
'title' => "Frugal Groups",
'crumbs' => [
    ['text' => "Groups"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-6">
                <a href="/admin/groups/create" class="btn btn-primary mb-2"><i class="fa fa-plus"></i> Create Group</a>
                <div class="card pt-4">
                    @include('admin.groups.list')
                </div>
            </div>
        </div>
@endsection