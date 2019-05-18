@extends('layouts.main', [
'title' => "Settings",
'crumbs' => [
    ['text' => "Settings"]
]])
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title">New {{$plugin}} Setting</h4>
                    </div>
                    <div class="card-body">
                        @include('admin.settings.fields', ['plugin' => $plugin, 'setting' => $setting])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection