<?php
use Carbon\Carbon;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Lead;
use FK3\Models\Quote;
use FK3\Models\Customer;

$type = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "Frugal Inspection");
$instance = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "FFT");

$data = '';
if (!isset($raw))
{
    //echo BS::title("Customer Signature", "Confirm Punch Items for Contract");
}

$job = Job::find($fft->job_id);
$jobItems = JobItem::where('instanceof', $instance)
                    ->where('job_id', $job->id)
                    ->get();
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);

if ($fft->warranty)
{
    $pre = "<h1>Frugal Kitchens Warranty Repair</h1>

<p class='lead'>I, {$customer->name}, hereby confirm that as of " .
        Carbon::now()->format("m/d/y h:i a") . " the items listed below have been agreed upon to be addressed by
        Frugal Kitchens and Cabinets. As soon as the replacements parts are received we will call you to set up a time
        for someone to come out and replace them.
";
}
else if ($fft->service)
{
    $pre = "<h1>Frugal Kitchens Service Repair</h1>

<p class='lead'>I, {$customer->name}, hereby confirm that as of " .
        Carbon::now()->format("m/d/y h:i a") . " the items listed below have been agreed upon to be addressed by
        Frugal Kitchens and Cabinets. As soon as the replacements parts are received we will call you to set up a time
        for someone to come out and replace them.
";
}
else
{
    $pre = "<h1>Frugal's Final Touch....For Your Peace of Mind</h1>
<p class='lead'>I, {$customer->name}, hereby confirm that as of " .
        Carbon::now()->format("m/d/y h:i a") . " the items listed below have been agreed upon to be addressed by Frugal Kitchens
and Cabinets. These items could take up to 4-6 weeks to arrive at our warehouse. Frugal Kitchens will call you as soon as
your items have been received to complete your project. Work will be completed once all parts have been received.
By signing this agreement, I understand if additional items are requested after signing that additional costs
may be incurred at the customer's expense. </p>

<p class='lead'>The following items have been listed to be addressed and all items noted were
found in a 'best effort' estimation by Frugal Kitchens and Cabinets in an effort to complete this job in an exemplary manner.
</p>
";
}
if ($fft->hours)
{
    $pre .= "<p class='lead'><b>Estimation Notice</b>: The hours listed below are for scheduling purposes only. The
completion time is only an estimate based on an average length of time that is normally required to complete your Final Touch Items.
</p>
<p class='lead'>
<b>Estimated Hours to Complete Listed Items: </b> {$fft->hours} Hours</p>
";
}

if ($fft->signature)
{
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/fft/{$fft->id}/signature/pdf'>pdf here</a>" : null;
    if (!isset($raw))
    {
        $pre .= BS::callout('info', "<b>Signature Found</b> A signature was found for this {$type} item and was
    signed by {$customer->name} on " . Carbon::parse($fft->signed)->format('m/d/y h:i a') . ". If
    additional items were found and are to be completed under the initial agreement, the customer can sign again and the
    items to this date will be added to the contract. {$link}");
    }
    $img = $fft->signature_img;
    //Signature::sigJsonToImage($fft->signature, array('imageSize' => array($w, $h), 'bgColour' => 'transparent'));
    ob_start();
    //imagepng($img);
    //imagedestroy($img);
    //$img = base64_encode(ob_get_clean());
    $pre .= '<div style="width:475px;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="' . $img . '" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">' . $customer->name . '</p>
            </div>';

//
//


}
$headers = ['Item', 'Found On'];
$rows = [];
$tableData = '';
foreach ($jobItems AS $item)
{
    $rows[] = [$item->reference, $item->created_at->format("m/d/y h:i a")];
    $tableData .= '<tr>\
                    <td>' . $item->reference . '</td>\
                    <td>' . $item->created_at->format("m/d/y h:i a") . '</td>\
                  </tr>
                  ';
}

$table = '<table id="" class="table table-striped table-bordered table-hover" width="100%">
            <thead>
              <tr>
                <th style="height: 30px">Item</th>
                <th style="height: 30px">Found On</th>
              </tr>
            </thead>
            <tbody>
              ' . $tableData . '
            </tbody>
          </table>';

$data .= $pre . $table;
echo $data;

/* $table = Table::init()->headers($headers)->rows($rows)->width(100)->render();
$sig = '
<center>
<form method="post" action="/fft/' . $fft->id . '/signature" class="sigPad">
  <p class="drawItDesc">Draw signature below</p>
  <ul class="sigNav" style="list-style:none;">

    <li class="clearButton"><a href="#clear">Clear</a></li>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="598" height="155"></canvas>
    <input type="hidden" name="output" class="output">
  </div>
  <button type="submit">I accept the terms of this agreement.</button>


</form></center>
';
if (isset($raw)) $sig = null;
$span = BS::span(8, $pre . $table . $sig, 2);
echo BS::row($span);

echo BS::encap("$('.sigPad').signaturePad({drawOnly:true});
$.fn.signaturePad.clear = '.clearButton';
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  "); */
