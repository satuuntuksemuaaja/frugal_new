@extends('layouts.main', [
'title' => $acl->action ?: "Create new Access Function",
'crumbs' => [
    ['url' => "/admin/acls", 'text' => "ACLs"],
    ['text' => $acl->action ?: "Create new Function"]
]])@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card groupBody">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title">{{$category->name}}  - {{$acl->action ?: "Create New ACL"}}</h5>
                    </div>
                    <div class="card-body">
                        @include('admin.acls.fields', ['acl' => $acl, 'category' => $category])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection