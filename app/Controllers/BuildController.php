<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Accessory;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationItem;
use FK3\Models\AuthorizationList;
use FK3\Models\BuildupNote;
use FK3\Models\Cabinet;
use FK3\Models\Checklist;
use FK3\Models\Lead;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Fft;
use FK3\Models\FftNote;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteAppliance;
use FK3\Models\Question;
use FK3\Models\QuestionAnswer;
use FK3\Models\Shop;
use FK3\Models\ShopCabinet;
use FK3\Models\Hardware;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\JobNote;
use FK3\Models\JobSchedule;
use FK3\Models\Po;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\Group;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;
use Mail;

class BuildController extends Controller
{
    public $auditPage = "Buildup";

    /*
     * Show Receiving Index.
     */
    public function index(Request $request)
    {
        $jobs = Job::join('quotes', 'jobs.quote_id', '=', 'quotes.id')
                    ->join('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                    ->join('leads', 'quotes.lead_id', '=', 'leads.id')
                    ->join('customers', 'leads.customer_id', '=', 'customers.id')
                    ->select(
                              'jobs.*',
                              'quotes.lead_id',
                              'quote_types.name as quoteTypeName',
                              'customers.name as customerName'
                            )
                    ->get();

        $jobOpt = '';
        foreach($jobs as $job)
        {
            $jobOpt .= '<option value="' . $job->id . '">' . htmlspecialchars($job->customerName, ENT_QUOTES) . ' (' . $job->quoteTypeName . ' - ' . $job->id . ')</option>';
        }

        return view('buildup.index', compact('jobOpt'));
    }

    /**
     * Render progress items.
     * @param $item
     * @return string
     */
    public function renderProgress($item)
    {
        $user = Auth::user();
        // Only allow frugal orders to be able to approve.
        // We have approved - started and completed
        $icons = null;

        // Not approved
        if (!$item->approved && ($user->group_id == 12 || $user->id == 1 || $user->id == 5))
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' title='Not Approved' href='/shopitem/$item->id/approved'><i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
        }
        // Approved
        if ($item->approved)
        {
            $icons .= "<a class='label label-success tooltiped' data-toggle='tooltip' data-target='#workModal'
                      data-original-title='Approved' href='#'><i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
        }

        if ($item->approved && !$item->started) // Approved and not started
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' title='Not Started' href='/shopitem/$item->id/started'><i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
        }
        if ($item->approved && $item->started)
        {
            $icons .= "<a class='btn btn-success' data-toggle='tooltip' title='Started' href='#'><i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
        }

        if ($item->approved && !$item->completed)
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' title='Not Completed' href='/shopitem/$item->id/completed'><i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
        }
        if ($item->approved && $item->completed)
        {
            $icons .= "<a class='btn btn-success tooltiped' data-toggle='tooltip' title='Completed' href='#'><i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
        }

        return $icons;

    }

    public function displayJobSold(Request $request)
    {
        $jobs = Job::join('quotes', 'jobs.quote_id', '=', 'quotes.id')
                    ->join('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                    ->join('leads', 'quotes.lead_id', '=', 'leads.id')
                    ->join('customers', 'leads.customer_id', '=', 'customers.id')
                    ->select(
                              'jobs.*',
                              'quotes.id as quoteId',
                              'quotes.lead_id',
                              'quote_types.name as quoteTypeName',
                              'customers.id as customerId',
                              'customers.name as customerName'
                            )
                    ->where('jobs.start_date', '!=', '0000-00-00')
                    ->orWhere('jobs.start_date', '!=', null)
                    ->orderBy('jobs.start_date', 'ASC')
                    ->get();

        if ( !empty( $jobs ) )
        {
            $total = 0;
            $newItems = array();
            foreach ( $jobs as $job )
            {
              $objItems = array();

              if ($job->built && $job->loaded) continue;

              $start = Carbon::parse($job->start_date);
              $cabinets = null;
              $quoteCabinets = QuoteCabinet::leftJoin('cabinets', 'quote_cabinets.cabinet_id', '=', 'cabinets.id')
                                            ->leftJoin('vendors', 'cabinets.vendor_id', '=', 'vendors.id')
                                            ->select(
                                                      'quote_cabinets.*',
                                                      'cabinets.name as cabinetName',
                                                      'vendors.name as vendorName'
                                                    )
                                            ->where('quote_id', $job->quoteId)
                                            ->get();

              foreach ($quoteCabinets AS $cabinet)
              {

                  $cabinets[] = "<b>". $cabinet->cabinetName . "</b> ({$cabinet->vendorName})";
              }
              $cabinets = implode(", ", $cabinets);
              $buildColor = $start->diffInDays() <= 4 ? 'danger' : 'info';
              $loadColor = $start->diffInDays() <= 2 ? 'danger' : 'info';

              $buildControl = '<a href="' . route('buildup_job_build', ['id' => $job->id]) . '" class="btn btn-' . $buildColor . ' btn-sm"><i class="fa fa-arrow-up"></i> Build</a>';
              $leftControl = '<a href="' . route('buildup_job_left', ['id' => $job->id]) . '" class="btn btn-warning btn-sm"><i class="fa fa-arrow-right"></i> Loaded Awaiting Departure</a>';
              $loadControl = '<a href="' . route('buildup_job_load', ['id' => $job->id]) . '" class="btn btn-' . $loadColor . ' btn-sm"><i class="fa fa-truck"></i> Load on Truck</a>';

              if ($job->loaded && !$job->truck_left)
              {
                  $load = $leftControl;
              }
              elseif (!$job->loaded)
              {
                  $load = $loadControl;
              }
              else
              {
                  $load = "<a class='btn btn-success'><i class='fa fa-check'></i></a>";
              }
              $notes = "<span class='pull-right'><a href='#' data-toggle='tooltip' data-placement='left'
                          title='Add Note' data-toggle='tooltip'
                          href='#' onclick='ShowModalBuildNote(" . $job->id . ")'><i class='fa fa-plus'></i></a></span>";

              $buildupNotes = BuildupNote::leftJoin('users', 'buildup_notes.user_id', '=', 'users.id')
                                          ->select(
                                                    'buildup_notes.*',
                                                    'users.name as userName'
                                                  )
                                          ->where('buildup_notes.job_id', $job->id)
                                          ->get();

              foreach ($buildupNotes AS $note)
              {
                  $notes .= "<b>". $note->created_at->format("m/d/y h:i a") . ' ' . $note->userName . ' - </b> ' . nl2br($note->note). "<br/><br/>";
              }
              $drawings = "<a href='#' data-toggle='tooltip' data-placement='right'
                          title='Drawings'
                          onclick='ShowModalDrawing(" . $job->quoteId . ")'><i class='fa fa-image'></i></a>";

              $objItems[] = '[' . $job->customerId . '] ' . $job->customerName . '<span class="pull-right">' . $drawings . '</span>';
              $objItems[] = $job->created_at->format("m/d/y");
              $objItems[] = $start->format('m/d/y');
              $objItems[] = $cabinets;
              $objItems[] = $job->built ? "Yes" : $buildControl;
              $objItems[] = $load;
              $objItems[] = $notes;

              $newItems[] = $objItems;
              $total++;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function build($id, Request $request)
    {
        $job = Job::find($id);
        $job->built = 1;
        $job->save();

        // Now mail it to chris@vocalogic.com
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);

        Mail::send('emails.built', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[BUILD] Build Complete for $customer->name");
        });

        return redirect()->back()->with('success', 'Build Set.');
    }

    public function load($id, Request $request)
    {
        $job = Job::find($id);
        $job->loaded = 1;
        $job->save();

        // Now mail it to chris@vocalogic.com
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);

        Mail::send('emails.loaded', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[LOAD] Truck Loading Complete for $customer->name");
        });

        return redirect()->back()->with('success', 'Load Set.');
    }

    public function left($id, Request $request)
    {
        $job = Job::find($id);
        $job->truck_left = 1;
        $job->save();

        // Now mail it to chris@vocalogic.com
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);

        Mail::send('emails.loaded', ['customer' => $customer], function ($message) use ($customer)
        {
            $message->to([
                'schedules@frugalkitchens.com' => 'Schedules'
            ])->subject("[TRUCK LEFT] Truck has left for $customer->name");
        });

        return redirect()->back()->with('success', 'Left Set.');
    }

    public function uploadFile($id, Request $request)
    {
        $fileName = $request->file('file')->getClientOriginalName();
        $filePath = $request->file('file')->store('quote_files');

        $file = new File();
        $file->location = $filePath;
        $file->description = $request->description;
        $file->user_id = Auth::user()->id;
        $file->quote_id = $id;
        $file->save();

        return redirect(route('buildup') . '?upload_file=1&quote_id=' . $id);
    }

    public function SaveNote(Request $request)
    {
        $job_id = $request->job_id;
        $note = $request->note;

        if($note == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Note cannot be empty.'
            ]
          );
        }

        $buildupNote = new BuildupNote();
        $buildupNote->job_id = $job_id;
        $buildupNote->note = $note;
        $buildupNote->user_id = Auth::user()->id;
        $buildupNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Note added.'
          ]
        );

    }

    public function displayJobCabinet(Request $request)
    {
        $shops = Shop::where('active', '1')
                      ->get();

        if ( !empty( $shops ) )
        {
            $total = 0;
            $newItems = array();
            foreach ( $shops as $shop )
            {
              $objItems = array();

              $job = Job::find($shop->job_id);
              if (!$job)
              {
                  $shop->delete();
                  continue;
              }

              $jobItem = JobItem::find($shop->job_item_id);
              if ($shop->job_item_id && !$jobItem)
              {
                  $shop->delete();
              }
              $punch = $shop->job_item_id ? "<small>From Punch Item: {$jobItem->reference}</small>" : null;

              $quote = Quote::find($job->quote_id);
              $lead = Lead::find($quote->lead_id);
              $customer = Customer::find($lead->customer_id);

              $objItems[] = "<span class='text-info'>" . $customer->name . "<br/><small>". $shop->created_at->format("m/d/y")."</small></span>";
              $objItems[] = null;
              $objItems[] = "<span class='text-info'>" . $punch . "</span>";
              $objItems[] = null;
              $objItems[] = 'blue';
              $objItems[] = $shop->id;

              $newItems[] = $objItems;
              $total++;

              $shopCabinets = ShopCabinet::where('shop_id', $shop->id)->get();
              foreach ($shopCabinets AS $shopCabinet)
              {
                  $status = "<span class='text-danger'>Pending Approval</span>";
                  if ($shopCabinet->approved)
                      $status = "<span class='text-info'>Approved</span>";
                  if ($shopCabinet->started)
                      $status = "<span class='text-success'>Started Work</span>";
                  if ($shopCabinet->complete)
                      $status = "<span class='text-success'><b>Completed</b></span>";

                  $quoteCabinet = QuoteCabinet::find($shopCabinet->quote_cabinet_id);
                  if(!$quoteCabinet) continue;
                  $cabinet = Cabinet::find($quoteCabinet->cabinet_id);

                  $objItems = array();

                  $objItems[] = null;
                  $cabinetCol = '';
                  if($cabinet->color) $cabinetCol = '/' . $cabinet_color;
                  $objItems[] = $cabinet->name . $cabinetCol;
                  $objItems[] = $shopCabinet->notes ? '<a href="#" onclick="ShowModalCabinetNotes(' . $shopCabinet->id . ')">' . $shopCabinet->notes . '</a>' : '<a href="#" onclick="ShowModalCabinetNotes(' . $shopCabinet->id . ')">No Notes Found</a>';
                  $objItems[] = $this->renderProgress($shopCabinet);

                  $newItems[] = $objItems;
                  $total++;
              }
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function getCabinetNotes(Request $request)
    {
        $shop_cabinet_id = $request->shop_cabinet_id;

        $shopCabinet = ShopCabinet::find($shop_cabinet_id);
        if(!$shopCabinet)
        {
          return Response::json(
            [
              'response' => 'success',
              'notes' => ''
            ]
          );
        }
        return Response::json(
          [
            'response' => 'success',
            'notes' => $shopCabinet->notes
          ]
        );
    }

    public function saveCabinetNotes(Request $request)
    {
        $shop_cabinet_id = $request->shop_cabinet_id;
        $notes = $request->notes;

        $shopCabinet = ShopCabinet::find($shop_cabinet_id);
        if(!$shopCabinet)
        {
          return Response::json(
            [
              'response' => 'error',
              'notes' => 'No Shop Cabinet found'
            ]
          );
        }
        $shopCabinet->notes = $notes;
        $shopCabinet->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Shop Cabinet Notes Set.'
          ]
        );
    }


}
