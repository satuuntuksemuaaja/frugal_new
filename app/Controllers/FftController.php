<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Accessory;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationItem;
use FK3\Models\AuthorizationList;
use FK3\Models\Checklist;
use FK3\Models\Lead;
use FK3\Models\User;
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
use FK3\Models\Cabinet;
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
use PDF;
use Response;
use Storage;
use Auth;
use App;
use Mail;

class FftController extends Controller
{
    public $auditPage = "Final Touch";

    /*
     * Show Final Touch Index.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('name')->where('active', 1)->get();
        $visitUsers = User::orderBy('name')->where('active', 1)->get();
        $punchUsers = User::orderBy('name')->where('group_id', 5)->where('active', 1)->get();
        $warranty = 0;
        $title = 'Final Touch';
        $paymentCategories = Fft::getPaymentCategories();

        return view('fft.index', compact('users', 'warranty', 'title', 'visitUsers', 'punchUsers', 'paymentCategories'));
    }

    public function displayFfts(Request $request)
    {
        if ($request->has('service'))
        {
            $items = Fft::where('service', $request->service);
        }
        else
        {
            $items = Fft::where('warranty', $request->warranty);
        }

        if($request->all == 'false' || $request->all == 'opened')
        {
              $items = $items->where('closed', '0');
        }
        else if($request->all == 'closed')
        {
              $items = $items->where('closed', '1');
        }

        // Get Total
        $total = $items->count();
        /* $items = $items->skip($request->start)
                        ->take($request->length); */
        $items = $items->get();

        $newItems = array();
        if ( !empty( $items ) )
        {
            foreach ( $items as $item )
            {
              $objItems = array();

              $job = Job::find($item->job_id);
              if(!$job) continue;
              $quote = Quote::find($job->quote_id);
              if(!$quote) continue;
              $lead = Lead::find($quote->lead_id);
              if(!$lead) continue;
              $customer = Customer::find($lead->customer_id);
              if(!$customer) continue;

              $customerColumn = $customer ? '<a href="' . route('view_profile', ['id' => $customer->id]) . '">' . $customer->name . ' (' . $customer->id . ')</a>' : '--No Customer--';
              $customerColumn .= '<br/>';
              $customerColumn .= '<a href="' . route('shop_fft', ['id' => $item->id]) . '" data-toggle="tooltip" title="Create Shop Work"><i class="fa fa-wrench"></i></a>';
              $customerColumn .= '&nbsp;';
              $customerColumn .= '<a href="' . route('quote_view', ['id' => $quote->id]) . '" data-toggle="tooltip" title="View Quote"><i class="fa fa-search"></i></a>';
              $customerColumn .= '&nbsp;';

              $openClose = "Close";
              $trash = "trash";
              if($item->closed == '1')
              {
                 $openClose = "Open";
                 $trash = "folder-open";
              }

              $customerColumn .= '<a href="' . route('close_fft', ['id' => $item->id]) . '" data-toggle="tooltip" title="' . $openClose . ' FFT/Warranty"><i class="fa fa-' . $trash . '"></i></a>';
              $customerColumn .= '&nbsp;';
              $customerColumn .= '<a href="#" data-toggle="tooltip" title="Add Task" onclick="ShowModalAddTask(' . $job->id . ')"><i class="fa fa-openid"></i></a>';
              $customerColumn .= '&nbsp;';
              $customerColumn .= '<a href="#" data-toggle="tooltip" title="Job Notes" onclick="ShowModalNotes(' . $job->id . ')"><i class="fa fa-check-square-o"></i></a>';
              $customerColumn .= '&nbsp;';

              $signatureTitle = 'Signature Found!';
              $sendFftSign = '';
              if(!$item->signature)
              {
                  $signatureTitle = 'No Signature Found!';
                  $sendFftSign = '&nbsp;<a href="' . route('fft_signature_send', ['id' => $item->id]) . '"><i class="fa fa-send"></i></a>';
              }
              else if($item->signature == '') $signatureTitle = 'No Signature Found!';
              $customerColumn .= '<a href="'. route('signature_fft', ['id'=> $item->id]) . '" data-toggle="tooltip" title="' . $signatureTitle . '"><i class="fa fa-edit"></i></a>' . $sendFftSign;

              $objItems[] = $customerColumn;
              $objItems[] = Carbon::parse($job->start_date)->format('m/d/y');
              $objItems[] = $item->hours ? $item->hours : '0';

              $userVisit = User::find($item->pre_assigned);
              $objItems[] = '<a href="#" onclick="ShowModalSetVisitAssigned(' . $item->id . ', \'' . $item->pre_assigned . '\')">' . ($userVisit ? $userVisit->name : '--No User--') . '</a>';

              $presheduledText = ($item->pre_schedule_start != '0000-00-00 00:00:00') ? $presheduledText = Carbon::parse($item->pre_schedule_start)->format('m/d/y h:i a') : "--No Schedule Set--";
              $objItems[] = '<a href="#" onclick="ShowModalSetPreSchedule(' . $item->id . ')">' . $presheduledText . '</a>';

              $userAssigned = User::find($item->user_id);
              $objItems[] = '<a href="#" onclick="ShowModalSetPunchAssigned(' . $item->id . ', \'' . $item->user_id . '\')">' . ($userAssigned ? $userAssigned->name : "Unassigned") . '</a>';

              $scheduleText = ($item->schedule_start != '0000-00-00 00:00:00') ? $scheduleText = Carbon::parse($item->schedule_start)->format('m/d/y h:i a') : "--No Schedule Set--";
              $objItems[] = '<a href="#" onclick="ShowModalSetScheduleStart(' . $item->id . ')">' . $scheduleText . '</a>';

              $buttonComment = '<a href="' . route('job_auth', ['id' => $item->job_id]) . '" data-toggle="tooltip" data-placement="left" title="Customer Authorizations" class="btn btn-success"><i class="fa fa-comment"></i></a>';

              $objItems[] = $this->getPunchesIndex($item, false, $request) . '&nbsp;' . $buttonComment;

              $fftNotesCount = FftNote::where('fft_id', $item->id)->count();
              $objItems[] = '<a href="#" class="btn btn-primary" onclick="ShowModalFftNotes(' . $item->id . ');">Payment Notes (' . $fftNotesCount . ')</a>';

              // Make a popover with the schedules with a modal
              $scheduleButton = '';
              $table = '';
              $extras = '';
              if ($job->locked)
              {
                  $scheduleButton = "Locked";
              }
              else
              {
                  $quoteJob = $quote;
                  if (($quoteJob->picking_slab == 'Yes' || $quoteJob->picking_slab == 'Undecided') && !$quoteJob->picked_slab)
                      $scheduleButton = "Locked - No Slab Picked <br/><a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
                          data-original-title='Customer has picked slab' href='/job/$job->id/picked'><i class='fa fa-eject'>
                          </i></a>";
                  if (!$job->reviewed) $scheduleButton = "Not Reviewed";
                  $count = JobSchedule::where('job_id', $job->id)->count();
                  if ($count == 0)
                  {
                      $scheduleButton = '<a href="#" class="btn btn-warning>!No Schedule</a>"';
                  }

                  $rows = '';
                  $ok = true;
                  $count = 0;
                  $countSchedule = 0;
                  $jobSchedules = JobSchedule::where('job_id', $job->id)->get();
                  $status = '';
                  $buttonScheduleColor = 'success';
                  foreach ($jobSchedules AS $schedule)
                  {
                      if (Carbon::now() > $schedule->end && !$schedule->complete)
                      {
                          $status = 'Outdated';
                          $buttonScheduleColor = 'danger';
                      }
                      $color = null;
                      if (Carbon::parse($schedule->start) < Carbon::now())
                      {
                          $color = 'table-danger ';
                          $ok = false;
                      }
                      if (!$schedule->sent && $schedule->start)
                      {
                          $color = 'table-info ';
                      }
                      if ($schedule->complete)
                      {
                          $color = 'table-success ';
                      }
                      $notes = ($schedule->notes) ? "<br/><span style='color:#ff0000;'>" . str_replace('"', null, $schedule->notes) . "</span>" : null;

                      if ($schedule->notes != '') $count++;

                      $group = Group::find($schedule->group_id);
                      $userSchedule = User::find($schedule->user_id);
                      if ($group && $userSchedule)
                      {
                          $rows .= "<tr class='" . $color . "'><td width='25%'>" . $group->name . "</td><td width='25%'>" . $userSchedule->name . "</td><td width='50%'>" . Carbon::parse($schedule->start)->format('m/d/y h:i a')
                              . " - " . Carbon::parse($schedule->end)->format('h:i a') . $notes . "</td></tr>";
                      }
                  }

                  $buttonColor = ($ok && !isset($color)) ? 'success' : 'danger';
                  $count = ($count > 0) ? " ($count)" : null;

                  $extras = "<br/>";
                  $extras .= (!$job->schedules_sent) ? "
                <a class='tooltiped' data-toggle='tooltip' data-placement='left'
                          title='Customer has not been sent schedule'
                          href='#'><i class='fa fa-user'></i></a>" : null;
                  $extras .= (!$job->schedules_confirmed) ? "
              <a class='tooltiped' data-toggle='tooltip' data-placement='left'
                          title='Customer has not confirmed schedule'
                          href='#'><i class='fa fa-exclamation'></i></a>" : null;

                  $table = "<table id='' class='table'  ><thead><tr><th style='height: 30px'>Designation</th><th style='height: 30px'>Contractor</th><th style='height: 30px'>When</th></tr></thead><tbody>" . $rows . "</tbody></table>";

                  if($table != '')
                  {
                      if(count($jobSchedules) > 0)
                      {
                          $countText = $count;

                          $scheduleButton = '<a href="' . route('job_schedules', ['id' => $schedule->job_id]) . '" data-toggle="popover" title="Job Schedules" data-placement="left" data-html="true" data-trigger="hover" data-content="' . $table . '" class="btn btn-' . $buttonScheduleColor . ' btn-xs  popovered  "><i class="fa fa-clock-o "></i> Schedules ' . $countText . '</a>';
                      }
                      else
                      {
                          $scheduleButton = '<a href="#" class="btn btn-warning btn-xs  popovered  ">! No Schedules </a>';
                      }
                  }
              }

              $objItems[] = $scheduleButton . $extras;

              $paymentReceived = '';
              if($item->warranty == '0' && $item->payment == '1')
              {
                  $paymentReceived = 'Received';
              }
              else
              {
                  $paymentReceived = '<a href="' . route('payment_fft', ['id' => $item->id]) . '" class="btn btn-primary"><i class="fa fa-money"></i> Payment</a>';
              }

              $objItems[] = $paymentReceived;

              $status = $this->getRowColor($item);
              $objItems[] = $status;

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Determine row color.
     *
     * @issue https://github.com/vocalogic/fk2/issues/392
     * @param FFT $fft
     * @return null|string
     */
    static public function getRowColor(FFT $fft)
    {
        $color = null;
        $allRecv = false;
        if (Carbon::parse($fft->pre_schedule_start)->timestamp <= 0) // No schedule yet.
        {
            return 'NeedsWalkthrough';
        }
        else $color = 'WalkthroughScheduled'; // It's scheduled.

        if (Carbon::parse($fft->schedule_start)->timestamp > 0) // If punch scheduled.
        {
            $color = 'PunchScheduled';
        }


        // If all items have been ordered. go blue.
        $allOrdered = true;
        $job = Job::find($fft->job_id);
        if (!$job) return;

        $jobItems = JobItem::where('job_id', $job->id)
                                ->where('instanceof', 'FFT')
                                ->get();

        if (count($jobItems) > 0)
        {
            foreach ($jobItems as $item)
            {
                if ($item->ordered == '0000-00-00' && $item->orderable)
                {
                    $allOrdered = false;
                }
            }
            $allRecv = false;
            if ($allOrdered) // Everything was ordered, check to see if they have been received
            {
                $color = 'AllItemsOrdered'; // By default it's just ordered.
                $allRecv = true;
                foreach ($jobItems as $item)
                {
                    if ($item->received == '0000-00-00' && $item->orderable)
                    {
                        $allRecv = false;
                    }
                }
            }
            else $color = 'ItemsNotOrdered';
            if ($allRecv)
            {
                $color = 'ItemsReceived';
            }
        } // count > 0
        if (Carbon::now()->timestamp > Carbon::parse($fft->schedule_start)->timestamp && Carbon::parse($fft->schedule_start)->timestamp > 0 && !$fft->signoff)
        {
            $color = 'PunchNotSigned';
        }
        if (Carbon::parse($fft->schedule_start)->timestamp > 0 && $allRecv) // If punch scheduled.
        {
            $color = 'PunchScheduled';
        }

        return $color;
    }

    static public function getPunchesIndex(Fft $fft, $warranty, $request)
    {
        $job = Job::find($fft->job_id);
        if (!$job)
        {
            // This may be a warranty item so we need to look at the customer id and find out what job this was.
            $leads = Lead::where('customer_id', $fft->customer_id)->get();
            foreach($leads as $lead)
            {
                $leadId[] = $lead->id;
            }
            $quotes = Quote::whereIn('lead_id', $leadId)->get();
            foreach ($quotes AS $quote)
            {
                $job = Job::where('quote_id', $quote->id)->first();
                if ($job)
                {
                    $fft->job_id = $job->id;
                    $fft->save();
                }
            }
        }
        if (!$job) return "<b>Cannot find job to link, Old frugalk?</b>";
        $type = "FFT";
        $total = 0;
        $jobItems = JobItem::where('job_id', $job->id)
                            ->where('instanceof', $type)
                            ->get();

        foreach ($jobItems AS $item)
        {
            if ($item->verified == '0000-00-00')
            {
                $total++;
            }
        }

        $type = '';
        if ($request->has('warranty')) $type = '?warranty=1';
        if ($request->has('service')) $type = '?service=1';
        if ($total == 0)
        {
            //$button = Button::init()->text(null)->icon('check')->color('success btn-xs')->url("/punches/{$fft->id}")->render();

            $button = '<a href="' . route('punch_job', ['id' => $fft->id]) . $type . '" class="btn btn-success btn-xs"><i class="fa fa-check"></i></a>';
        }
        else
        {
            //$button = Button::init()->text($total)->icon(null)->color('danger btn-xs')->url("/punches/{$fft->id}")->render();

            $button = '<a href="' . route('punch_job', ['id' => $fft->id]) . $type . '" class="btn btn-danger btn-xs">' . $total . '</a>';
        }
        return $button;
    }

    public function displayFftNotes(Request $request)
    {
        $fft_id = $request->fft_id;

        $items = FftNote::leftJoin('users', 'fft_notes.user_id', '=', 'users.id')
                        ->where('fft_notes.fft_id', $fft_id)
                        ->select(
                                  'fft_notes.id',
                                  'fft_notes.category',
                                  'fft_notes.note',
                                  'fft_notes.created_at',
                                  'fft_notes.note',
                                  'users.name'
                                );

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = Carbon::parse($item->created_at)->format('Y-m-d h:i:s a');
              $objItems[] = $item->category;
              $objItems[] = $item->note;
              $objItems[] = $item->name;
              $objItems[] = '<a href="#" id="btn_delete_item_' . $item->id . '" class="btn btn-danger" onclick="DeleteFftNote(' . $fft_id . ', ' . $item->id . ');"><i class="fa fa-trash"></i> Delete</a>';

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveFftNote(Request $request)
    {
        $fft_id = $request->fft_id;
        $category = $request->category;
        $note = $request->note;

        $fftNote = new FftNote();
        $fftNote->user_id = Auth::user()->id;
        $fftNote->fft_id = $fft_id;
        $fftNote->category = $category;
        $fftNote->note = $note;
        $fftNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Notes Added.'
          ]
        );
    }

    public function DeleteFftNote(Request $request)
    {
        $fft_note_id = $request->fft_note_id;

        $fftNote = FftNote::find($fft_note_id);
        if(!$fftNote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Note Not Found.'
              ]
            );
        }
        $fftNote->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Note Deleted.'
          ]
        );

    }

    /**
     * Signifies that payment has been received for the FFT
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payment($id)
    {
        $fft = FFT::find($id);
        $fft->payment = 1;
        $fft->save();

        return redirect(route('ffts.index'))->with('success', 'Payment Set.');
    }

    public function close($id)
    {
        $fft = FFT::find($id);
        $openClose = '';
        if($fft->closed == '0')
        {
            $fft->closed = 1;
            $fft->closed_on = Carbon::now();
            $openClose = 'Closed';
        }
        else
        {
            $fft->closed = 0;
            $fft->closed_on = null;
            $openClose = 'Open';
        }
        $fft->save();

        return redirect(route('ffts.index'))->with('success', 'FFT ' .
        $openClose . ' Set.');
    }

    public function signature($id)
    {
        $fft = FFT::find($id);
        $job = Job::find($fft->job_id);
        if(!$job) return redirect()->back();
        $quote = Quote::find($job->quote_id);
        if(!$quote) return redirect()->back();
        $lead = Lead::find($quote->lead_id);
        if(!$lead) return redirect()->back();
        $customer = Customer::find($lead->customer_id);
        if(!$customer) return redirect()->back();

        return view('fft.signature', compact('fft', 'job', 'customer'));
    }

    public function signoff($id)
    {
        $fft = FFT::find($id);
        $job = Job::find($fft->job_id);
        if(!$job) return redirect()->back();
        $quote = Quote::find($job->quote_id);
        if(!$quote) return redirect()->back();
        $lead = Lead::find($quote->lead_id);
        if(!$lead) return redirect()->back();
        $customer = Customer::find($lead->customer_id);
        if(!$customer) return redirect()->back();

        return view('fft.signoff', compact('fft', 'job', 'customer'));
    }

    public function displayJobItems(Request $request)
    {
        $job_id = $request->job_id;

        $items = JobItem::where('instanceof', 'FFT')
                          ->where('job_id', $job_id);

        // Get Total
        $total = $items->count();

        $items = $items->get();

        $newItems = array();
        if ( !empty( $items ) )
        {
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->reference;
              $objItems[] = Carbon::parse($item->created_at)->format('Y-m-d h:i:s');

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveSign(Request $request)
    {
        $fft_id = $request->fft_id;
        $signature = $request->signature;
        $signature_img = $request->signature_img;

        $fft = Fft::find($fft_id);
        $fft->signature = $signature;
        $fft->signature_img = $signature_img;
        $fft->signoff_stamp = Carbon::now()->format('Y-m-d H:i:s');
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Set.'
          ]
        );
    }

    public function saveSignOff(Request $request)
    {
        $fft_id = $request->fft_id;
        $signoff = $request->signature;
        $signoff_img = $request->signature_img;

        $fft = Fft::find($fft_id);
        $fft->signoff = $signoff;
        $fft->signoff_img = $signoff_img;
        $fft->signoff_stamp = Carbon::now()->format('Y-m-d H:i:s');
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Set.'
          ]
        );
    }

    public function getPreSchedule(Request $request)
    {
        $fft = Fft::find($request->fft_id);
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            if(empty($fft->pre_schedule_start) || is_null($fft->pre_schedule_start))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($fft->pre_schedule_start)->format('m/d/Y');
            $timeFormat = Carbon::parse($fft->pre_schedule_start)->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    public function SetPreSchedule(Request $request)
    {
        $date = $request->date;
        $time = $request->time;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
        }
        if($time == '')
        {
            if($messageError != '') $messageError .= ', Time ';
            else $messageError = 'Time ';
        }
        if($messageError != '')
        {
            $messageError .= 'cannot empty.';
            return Response::json(
              [
                'response' => 'error',
                'message' => $messageError
              ]
            );
        }

        $dateFormat = Carbon::parse($date)->format('Y-m-d');
        $timeFormat = Carbon::parse($time)->format('H:i:s');

        $fft = Fft::find($request->fft_id);
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $fft->pre_schedule_start = $dateFormat . ' ' . $timeFormat;
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    public function getScheduleStart(Request $request)
    {
        $fft = Fft::find($request->fft_id);
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            if(empty($fft->schedule_start) || is_null($fft->schedule_start))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($fft->schedule_start)->format('m/d/Y');
            $timeFormat = Carbon::parse($fft->schedule_start)->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    public function SetScheduleStart(Request $request)
    {
        $date = $request->date;
        $time = $request->time;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
        }
        if($time == '')
        {
            if($messageError != '') $messageError .= ', Time ';
            else $messageError = 'Time ';
        }
        if($messageError != '')
        {
            $messageError .= 'cannot empty.';
            return Response::json(
              [
                'response' => 'error',
                'message' => $messageError
              ]
            );
        }

        $dateFormat = Carbon::parse($date)->format('Y-m-d');
        $timeFormat = Carbon::parse($time)->format('H:i:s');

        $fft = Fft::find($request->fft_id);
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $fft->schedule_start = $dateFormat . ' ' . $timeFormat;
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    public function signaturePdf($id)
    {
        $html = \View::make('fft.signature_pdf')->withFft(Fft::find($id))->withRaw(true)->render();

        return PDF::loadHtml($html)->setPaper('a4', 'portrait')->setWarnings(true)->download('ffft_' . $id . '_' . Carbon::parse(now())->toDateTimeString() . '.pdf');
    }



    public function deleteItem($id, Request $request)
    {
        JobItem::find($id)->delete();
        return redirect()->back()->with('success', 'Item deleted');
    }

    public function toggleReplacement($id, Request $request)
    {
        $fft = JobItem::find($id);
        $fft->replacement = $fft->replacement ? 0 : 1;
        $fft->save();
        return redirect()->back()->with('success', 'Item replacement set.');
    }

    public function toggleOrderable($id)
    {
        $fft = JobItem::find($id);
        $fft->orderable = $fft->orderable ? 0 : 1;
        $fft->save();
        return redirect()->back()->with('success', 'Item orderable set.');
    }

    public function trackItem($id, $item)
    {
        $item = JobItem::find($item);

        if (!$item->orderable)
        {
            $item->verified = Carbon::now();
            $item->save();
            return redirect()->back()->with('success', 'Item completed.');
            //return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "Item Completed!"]);
        }

        $state = 'complete';
        if ($item->verified == '0000-00-00')
        {
            $state = 'verify';
        }
        if ($item->received == '0000-00-00')
        {
            $state = 'receive';
        }
        if ($item->confirmed == '0000-00-00')
        {
            $state = 'confirm';
        }
        if ($item->ordered == '0000-00-00')
        {
            $state = 'order';
        }
        switch ($state)
        {
            case 'order' :
                $item->ordered = Carbon::now();
                $what = "Ordered";
                break;
            case 'confirm' :
                $item->confirmed = Carbon::now();
                $what = "Confirmed";
                break;
            case 'receive' :
                $item->received = Carbon::now();
                $what = "Received";
                break;
            case 'verify' :
                $item->verified = Carbon::now();
                $what = "Verified";
                break;
            default:
                $what = "nothing";
        }
        $item->save();
        $now = date("m/d/y");

        $job = Job::find($item->job_id);
        $this->checkItemsForCompletion($job);
        return Response::json(['status' => 'success', 'action' => 'reassign', 'message' => "{$what} on {$now}"]);
    }

    /**
     * Check to see if each orderable item has been received.
     *
     * @param Job $job
     * @internal param FFT $fft
     */
    public function checkItemsForCompletion(Job $job)
    {
        $pass = true;
        $jobItems = JobItems::where('job_id', $job->id)>get()
                            ->where('instanceof', 'FFT')
                            ->where('orderable', true)
                            ->get();

        foreach ($jobItems AS $item)
        {
            if (Carbon::parse($item->verified)->timestamp <= 0)
            {
                $pass = false;
            }
        }
        if ($pass)
        {
            $this->emailPunchCompletion($job);
        }
        return;
    }

    /**
     * Send an email letting everyone know that all items on the job
     * board have been addressed.
     *
     * @param Job $job
     */
    public function emailPunchCompletion(Job $job)
    {
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find('customer_id', $lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $customer = $contact->name;
        @Mail::send('emails.itemscomplete', ['contact' => $contact], function ($message) use ($job, $customer) {
            $message->to("schedules@frugalkitchens.com", "Frugal Schedules")
                ->subject("All orderable punch list items for {$customer} have been received.");
        });
    }

    public function contractorComplete($id, Request $request)
    {
        $item = JobItem::find($id);
        $item->contractor_complete = 1;
        $item->save();

        return redirect()->back()->with('success', 'Contractor Complete set.');
    }

    public function signoffPdf($id, Request $request)
    {
        $html = \View::make('fft.signoff_pdf')->withFft(FFT::find($id))->withRaw(true)->render();

        return PDF::loadHtml($html)->setPaper('a4', 'portrait')->setWarnings(true)->download('ffft_signoff_' . $id . '_' . Carbon::parse(now())->toDateTimeString() . '.pdf');
    }

    /**
     * Create new punch list item.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createItem($id, Request $request)
    {
        $fft = Fft::find($id);

        $item = new JobItem;
        $item->job_id = $fft->job_id;
        if ($fft->warranty) $item->instanceof = "Warranty";
        if ($fft->service) $item->instanceof = "Service";
        if (!$item->instanceof) $item->instanceof = "FFT";
        $item->reference = $request->item_reference;
        $item->orderable = (!empty($request->item_orderable)) ? 1 : 0;
        $item->replacement = (!empty($request->item_replacement)) ? 1 : 0;

        if ($request->has('replacement_img_1'))
        {
            $fileName = $request->file('replacement_img_1')->getClientOriginalName();
            $filePath = $request->file('replacement_img_1')->store('fft_files');
            $item->image1 = $filePath;
        }
        if ($request->has('replacement_img_2'))
        {
            $fileName = $request->file('replacement_img_2')->getClientOriginalName();
            $filePath = $request->file('replacement_img_2')->store('fft_files');
            $item->image2 = $filePath;
        }
        if ($request->has('replacement_img_3'))
        {
            $fileName = $request->file('replacement_img_3')->getClientOriginalName();
            $filePath = $request->file('replacement_img_3')->store('fft_files');
            $item->image3 = $filePath;
        }
        $item->save();
        if ($request->has('item_shop'))
        {
            // Create a job work item with this.
            $item = JobItem::find($item->id);
            $shop = new Shop();
            $shop->user_id = Auth::user()->id;
            $shop->active = 1;
            $shop->job_id = $item->job_id;
            $shop->job_item_id = $item->id;
            $shop->save();

            $job = Job::find($item->job_id);
            $quote = Quote::find($job->quote_id);
            $quoteCabinets = QuoteCabinet::where('quote_id', $quote->id)->get();

            foreach ($quoteCabinets AS $cabinet)
            {
                $cab = new ShopCabinet();
                $cab->quote_cabinet_id = $cabinet->id;
                $cab->shop_id = $shop->id;
                $cab->notes = '';
                $cab->save();
            }
        }

        // #368 - If a punch item is added and there is an FFT signoff signature
        // then we need to email punches@frugalkitchens.com
        if ($fft->signature)
        {
            @Mail::send('emails.punchwalk', ['fft' => $fft, 'item' => $item], function ($message) use ($fft) {
                $message->to("punch@frugalkitchens.com", "Frugal Kitchens")
                    ->subject("Punch Item Added after Signoff!");
            });
        }

        return redirect()->back()->with('success', 'Item Added.');
    }

    public function downloadFile($id, $img_number, Request $request)
    {
        $jobItem = JobItem::find($id);

        $jobImage = $jobItem->image1;
        if($img_number == '2') $jobImage = $jobItem->image2;
        else if($img_number == '3') $jobImage = $jobItem->image3;

        return response()->file(storage_path("app/") . $jobImage);
    }

    public function emailPunch($id)
    {
        $fft = Fft::find($id);
        $data['fft'] = $fft;

        $job = Job::find($fft->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $customer = $contact->name;
        $email = $contact->email;

        @Mail::send('emails.punch', ['fft' => $fft], function ($message) use ($fft, $customer, $email) {
            $message->to($email, "Frugal Kitchens")
                ->subject("We have prepared a punch list for your review. Please Read!");
        });
        @Mail::send('emails.punch', ['fft' => $fft], function ($message) use ($fft, $customer, $email) {
            $message->to("punch@frugalkitchens.com", "Frugal Kitchens")
                ->subject("We have prepared a punch list for your review. Please Read!");
        });

        return redirect(route('punch_job', $fft->id));
    }

    public function pay($id, Request $request)
    {
        $paid = $request->paid;
        $paid_reason = $request->paid_reason;

        $fft = FFT::find($id);
        $job = Job::find($fft->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $text = 'UNPAID';
        if($paid == '1') $text = 'PAID';

        $fft->paid = $paid;
        $fft->paid_reason = $paid_reason;
        $data['content'] = "Punch list for {$customer->name} has been marked as $text. <br/><br/>The following notes were provided: <b>$fft->paid_reason</b>";
        @Mail::send('emails.notification', $data, function ($message) use ($fft, $text, $customer) {
            $message->to(['kimw@frugalkitchens.com']);
            $message->subject("[{$customer->name}] Punches have been marked as $text!");
        });
        @Mail::send('emails.notification', $data, function ($message) use ($fft, $text, $customer) {
            $message->to(['punch@frugalkitchens.com']);
            $message->subject("[{$customer->name}] Punches have been marked as $text!");
        });
        $fft->paid = $paid;
        $fft->paid_on = Carbon::now();
        $fft->save();

        return redirect()->back()->with('success', 'Payment Status Set.');
    }

    public function warrantyIndex()
    {
        $users = User::orderBy('name')->where('active', 1)->get();
        $visitUsers = User::orderBy('name')->where('active', 1)->get();
        $punchUsers = User::orderBy('name')->where('group_id', 5)->where('active', 1)->get();
        $warranty = 1;
        $title = 'Warranties';
        $jobs = Job::where('closed', '0')->get();
        $paymentCategories = Fft::getPaymentCategories();

        $selectJob = '';
        foreach($jobs as $job)
        {
          $quote = Quote::leftJoin('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                          ->where('quotes.id', $job->quote_id)
                          ->select('quotes.lead_id', 'quote_types.name')
                          ->first();

          if(!$quote) continue;
          $lead = Lead::find($quote->lead_id);
          if(!$lead) continue;
          $customer = Customer::find($lead->customer_id);

          $selectJob .= '<option value="' . $job->id .  '">' . (($customer) ? htmlspecialchars($customer->name, ENT_QUOTES) : null) . ' (' . $quote->name . ' - ' . $job->id . ')</option>';
        }

        $customers = Customer::where('active', '1')->orderBy('name', 'asc')->get();

        return view('fft.index', compact('users', 'warranty', 'title', 'jobs', 'selectJob', 'customers', 'visitUsers', 'punchUsers', 'paymentCategories'));
    }

    /**
     * Create a new warranty.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newWarranty(Request $request)
    {
        $job_id = $request->job_id;
        $customer_id = $request->customer_id;

        if($job_id == '' || $customer_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please select job and customer.'
              ]
            );
        }

        $fft = new Fft();
        $fft->warranty = 1;
        $fft->job_id = $request->job_id;
        $fft->customer_id = $request->customer_id;
        $fft->user_id = Auth::user()->id;
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Warranty Added.'
          ]
        );
    }

    public function serviceIndex()
    {
        $users = User::orderBy('name')->where('active', 1)->get();
        $visitUsers = User::orderBy('name')->where('active', 1)->get();
        $punchUsers = User::orderBy('name')->where('group_id', 5)->where('active', 1)->get();
        $service = 1;
        $title = 'Service Work';
        $jobs = Job::where('closed', '0')->get();
        $paymentCategories = Fft::getPaymentCategories();

        $selectJob = '';
        foreach($jobs as $job)
        {
          $quote = Quote::leftJoin('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                          ->where('quotes.id', $job->quote_id)
                          ->select('quotes.lead_id', 'quote_types.name')
                          ->first();

          if(!$quote) continue;
          $lead = Lead::find($quote->lead_id);
          if(!$lead) continue;
          $customer = Customer::find($lead->customer_id);

          $selectJob .= '<option value="' . $job->id .  '">' . (($customer) ? htmlspecialchars($customer->name, ENT_QUOTES) : null) . ' (' . $quote->name . ' - ' . $job->id . ')</option>';
        }

        $customers = Customer::where('active', '1')->orderBy('name', 'asc')->get();

        return view('fft.index', compact('users', 'service', 'title', 'jobs', 'selectJob', 'customers', 'visitUsers', 'punchUsers', 'paymentCategories'));
    }

    /**
     * Create a new warranty.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newService(Request $request)
    {
        $job_id = $request->job_id;
        $customer_id = $request->customer_id;

        if($job_id == '' || $customer_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please select job and customer.'
              ]
            );
        }

        $fft = new Fft();
        $fft->warranty = 0;
        $fft->service = 1;
        $fft->job_id = $request->job_id;
        $fft->customer_id = $request->customer_id;
        $fft->user_id = Auth::user()->id;
        $fft->save();

        if (!$fft->job_id)
        {
            // Try to find a job for this customer.
            $customer = Customer::find($request->customer_id);

            $lead = Lead::where('customer_id', $customer->id)->first();
            $quotes = Quote::where('lead_id', $lead->id)->get();
            foreach ($quotes as $quote)
            {
                if ($quote->closed)
                {
                    $job = Job::where('quote_id', $quote->id)->first();
                    $fft->update(['job_id' => $job->id]);
                }
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Service Work Added.'
          ]
        );
    }

    /**
     * Create shop work from FFT job
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shopFromFft($id)
    {
        $fft = Fft::find($id);
        $shop = new Shop();
        $shop->user_id = Auth::user()->id;
        $shop->active = 1;
        $shop->job_id = $fft->job_id;
        $shop->save();

        foreach ($fft->job->quote->cabinets AS $cabinet)
        {
            $cab = new ShopCabinet();
            $cab->quote_cabinet_id = $cabinet->id;
            $cab->shop_id = $shop->id;
            $cab->save();
        }
        return redirect(route('shop'));
    }

    /**
     * Get Visit Assigned User
     * @return json
     */
    public function getVisitAssignedUser(Request $request)
    {
        $fft = Fft::join('users', 'ffts.pre_assigned', '=', 'users.id')
                      ->where('ffts.id', $request->fft_id)
                      ->select('ffts.pre_assigned', 'users.name')
                      ->first();

        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            if(empty($fft->name) || is_null($fft->name))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            return Response::json(
              [
                'response' => 'success',
                'user_id' => $fft->pre_assigned,
                'name' => $fft->name
              ]
            );
        }
    }

    /**
     * Set Visit Assigned User
     * @return json
     */
    public function setVisitAssignedUser(Request $request)
    {
        $user_id = $request->visit_assigned_user_id;

        if($user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select user.'
            ]
          );
        }

        $fft = Fft::where('id', $request->fft_id)->first();
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Fft not found.'
              ]
            );
        }
        $fft->pre_assigned = $user_id;
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Visit Assigned User set.'
          ]
        );
    }

    /**
     * Get Punch Assigned User
     * @return json
     */
    public function getPunchAssignedUser(Request $request)
    {
        $fft = Fft::join('users', 'ffts.user_id', '=', 'users.id')
                      ->where('ffts.id', $request->fft_id)
                      ->select('ffts.user_id', 'users.name')
                      ->first();

        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            if(empty($fft->name) || is_null($fft->name))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            return Response::json(
              [
                'response' => 'success',
                'user_id' => $fft->user_id,
                'name' => $fft->name
              ]
            );
        }
    }

    /**
     * Set Punch Assigned User
     * @return json
     */
    public function setPunchAssignedUser(Request $request)
    {
        $user_id = $request->punch_assigned_user_id;

        if($user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select user.'
            ]
          );
        }

        $fft = Fft::where('id', $request->fft_id)->first();
        if(!$fft)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Fft not found.'
              ]
            );
        }
        $fft->user_id = $user_id;
        $fft->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Punch Assigned User set.'
          ]
        );
    }

    public function signatureSend($id)
    {
        $fft = Fft::find($id);
        $data['fft'] = $fft;

        $job = Job::find($fft->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $customer = $contact;

        $type = ($fft->warranty) ? "Warranty" : (($fft->service) ? "Service" : "Frugal Final Touch");

        Mail::send('emails.fftsignature', $data, function ($message) use ($customer, $type)
        {
            $message->to(['fft@frugalkitchens.com', $customer->email]);
            $message->subject("[$customer->name] (PENDING APPROVAL) A $type signature has been requested.");

        });
        return redirect()->back()->with('success', 'Email sent');
    }


    /**
     * Show an existing task
     * @param Task $task
     * @return mixed
     */
    public function show(Fft $fft)
    {

    }

    /**
     * Create new task
     * @return mixed
     */
    public function create()
    {
        return view('ffts.create');
    }

    /**
     * Store a new task
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {

    }

    /**
     * Update a quote.
     * @param Quote $quote
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Fft $fft, Request $request)
    {

    }


}
