@extends('layouts.main', [
'title' => "Settings",
'crumbs' => [
    ['text' => "Settings"]
]])
@section('content')
        <div class="row">
            @foreach(\FK3\Plugins\PluginRegistry::$registry as $plugin => $data)
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title">Configure {{$data['name']}} </h4>
                        <a href="/admin/settings/create?plugin={{$plugin}}" class="btn btn-white btn-sm">
                            <i class="fa fa-plus"></i>
                        </a>
                    </div>
                    @include('admin.settings.list', ['plugin' => $plugin])
                </div>
            </div>
            @endforeach
        </div>
@endsection