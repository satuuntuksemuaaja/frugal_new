@extends('layouts.main', [
'title' => $countertop->name ?: "Create Countertop",
'crumbs' => [
    ['text' => "Countertops", 'url' => "/admin/countertops"],
    ['text' =>  $countertop->name ?: "Create Countertop"]
]])
@section('content')
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-body">
                        @include('admin.countertops.fields', ['countertop' => $countertop])
                    </div>
                </div>
            </div>
        </div>
@endsection
