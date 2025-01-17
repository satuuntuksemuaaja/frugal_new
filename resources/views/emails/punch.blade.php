<?php
use FK3\Models\Fft;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Lead;
use FK3\Models\Quote;
use FK3\Models\Customer;

$fft = Fft::find($fft['id']);

$job = Job::find($fft->job_id);
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);

?>
Hi {{$customer->name}},
<br/><br/>
We have created a punch list for your review. The following items have been notated to be completed as part of our punch list.<br/><br/>
<b>This punch list order must be signed and approved before work is started. You can do so by
    <a href='http://www.frugalk.com/punch/{{$fft->id}}/job/{{$fft->job->id}}/sign'>clicking here</a></b>.
<br/><br/>
Punch List Details:
<br/><br/>
<table border='1' cellpadding='4'>
    <tr>
        <td align='center'><b>Item</b></td>
        <td align='center'><b>Added</b></td>
    </tr>
    @foreach (JobItem::where('job_id', $job->id)->where("instanceof", "FFT")->get() AS $item)
        <tr>
            <td>{{nl2br($item->reference)}}</td>
            <td>{{$item->created_at->format("m/d/y")}}</td>
        </tr>
    @endforeach

</table>
<br/><br/>
If you have any questions please contact our office at 770.460.4331
<br/>
<br/>
Thank You,<br/>
Frugal Kitchens and Cabinets
