<?php

use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Quote;
use FK3\Models\Lead;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\ChangeOrderDetail;
use Carbon\Carbon;

$job = Job::find($fft->job_id);
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);
$contact = Contact::where('customer_id', $customer->id)->first();
$items = JobItem::where('instanceof', 'FFT')->where('job_id', $job->id)->get();

$type = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "Frugal Final Touch");
 ?>
Hi {{$customer->name}},
<br/><br/>
We have created a {{ $type }} for your review. <b>You must sign this {{ $type }} in order to approve it. You can do so by
    <a href='{{ route("signature_fft", ["id" => $fft->id]) }}'>clicking here</a></b>.
<br/><br/>
Job Item Details:
<br/><br/>
<table border='1' cellpadding='4'>
    <tr>
        <td align='center'><b>Item</b></td>
        <td align='center'><b>Found On</b></td>
    </tr>
    @foreach ($items AS $item)
        <tr>
            <td>{{ $item->reference }}</td>
            <td>{{ Carbon::parse($item->created_at)->format('Y-m-d h:i:s') }}</td>
        </tr>
    @endforeach

</table>
To see how this {{ $type }} affects your final job items, please click the approval link listed above to review and sign.
<br/>
<br/>
If you have any questions please contact our office at 770.460.4331
<br/>
<br/>
Thank You,<br/>
Frugal Kitchens and Cabinets
