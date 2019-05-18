@extends('layouts.main', [
'title' => $customer->id ?: "Create Customer",
'crumbs' => [
    ['url' => '/customers', 'text' => "Customer Manager"],
    ['text' => $customer->id ?: "Create Customer"]
]])
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body customerBody">
                    @include('customers.fields', ['customer' => $customer])
                </div>
            </div>
        </div>
    </div>
@endsection
