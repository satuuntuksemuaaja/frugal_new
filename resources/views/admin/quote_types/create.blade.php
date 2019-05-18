@extends('layouts.main', [
'title' => "Quote Types",
'crumbs' => [
    ['url' => "/admin/quote_types", 'text' => "Quote Types"],
    ['text' => $quote_type->name ?: "Create Quote Type"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-4">
                <div class="card groupBody">
                    <div class="card-body">
                        @include('admin.quote_types.fields', ['quote_type' => $quote_type])
                    </div>
                </div>
            </div>
            @if($quote_type->id)
            <div class="col-lg-8">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h4 class="card-title">{{$quote_type->name}} Contract</h4>
                </div>
                <div class="card-body">
                    @include('admin.quote_types.contract', ['quote_type' => $quote_type])
                </div>
            </div>
            @endif
        </div>
@endsection