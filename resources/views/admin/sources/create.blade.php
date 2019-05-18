@extends('layouts.main', [
'title' => $source->name ?: "Create New Lead Source",
'crumbs' => [
    ['text' => "Lead Sources", 'url' => "/admin/lead_sources"],
    ['text' =>  $source->name ?: "Create Lead Source"]
]])@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4">
                <div class="card sourceBody">
                    <div class="card-body">
                        @include('admin.sources.fields', ['source' => $source])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection