@if(isset($t4))
    <h4>Your change order must be signed otherwise it will be closed!</h4>
@endif

<?php

use FK3\Models\Job;
use FK3\Models\Quote;
use FK3\Models\Lead;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\ChangeOrderDetail;

$job = Job::find($order->job_id);
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);
$contact = Contact::where('customer_id', $customer->id)->first();
$items = ChangeOrderDetail::where('change_order_id', $order->id)->get();
 ?>
Hi {{$customer->name}},
<br/><br/>
We have created a change order for your review. <b>This change order must be paid in full before any parts are ordered, or any work is performed.</b>
 The following items have been notated to be added to your job.<br/><br/>
<b>You must sign this change order in order to approve it. You can do so by
    <a href='{{ route("view_changes", ["id" => $order->id]) }}?show_sign=true'>clicking here</a></b>.
<br/><br/>
Change Order Details:
<br/><br/>
<table border='1' cellpadding='4'>
    <tr>
        <td align='center'><b>Item</b></td>
        <td align='center'><b>Price</b></td>
    </tr>
    @foreach ($items AS $item)
        <tr>
            <td>{{nl2br($item->description)}}</td>
            <td>${{number_format($item->price,2)}}</td>
        </tr>
    @endforeach

</table>
To see how this change order affects your final job price, please click the approval link listed above to review and sign.
<br/>
<br/>
If you have any questions please contact our office at 770.460.4331
<br/>
<br/>
Thank You,<br/>
Frugal Kitchens and Cabinets
