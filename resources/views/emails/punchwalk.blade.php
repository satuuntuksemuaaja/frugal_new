<?php
use FK3\Models\Fft;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Lead;
use FK3\Models\Quote;
use FK3\Models\Customer;

$fft = Fft::find($fft['id']);
$item = JobItem::find($item['id']);

$job = Job::find($fft->job_id);
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);

?>
There has been a punch item added to a job that has already been signed off on.
<br/><br/>
<b>Customer: </b>{{$customer->name}}<br/>
<b>Job ID:</b> {{$job->id}}
<hr/>
<b>Item Details:</b>
<br/><br/>
{{$item->reference}} - {{$item->orderable ? "Orderable Item" : "Item is not orderable"}} -
{{$item->replacement ? "This is a replacement item" : "Not a replacement item."}}
