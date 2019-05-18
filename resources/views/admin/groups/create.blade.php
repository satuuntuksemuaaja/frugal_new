@extends('layouts.main', [
'title' => $group->name ?: "Create new Group",
'crumbs' => [
    ['url' => "/admin/groups", 'text' => "Groups"],
    ['text' => $group->name ?: "Create New Group"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-body">
                        @include('admin.groups.fields', ['group' => $group])
                    </div>
                </div>
            </div>
            @if($group->id)
            <div class="col-lg-8">
                {!! Form::open(['ajax' => true, 'url' => "/admin/groups/$group->id?acls=true", 'method' => "PUT"]) !!}
                @foreach (\FK3\Models\AclCategory::all() as $category)
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">{{$category->name}}</h5>
                        </div>
                        @include('admin.groups.selector', ['category' => $category, 'group' => $group])
                    </div>
                @endforeach
                <input type="submit" class="btn btn-primary mt-2" value="Save Access Control">
                {!! Form::close() !!}
            </div>
            @endif
        </div>
@endsection