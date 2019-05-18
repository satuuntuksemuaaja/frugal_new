@extends('layouts.main', [
'title' => $customer->name,
'crumbs' => [
    ['url' => "/customers", 'text' => "Customer Manager"],
    ['text' => $customer->name]
]])
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#details" role="tab">Details</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#quotes" role="tab">Quotes (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#jobs" role="tab">Jobs (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#fft" role="tab">FFT (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#warranty" role="tab">Warranty (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#service" role="tab">Service (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tasks" role="tab">Tasks (0)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#notes" role="tab">Notes (0)</a>
                    </li>

                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane active p-3" id="details" role="tabpanel">
                        @include('customers.fields', ['customer' => $customer])

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
