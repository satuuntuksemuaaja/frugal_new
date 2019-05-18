@extends('layouts.main', [
'title' => "Access Control Lists",
'crumbs' => [
    ['text' => "ACLs"]
]])
@section('content')
        <div class="row">
            @foreach(\FK3\Models\AclCategory::all() as $category)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="card-title">{{$category->name}}</h5>
                        <a href="/admin/acls/create?category={{$category->id}}" class="btn btn-white btn-sm">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                    @include('admin.acls.list', ['category' => $category])
                </div>
            </div>
            @endforeach
        </div>
@endsection