<?php
use Carbon\Carbon;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Lead;
use FK3\Models\Quote;
use FK3\Models\Customer;
use vl\core\Signature;

$type = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "Frugal Final Touch");
$instance = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "FFT");
$pre = '';
if (!isset($raw))
{
  //echo BS::title("Customer Signature", "Confirm Punch Items Completed for Contract");
}
if ($fft->warranty == '1')
    $pre = "<h1>Frugal Kitchens Warranty Repair</h1>";
else if ($fft->warranty == '1')
    $pre = "<h1>Frugal Kitchens Service Repair</h1>";
else
    $pre = "<h1>Frugal's Final Touch....For Your Peace of Mind</h1>
<h2>Punch List Signoff Confirmation</h2>";

$job = Job::find($fft->job_id);
$quote = Quote::find($job->quote_id);
$lead = Lead::find($quote->lead_id);
$customer = Customer::find($lead->customer_id);

$pre .= "
<p class='lead'>I, {$customer->name}, hereby confirm that as of ".
Carbon::now()->format("m/d/y h:i a")." the items listed below have been completed to my satisfaction. </p>";


if ($fft->signoff)
  {
    $w = 598;
    $h = 155;
    $link = (!isset($raw)) ? "You can download the <a href='/fft/{$fft->id}/signoff/pdf'>pdf here</a>" : null;
    if (!isset($raw))
    $pre .= BS::callout('info', "<b>Signature Found</b> A signature was found for this {$type} signoff and was
    signed by {$customer->name}  on " . Carbon::parse($fft->signoff_stamp)->format('m/d/y h:i a') . ". If
    additional items were found and are to be completed under the initial agreement, the customer can sign again and the
    items to this date will be added to the contract. {$link}");
    $img = $fft->signoff_img;
    //$img = Signature::sigJsonToImage($fft->signoff,array('imageSize'=>array($w, $h),'bgColour' => 'transparent'));
    ob_start();
    //imagepng($img);
    //imagedestroy($img);
    //$img=base64_encode(ob_get_clean());
    $pre .= '<div style="width:475px;">
           <p class="drawItDesc" style="display: block;">Signed By:</p>
            <img src="'.$img.'" />
            <p style="border-top:1px solid gray; padding-top:10px; text-align:center;">'.$customer->name.'</p>
            </div>';

//
//


  }
$headers = ['Item',  'Found On'];
$rows = [];
$tableData = '';
$data = '';

$jobItems = JobItem::where('job_id', $job->id)
                    ->where('instanceof', $instance)
                    ->get();

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
<form method="post" action="/fft/'.$fft->id.'/signoff" class="sigPad">
  <p class="drawItDesc">Draw signature below</p>
  <ul class="sigNav" style="list-style:none;">

    <li class="clearButton"><a href="#clear">Clear</a></li>
  <div class="sig sigWrapper">
    <div class="typed"></div>
    <canvas class="pad" width="598" height="155"></canvas>
    <input type="hidden" name="output" class="output">
  </div>
  <button type="submit">I accept the terms of this agreement.</button>


</form>
</center>
';
if (Auth::user())
  $sig .= '<br/><br/><Br/>
<a class="btn btn-primary" href="/fft/'.$fft->id.'/punch/send">Resend Punch Confirmation To Customer</a>
<a href="/fft/'.$fft->id.'/signoff/pdf" class="btn btn-info">Download PDF</a>';

if (isset($raw)) $sig = null;
$span = BS::span(8, $pre.$table.$sig, 2);
echo BS::row($span);

echo BS::encap("$('.sigPad').signaturePad({drawOnly:true});
$.fn.signaturePad.clear = '.clearButton';
$('.responsive-admin-menu').toggleClass('sidebar-toggle');
$('.content-wrapper').toggleClass('main-content-toggle-left');
  "); */
