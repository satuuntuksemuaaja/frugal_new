@extends('layouts.main', [
'title' => $cabinet->name ?: "Create Cabinet",
'crumbs' => [
    ['text' => "Cabinets", 'url' => "/admin/cabinets"],
    ['text' =>  $cabinet->name ?: "Create Cabinet"]
]])

@section('content')
        <div class="row">
            <div class="col-lg-6">
                <div class="card vendorBody">
                    <div class="card-body">
                        @include('admin.cabinets.fields', ['cabinet' => $cabinet])
                    </div>
                </div>
            </div>
            @if ($cabinet->id)
                <div class="col-lg-6">
                    <div class="card vendorBody">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">Cabinet Image</h5>
                        </div>
                        <div class="card-body">
                            @if ($cabinet->image)
                                <img
                                    src="{{ url('app') . '/' . $cabinet->image }}"
                                    style="max-width:100%; max-height:100%"
                                >
                            @else
                                No image uploaded.
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
