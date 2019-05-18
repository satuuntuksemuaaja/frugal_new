<?php
use FK3\Models\Quote;

$quote = Quote::find($quote['id']);
?>
Hi {{$quote->lead->customer->contacts()->first()->name}}, <br/>
<br/>
You have selected the following appliance items. In order to continue we must have the brand, model and size of each of
the appliances. We cannot continue processing your order until this is complete.
<br/><br/>
<a href="{{ route('quote_customer_appliances', ['id' => $quote->id]) }}">Please click here to enter your appliances.</a>
<br/>
<br/>
<b>Appliance List:</b>
<ul>
    @foreach ($quote->appliances as $appliance)
        <li>{{ $appliance->appliance->name }}</li>
        @endforeach
</ul>
