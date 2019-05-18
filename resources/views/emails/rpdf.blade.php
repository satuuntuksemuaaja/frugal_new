<?php
use FK3\Models\Job;

$job = Job::find($job['id']);
?>
<center><img src="@php echo public_path(). '/logo.png'; @endphp" /></center>
    <h4>Customer Responsibilities</h4>
    <h5>{{$job->quote->lead->customer->name}}</h5>
    <p>Thank you for choosing Frugal Kitchens and Cabinets. Please review the items listed below.</p>
    <ul>
        @foreach($job->quote->responsibilities as $qr)
            <li>{{$qr->responsibility->name}}</li>
        @endforeach
    </ul>
