@extends('layouts.main', [
'title' => $user->name ?: "Create new User",
'crumbs' => [
    ['text' => "Users", 'url' => "/admin/users"],
    ['text' => $user->name ?: "New User"]
]])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        @include('admin.users.fields', ['user' => $user])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection