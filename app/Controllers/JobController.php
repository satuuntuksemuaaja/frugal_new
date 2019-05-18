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
use FK3\Models\File;
use FK3\Models\Fft;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteAppliance;
use FK3\Models\Question;
use FK3\Models\QuestionAnswer;
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
use FK3\vl\core\Formatter;
use FK3\vl\core\ScheduleEngine;
use FK3\vl\jobs\JobBoard;
use FK3\vl\quotes\QuoteGeneratorNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use PDF;
use Response;
use Storage;
use Auth;
use App;
use View;
use Mail;

class JobController extends Controller
{
    public $auditPage = "Job";
    public $bgColorSuccess = "#d9ead0";
    public $colorSuccess = "#82b964";
    public $bgColorInfo = "#dff3f9";
    public $colorInfo = "#5bc0de";

    /*
     * Show Task Index.
     */
    public function index(Request $request)
    {
        if (Auth::user()->group_id == 20)
        {
            return redirect(route('leads.index'));
        }

        $users = User::orderBy('name')->where('active', 1)->get();

        return view('job.index', compact('request', 'users'));
    }

    /**
     * Display Job Data
     * @return Array
     */
    public function displayJobs(Request $request)
    {		
		$search_by = $request->search_by;
		$q = $request->q;
		
		$sort_by = $request->order[0]['column'];
		$sort_dir = $request->order[0]['dir'];
		
        $items = Job::join('quotes', 'jobs.quote_id', '=', 'quotes.id')
					->join('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
					->join('leads', 'quotes.lead_id', '=', 'leads.id')
					->leftJoin('users as userDesigner', 'leads.user_id', 'userDesigner.id')
					->leftJoin('customers', 'leads.customer_id', 'customers.id')
					->leftJoin('authorizations', 'jobs.id', '=', 'authorizations.job_id')
					->where('jobs.closed', 0);
		
		if($q != '')
		{			
			if($search_by == 'Client Name') $items = $items->where('customers.name', 'like', '%' . $q . '%');
			else if($search_by == 'Quote Type') $items = $items->where('quote_types.name', 'like', '%' . $q . '%');
			else if($search_by == 'Designer') $items = $items->where('userDesigner.name', 'like', '%' . $q . '%');
			else if($search_by == 'Closed') $items = $items->where('jobs.closed_on', 'like', '%' . $q . '%');
			else if($search_by == 'Starts') $items = $items->where('jobs.start_date', 'like', '%' . $q . '%');
		}
		
		if($sort_by == '0') $items = $items->orderBy('customers.name', $sort_dir);
		else if($sort_by == '1') $items = $items->orderBy('userDesigner.name', $sort_dir);
		else if($sort_by == '2') $items = $items->orderBy('jobs.closed_on', $sort_dir);
		else if($sort_by == '7') $items = $items->orderBy('jobs.start_date', $sort_dir);
		
		$items = $items->select(
								'jobs.id as job_id',
								'jobs.quote_id',
								'jobs.locked',
								'jobs.reviewed',
								'jobs.closed_on',
								'jobs.start_date',
								'jobs.locked',
								'jobs.schedules_sent',
								'jobs.schedules_confirmed',
								'quote_types.name as quote_type_name',
								'authorizations.signature',
								'userDesigner.name as user_designer_name',
								'customers.name as cust_name',
								'customers.id as customer_id',
								'quotes.picking_slab',
								'quotes.picked_slab'
							)
					->skip($request->start)
					->take($request->length)
					->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
			$totalRec = 0;
			
            foreach($items as $job)
            {
				$jobVerify = Job::find($job->job_id);
                //$this->verifyCabinets($jobVerify);

                if ($job->signature == null)
                {
                    $authIcon = "<i class='fa fa-comment text-warning'></i>";
                }
                else
                {
                    if ($job->signature)
                        $authIcon = "<i class='fa fa-comment text-success'></i>";
                    else
                        $authIcon = "<i class='fa fa-comment text-danger'></i>";
                }

                $objItems = array();
				
				//Client
				$client = !empty($job->cust_name) ? '<a href="' . route('view_profile', ['id' => $job->customer_id]) . '">' . $job->cust_name . '</a>' : '--no customer yet--';
				$client .= '<br>';
				$client .= '<a data-toggle="tooltip" title="View Job" href="' . route('quote_view', $job->quote_id) . '"><i class="fa fa-search"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Drawings" href="#" onclick="ShowModalDrawing(' . $job->quote_id . ');"><i class="fa fa-picture-o"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Job Notes" href="#" onclick="ShowModalNotes(' . $job->job_id . ');"><i class="fa fa-edit"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Delete Job" href="#" onclick="ShowModalDeleteConfirm(' . $job->job_id . ')"><i class="fa fa-trash"></i></a>';

				if ($job->locked)
				{
					$client .= '&nbsp;';
					$client .= '<a data-toggle="tooltip" title="Unlock Schedule" href="' . route('set_job_unlock', ['id' => $job->job_id]) . '"><i class="fa fa-unlock"></i></a>';
				}

				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Add Task" href="#" onclick="ShowModalAddTask(' . $job->job_id . ');"><i class="fa fa-openid"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Money Received!" href="#" onclick=""><i class="fa fa-money"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Override XML" href="#" onclick="ShowModalOverrideXml(' . $job->job_id . ');"><i class="fa fa-recycle"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Job Summary" href="#" onclick=""><i class="fa fa-arrow-down"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Construction Verification" href="#" onclick="ShowModalConstructionConfirm(' . $job->job_id . ');"><i class="fa fa-check"></i></a>';
				$client .= '&nbsp;';

				$spinEye = '';
				$notReviewed = '';
				$textReviewed = ' text-success';
				//if not reviewed
				if($job->reviewed == null || $job->reviewed == '0000-00-00 00:00:00')
				{
					$spinEye = 'fa-spin ';
					$notReviewed = ' Not ';
					$textReviewed = ' text-danger';
				}
				$client .= '<a data-toggle="tooltip" title="Job ' . $notReviewed . 'Reviewed" href="#" onclick="SetReview(' . $job->job_id . ')"><i class="fa ' . $spinEye . 'fa-eye ' . $textReviewed . '"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Cabinet Arrival Email Sent" href="#" onclick="SetArrival(' . $job->job_id . ');"><i class="fa fa-calendar"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Appliance Settings" href="#" onclick="ShowModalQuoteAppliances(' . $job->quote_id . ');"><i class="fa fa-wrench"></i></a>';
				$client .= '&nbsp;';
				$client .= '<a data-toggle="tooltip" title="Multiple Authorization" href="' . route('customer_job_multiple_auth', ['id' => $job->customer_id]) . '"><i class="fa fa-comment"></i></a>';
				$client .= '<br>';

				$quoteTypeName = $job->quote_type_name;

				$customerId = $job->customer_id;
				$client .= $quoteTypeName . ' - ' . $customerId;

				$objItems[] = $client;

				//Designer
				$objItems[] = ($job->user_designer_name) ? $job->user_designer_name : '--no designer yet--';
				$objItems[] = Carbon::parse($job->closed_on)->format('Y-m-d');

				//po cabinets
				$poNumber = '';
				if($job->job_id == '780') $poNumber = '<span id="span_test_' . $job->job_id . '"></span>';	
				
				$pos = Po::where('job_id', $job->job_id)
						  ->where('type', 'Cabinets')
						  ->select(
									'id',
									'number',
									'submitted',
									'status',
									'vendor_id'
								  )
						  ->get();

				foreach($pos as $po)
				{
					$color = 'success';
					if ($po->status == 'draft')
					{
						$color = 'danger';
					}
					elseif ($po->status == 'ordered') $color = 'info';
					elseif ($po->status == 'complete') $color = 'success';
					else $color = 'warning';

					if($poNumber == '')
					{
						$poNumber .= '<a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'" data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
					else
					{
						$poNumber .= '<br><a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'" data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
				}
				

				$objItems[] = $poNumber;

				//Hardware
				$poNumber = '';
				
				
				$pos = Po::where('job_id', $job->job_id)
						  ->where('type', 'Hardware')
						  ->select(
									'id',
									'number',
									'submitted',
									'status',
									'vendor_id'
								  )
						  ->get();

				foreach($pos as $po)
				{
					$color = 'success';
					if ($po->status == 'draft')
					{
						$color = 'danger';
					}
					elseif ($po->status == 'ordered') $color = 'info';
					elseif ($po->status == 'complete') $color = 'success';
					else $color = 'warning';

					if($poNumber == '')
					{
						$poNumber .= '<a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'" data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
					else
					{
						$poNumber .= '<br><a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'" data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
				}
				

				$objItems[] = $poNumber;

				//Accessories
				$poNumber = '';
				
				
				$pos = Po::where('job_id', $job->job_id)
						  ->where('type', 'Accessories')
						  ->select(
									'id',
									'number',
									'submitted',
									'status',
									'vendor_id'
								  )
						  ->get();
				
				foreach($pos as $po)
				{
					$color = 'success';
					if ($po->status == 'draft')
					{
						$color = 'danger';
					}
					elseif ($po->status == 'ordered') $color = 'info';
					elseif ($po->status == 'complete') $color = 'success';
					else $color = 'warning';

					if($poNumber == '')
					{
						$poNumber .= '<a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'" data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
					else
					{
						$poNumber .= '<br><a href="' . route('view_po', ['id' => $po->id]) . '" class="btn btn-' . $color .'data-toggle="popover" data-placement="left" data-html="true" data-trigger="hover" data-content="Vendor: ' . ($po->vendor ? $po->vendor->name : 'Unknown Vendor') . '" btn-xs  popovered  ">#' . $po->number . '</a><br>' . Carbon::parse($po->submitted)->format('Y-m-d');
					}
				}
				

				$objItems[] = $poNumber;

				//Get Item status
				$itemData = '';
				
				
				$items = JobItem::where('job_id', $job->job_id)
								->where('instanceof', 'Item')
								->get();
				$confirmed = true; // Assume we're good
				$found = 0;
				foreach ($items AS $item)
				{
					if (empty($item->verified) || $item->verified == '0000-00-00')
					{
						$found++;
						$confirmed = false;
					}
				}
				if ($confirmed)
				{
					$itemData = "<a href='#' class='btn btn-success' onclick='ShowModalJobItem(" . $job->job_id . ");'>
					  <i class='fa fa-check'></i></a>";
				}
				else
				{
					$itemData = "<a href='#' class='btn btn-danger' onclick='ShowModalJobItem(" . $job->job_id . ");'>({$found})</a>";
				}			

				//Download
				$buttonDownload = '<a href="' . route('job_checklist', ['id' => $job->job_id]) . '" target="_blank" class="btn btn-success"><i class="fa fa-download"></i></a>';

				$objItems[] = $itemData . $buttonDownload;

				//Start date
				$objItems[] = "<a href='#' onclick='ShowModalSetStartDate(" . $job->job_id . ");' id='a_start_date_" . $job->job_id . "'>" . (($job->start_date == '0000-00-00') ? "No Start Date" : $job->start_date) . "</a>";

				// Make a popover with the schedules with a modal
				$scheduleButton = '';
				$table = '';
				$extras = '';
				$status = '';
				if ($job->locked)
				{
					$scheduleButton = "Locked";
				}
				else
				{
					if (($job->picking_slab == 'Yes' || $job->picking_slab == 'Undecided') && !$job->picked_slab)
						$scheduleButton = "Locked - No Slab Picked <br/><a class='tooltiped get text-danger' data-toggle='tooltip' data-placement='right'
							data-original-title='Customer has picked slab' href='/job/$job->job_id/picked'><i class='fa fa-eject'>
							</i></a>";
					if (!$job->reviewed) $scheduleButton = "Not Reviewed";
					$count = JobSchedule::where('job_id', $job->job_id)->count();
					if ($count == 0)
					{
						$scheduleButton = '<a href="' . route('job_schedules', ['id' => $job->job_id]) . '" class="btn btn-warning>! No Schedule</a>"';
					}

					$rows = '';
					$ok = true;
					$count = 0;
					$countSchedule = 0;
					
					 
					$jobSchedules = JobSchedule::where('job_id', $job->job_id)->get();
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
						if ($group)
						{
							$rows .= "<tr class='" . $color . "'><td width='25%'>" . $group->name . "</td><td width='25%'>" . @$userSchedule->name . "</td><td width='50%'>" . Carbon::parse($schedule->start)->format('m/d/y h:i a')
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

							$scheduleButton = '<a href="' . route('job_schedules', ['id' => $job->job_id]) . '" data-toggle="popover" title="Job Schedules" data-placement="left" data-html="true" data-trigger="hover" data-content="' . $table . '" class="btn btn-' . $buttonScheduleColor . ' btn-xs  popovered  "><i class="fa fa-clock-o "></i> Schedules ' . $countText . '</a>';
						}
						else
						{
							$scheduleButton = '<a href="' . route('job_schedules', ['id' => $job->job_id]) . '" class="btn btn-warning btn-xs  popovered  ">! No Schedules </a>';
						}
					}
					
				}

				$objItems[] = $scheduleButton . $extras;

				$objItems[] = $status;

				$newItems[] = $objItems;
            }
        }

		
        $total = Job::join('quotes', 'jobs.quote_id', '=', 'quotes.id')
					->join('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
					->join('leads', 'quotes.lead_id', '=', 'leads.id')
					->leftJoin('users as userDesigner', 'leads.user_id', 'userDesigner.id')
					->leftJoin('customers', 'leads.customer_id', 'customers.id')
					->leftJoin('authorizations', 'jobs.id', '=', 'authorizations.job_id')
					->where('jobs.closed', 0);
					
		if($search_by == 'Client Name') $total = $total->where('customers.name', 'like', '%' . $q . '%');
		else if($search_by == 'Quote Type') $total = $total->where('quote_types.name', 'like', '%' . $q . '%');
		else if($search_by == 'Designer') $total = $total->where('userDesigner.name', 'like', '%' . $q . '%');
		else if($search_by == 'Closed') $total = $total->where('jobs.closed_on', 'like', '%' . $q . '%');
		else if($search_by == 'Starts') $total = $total->where('jobs.start_date', 'like', '%' . $q . '%');
					
		$total = $total->orderBy('jobs.start_date', 'desc')
					->select(
								'jobs.id as job_id',
								'jobs.quote_id',
								'jobs.locked',
								'jobs.reviewed',
								'jobs.closed_on',
								'jobs.start_date',
								'jobs.locked',
								'jobs.schedules_sent',
								'jobs.schedules_confirmed',
								'quote_types.name as quote_type_name',
								'authorizations.signature',
								'userDesigner.name as user_designer_name',
								'customers.name as cust_name',
								'customers.id as customer_id',
								'quotes.picking_slab',
								'quotes.picked_slab'
							)
					->count();
					
        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Make sure that references for cabinets match what we have the in quote.
     *
     * @param  Job $job [description]
     */
    static public function verifyCabinets(Job $job)
    {
        $ids = [];
        $cabinets = QuoteCabinet::join('cabinets', 'quote_cabinets.cabinet_id', 'cabinets.id')
                                ->select('cabinets.id')
                                ->where('quote_cabinets.quote_id', $job->quote_id)
                                ->where('quote_cabinets.deleted_at', null)
                                ->get();

        foreach ($cabinets AS $cabinet)
            $ids[] = $cabinet->id; // This is a quote_cabinet id reference. So keep it.
        $ids = implode(',', $ids);
        if ($ids)
        {
            \DB::statement("DELETE from job_items WHERE (job_id='$job->id' AND instanceof='Cabinet') AND reference NOT IN($ids)");
        }
    }

    public function displayJobItems(Request $request)
    {
        $job_id = $request->job_id;

        $items = JobItem::where('job_id', $job_id)
                            ->where('instanceof', 'Item')
                            ->orderBy('verified', 'asc');

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->reference;

              $verified = '';
              if($item->verified == '0000-00-00' || empty($item->verified))
              {
                  $verified = '<a href="#" id="btn_verify_item_' . $item->id . '" class="btn btn-info" onclick="SetVerifyItem(' . $job_id . ', ' . $item->id . ')"><i class="fa fa-check"></i> Verify</a>&nbsp;<a href="#" id="btn_delete_item_' . $item->id . '" class="btn btn-danger" onclick="DeleteItem(' . $job_id . ', ' . $item->id . ')"><i class="fa fa-trash"></i> Delete</a>';
              }
              else
              {
                  $verified = Carbon::parse($item->verified)->format('Y-m-d H:i A');
              }
              $objItems[] = $verified;

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveJobItems(Request $request)
    {
        $job_id = $request->job_id;
        $reference = $request->reference;

        if($job_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Job ID required.'
              ]
            );
        }

        if($reference == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please fill the Item.'
              ]
            );
        }

        $jobItem = new JobItem();
        $jobItem->job_id = $job_id;
        $jobItem->instanceof = 'Item';
        $jobItem->reference = $reference;
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item added.'
          ]
        );
    }

    public function setVerifyItem(Request $request)
    {
        $item_id = $request->item_id;
        $jobItem = JobItem::find($item_id);
        if(!$jobItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item not found.'
              ]
            );
        }

        $jobItem->verified = Carbon::parse(now())->format('Y-m-d');
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item verified.'
          ]
        );
    }

    public function deleteItem(Request $request)
    {
        $item_id = $request->item_id;
        $jobItem = JobItem::find($item_id);
        if(!$jobItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item not found.'
              ]
            );
        }

        $jobItem->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item deleted.'
          ]
        );
    }

    /**
     * Display Job Files Data
     * @return Array
     */
    public function displayFiles(Request $request)
    {
      $quote_id = $request->quote_id;

      $items = File::leftJoin('users', 'files.user_id', '=', 'users.id')
                        ->select(
                                  'files.id',
                                  'files.location',
                                  'files.description',
                                  'users.name as user_name',
                                  'files.attached',
                                  'files.created_at'
                                )
                        ->where('files.quote_id', $quote_id)
                        ->where('files.deleted_at', null)
                        ->get();

      if ( !empty( $items ) )
      {
          $newItems = array();
          foreach ( $items as $item )
          {
            $objItems = array();

            $objItems[] = '<a href="' . route('job_download_file', ['id' => $quote_id, 'file_id' => $item->id]) . '" target="_blank"><i class="fa fa-download"></i></a>';
            $objItems[] = $item->description;
            $objItems[] = $item->user_name . ' on ' . Carbon::parse($item->created_at)->format('m/d/Y H:i:s');

            $attached = 'No';
            if($item->attached == '1') $attached = 'Yes';
            $objItems[] = $attached;
            $objItems[] = '<a href="#" onclick="DoDeleteFile(' . $quote_id . ',' . $item->id . ')"><i class="fa fa-trash"></i></a>';

            $newItems[] = $objItems;
          }
      }

      // Get Total
      $total = File::where('deleted_at', null)->count();

      return array( 	'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'data' => $newItems
          );
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

        return redirect(route('jobs.index') . '?upload_file=1&quote_id=' . $id);
    }

    public function downloadFile($id, $file_id, Request $request)
    {
        $file = File::find($file_id);

        return response()->download(public_path("app/") . $file->location);
    }

    public function deleteFile(Request $request)
    {
        $file_id = $request->file_id;
        $file = File::find($file_id);
        @unlink(public_path('app') . '/' . $file->location);
        $file->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'File deleted.'
          ]
        );
    }

    public function displayJobNotes(Request $request)
    {
        $job_id = $request->job_id;

        $items = JobNote::leftJoin('users', 'job_notes.user_id', '=', 'users.id')
                          ->select(
                                    'job_notes.id',
                                    'job_notes.note',
                                    'users.name',
                                    'job_notes.created_at'
                                  )
                          ->where('job_notes.job_id', $job_id)
                          ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = Carbon::parse($item->created_at)->format('m/d/Y H:i:s');
              $objItems[] = $item->note;
              $objItems[] = $item->name;

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = JobNote::where('job_id', $job_id)->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveJobNotes(Request $request)
    {
        $job_id = $request->job_id;
        $note = $request->notes;

        if($note == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please fill notes field.'
            ]
          );
        }

        $jobNote = new JobNote();
        $jobNote->job_id = $job_id;
        $jobNote->note = $note;
        $jobNote->user_id = Auth::user()->id;
        $jobNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Note Added.'
          ]
        );
    }

    /**
     * Show an existing task
     * @param Task $task
     * @return mixed
     */
    public function show(Task $task)
    {

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
    public function update(Task $task, Request $request)
    {

    }

    /**
     * Delete job
     * @param $id
     * @return array
     */
    public function destroy($id)
    {
        $job = Job::find($id);
        JobItem::where('job_id', $id)->delete();
        JobNote::where('job_id', $id)->delete();
        JobSchedule::where('job_id', $id)->delete();

        $tasks = Task::where('job_id', $id)->get();
        foreach($tasks as $task)
        {
            TaskNote::where('task_id', $task->id)->delete();
        }
        Task::where('job_id', $id)->delete();

        $job->delete();

        return redirect(route('jobs.index'))->with('success', 'Job deleted!');;
    }

    public function jobSaveTask(Request $request)
    {
      $subject = $request->subject;
      $due_date = $request->due_date;
      $due_time = $request->due_time;
      $urgent = $request->urgent;
      $assigned_id = $request->assigned_id;
      $body = $request->body;
      $job_id = $request->job_id;

      $job = Job::find($job_id);
      if(!$job)
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Job not found.'
            ]
          );
      }

      $quote_id = $job->quote_id;

      if($subject == '' || $body == '')
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Subject and Details cannot empty.'
            ]
          );
      }

      $quote = Quote::find($quote_id);
      $lead = Lead::find($quote->lead_id);
      $customer_id = 0;
      if($lead) $customer_id = $lead->customer_id;

      $task = new Task;
      $task->user_id = Auth::user()->id;
      $task->assigned_id = $assigned_id;
      $task->subject = $subject;
      $task->body = $body;
      $task->job_id = $job_id;
      $task->customer_id = $customer_id;
      $task->urgent = $urgent;
      $task->closed = 0;
      if ($request->due_date != '')
      {
          $due_date = Carbon::parse($due_date)->format('Y-m-d');
      }
      if ($request->due_time != '')
      {
          $due_time = Carbon::parse($due_date)->format('H:i:s');
      }

      if($due_date != '' && $due_time != '') $task->due = $due_date . ' ' . $due_time;

      $task->save();

      if ($task->assigned_id > 0)
      {
        $user = User::find($task->assigned_id);
        $user->task_id = $task->id;
        $user->save();

        $customer = Customer::find($task->customer_id);
        $urgentMessage = ($task->urgent) ? "** URGENT ** (Reply TC or LM when Complete) - " : null;
        \FK3\vl\core\SMS::command('directory.send',
                  ['target' => $user->mobile,
                  'message' => "($task->id) $customer {$urgentMessage} New Task: $task->subject : $task->body"]);
      }

      // Create a Google Calendar Event
      try
      {
        if ($due_date != '')
        {
          $params = [];
          $params['title'] = $task->subject;
          $params['location'] = "Task #{$task->id} in frugalk.com";
          $params['description'] = $task->body;
          $params['start'] = Carbon::parse($task->due);
          $params['end'] = Carbon::parse($task->due)->addMinutes(30);
          \FK3\vl\core\Google::event(User::find($task->assigned_id), $params);
        }
      }
      catch (Exception $e)
      {

      }

      return Response::json(
        [
          'response' => 'success',
          'message' => 'Task Added.'
        ]
      );
    }

    public function arrival($id)
    {
        $job = Job::find($id);
        $job->sent_cabinet_arrival = 1;
        $job->save();

        $quote = Quote::find($job->quote_id);
        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Quote Not found. Can\'t send email.'
              ]
            );
        }

        $lead = Lead::find($quote->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Lead Not found. . Can\'t send email.'
              ]
            );
        }
        $customer = Customer::find($lead->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Customer Not found. . Can\'t send email.'
              ]
            );
        }

        $contact = Contact::where('customer_id', $customer->id)->first();
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Contact Not found. . Can\'t send email.'
              ]
            );
        }

        // Now we should probably send it.
        $subject = "[Frugal Kitchens/$customer->name] Your Cabinets are Shipping!";
        $data = [
            'customer' => $customer,
            'contact'  => $contact,
            'content'  => "Hi $contact->name, We wanted to let you know that we have received a tentative ship date.
            Could you please call us at 770-486-1247 to set up an installation date!
            <b>Please remember that these dates are approximate</b> and we can’t guarantee the date we set up until we receive the cabinets.
            If you an exact date we will not be able to schedule anything till we have the product in our possession."
        ];
        @Mail::send('emails.notification', $data, function ($message) use ($contact, $subject) {
            $message->to([
                $contact->email             => $contact->name,
                'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject($subject);
        });

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Arrival Set. Email sent.'
          ]
        );

    }

    public function displayJobAppliances(Request $request)
    {
        $quote_id = $request->quote_id;

        $items = QuoteAppliance::leftJoin('appliances', 'quote_appliances.appliance_id', '=', 'appliances.id')
                          ->where('quote_appliances.quote_id', $quote_id)
                          ->where('appliances.active', '1')
                          ->select(
                                    'appliances.id as appliances_id',
                                    'appliances.name',
                                    'quote_appliances.id as quote_appliances_id',
                                    'quote_appliances.brand',
                                    'quote_appliances.model',
                                    'quote_appliances.size'
                                  )
                          ->orderBy('appliances.name');

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            $quoteApplianceIdArr = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->name;
              $objItems[] = '<input type="text" name="brand_' . $item->quote_appliances_id . '" id="brand_' . $item->quote_appliances_id . '" class="form-control" value="' . $item->brand . '">';
              $objItems[] = '<input type="text" name="model_' . $item->quote_appliances_id . '" id="model_' . $item->quote_appliances_id . '" class="form-control" value="' . $item->model . '">';
              $objItems[] = '<input type="text" name="size_' . $item->quote_appliances_id . '" id="size_' . $item->quote_appliances_id . '" class="form-control" value="' . $item->size . '">';

              $newItems[] = $objItems;
              $quoteApplianceIdArr[] = $item->quote_appliances_id;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems,
              'quoteApplianceIdArr' => $quoteApplianceIdArr
            );
    }

    public function saveJobAppliances(Request $request)
    {
        $quoteApplianceIdArr = $request->quoteApplianceIdArr;

        $quoteApplianceIdArr = explode(',', $quoteApplianceIdArr);
        for($x = 0; $x < count($quoteApplianceIdArr); $x++)
        {
            $brand = $request->input('brand_' . $quoteApplianceIdArr[$x]) ? $request->input('brand_' . $quoteApplianceIdArr[$x]) : '';
            $model = $request->input('model_' . $quoteApplianceIdArr[$x]) ? $request->input('model_' . $quoteApplianceIdArr[$x]) : '';
            $size = $request->input('size_' . $quoteApplianceIdArr[$x]) ? $request->input('size_' . $quoteApplianceIdArr[$x]) : '';

            QuoteAppliance::where('id', $quoteApplianceIdArr[$x])
                          ->update([
                                    'brand' => $brand,
                                    'model' => $model,
                                    'size' => $size
                                  ]);
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Appliances Data Updated.'
          ]
        );
    }

    /**
     * Send customer a link to fill in their appliances makes and models.
     * @param $id
     */
    public function sendQuoteAppliances(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = Quote::find($quote_id);

        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote Not found. Can\'t send email.'
              ]
            );
        }

        $lead = Lead::find($quote->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead Not found. Can\'t send email.'
              ]
            );
        }
        $customer = Customer::find($lead->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Customer Not found. Can\'t send email.'
              ]
            );
        }

        $contact = Contact::where('customer_id', $customer->id)->first();
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Contact Not found. Can\'t send email.'
              ]
            );
        }

        $data = [
            'quote' => $quote,

        ];

        @Mail::send('emails.appliances', $data, function ($message) use ($contact) {
            $message->to([
                $contact->email => $contact->name,
                //       'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject("IMPORTANT! Please confirm your appliances for your Frugal Kitchens and Cabinets Job");
        });

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Email sent.'
          ]
        );
    }

    public function auth($job_id, Request $request)
    {
        $job = Job::find($job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $auth = Authorization::where('job_id', $job->id)->first();
        if(!$auth)
        {
            $auth = new Authorization();
            $auth->job_id = $job_id;
            $auth->signature = '';
            $auth->save();
        }
        $authLists = AuthorizationList::all();

        return view('job.auth', compact('job', 'customer', 'auth', 'authLists', 'request'));
    }

    public function displayJobAuthItems(Request $request)
    {
        $job_id = $request->job_id;

        $authorization = Authorization::where('job_id', $job_id)->first();

        if(!$authorization)
        {
            return array( 	'iTotalRecords' => 0,
                  'iTotalDisplayRecords' => 0,
                  'data' => []
                );
        }

        $items = AuthorizationItem::where('authorization_id', $authorization->id);

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->item;
              $objItems[] = '<a href="#" class="btn btn-danger" onclick="DeleteAuthItem(' . $item->id . ');"><i class="fa fa-trash"></i> Delete</a>';

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveAuthItem(Request $request)
    {
        $job_id = $request->job_id;
        $auth_item = $request->auth_item;
        $new_auth_item = $request->new_auth_item;

        if($auth_item == '' && $new_auth_item == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please select an authorization item or fill the manual item field.'
              ]
            );
        }

        if($auth_item != '' && $new_auth_item != '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please not fill both, just select an authorization item or fill the manual item field.'
              ]
            );
        }

        $auth = Authorization::where('job_id', $job_id)->first();
        if(!$auth)
        {
            $auth = new Authorization();
            $auth->job_id = $job_id;
            $auth->signature = '';
            $auth->save();
        }

        $authItem = new AuthorizationItem();
        $authItem->authorization_id = $auth->id;

        if($auth_item != '') $authItem->item = $auth_item;
        else if($new_auth_item != '') $authItem->item = $new_auth_item;

        $authItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Authorization Item Added.'
          ]
        );
    }

    public function deleteAuthItem(Request $request)
    {
        $item_id = $request->item_id;

        $authItem = AuthorizationItem::find($item_id);
        if(!$authItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Authorization Item Not Found.'
              ]
            );
        }
        $authItem->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Authorization Item Deleted.'
          ]
        );
    }

    /**
     * Send email to customer.
     * @param $id
     */
    public function authSend(Request $request)
    {
        $job_id = $request->job_id;
        $job = Job::find($job_id);
        $auth = Authorization::where('job_id', $job_id)->first();
        if(!$auth)
        {
            $auth = new Authorization();
            $auth->job_id = $job->id;
            $auth->signature = '';
            $auth->save();
        }
        $data['auth'] = $auth;
        $quote = Quote::find($job->quote_id);

        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote Not found. Can\'t send email.'
              ]
            );
        }

        $lead = Lead::find($quote->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead Not found. Can\'t send email.'
              ]
            );
        }
        $customer = Customer::find($lead->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Customer Not found. Can\'t send email.'
              ]
            );
        }

        $contact = Contact::where('customer_id', $customer->id)->first();
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Contact Not found. Can\'t send email.'
              ]
            );
        }

        $customer = $customer->name;
        $email = $contact->email;

        Mail::send('emails.jobauth', ['auth' => $auth], function ($message) use ($auth, $customer, $email) {
            $message->to($email, "Frugal Kitchens")
                ->subject("Prior Authorization Required - PLEASE READ!");
        });

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Email Sent.'
          ]
        );
    }

    public function displayJobAuthSignItems(Request $request)
    {
        $job_id = $request->job_id;

        $authorization = Authorization::where('job_id', $job_id)->first();

        if(!$authorization)
        {
            return array( 	'iTotalRecords' => 0,
                  'iTotalDisplayRecords' => 0,
                  'data' => []
                );
        }

        $items = AuthorizationItem::where('authorization_id', $authorization->id);

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->item;

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveAuthSign(Request $request)
    {
        $job_id = $request->job_id;
        $signature = $request->signature;

        $auth = Authorization::where('job_id', $job_id)->first();
        if(!$auth)
        {
            $auth = new Authorization();
            $auth->job_id = $job_id;
        }
        $auth->signature = $signature;
        $auth->signed_on = Carbon::now()->format('Y-m-d H:i:s');
        $auth->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Set.'
          ]
        );
    }

    public function removeAuthSign(Request $request)
    {
        $job_id = $request->job_id;

        $auth = Authorization::where('job_id', $job_id)->first();
        if(!$auth)
        {
            $auth = new Authorization();
            $auth->job_id = $job_id;
        }
        $auth->signature = '';
        $auth->signed_on = null;
        $auth->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Removed.'
          ]
        );
    }

    /**
     * Download PDF of Checklist
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function checklist($id)
    {
        libxml_use_internal_errors(true);
        $data = $this->generatePdf($id);

        return PDF::loadHtml($data)->setPaper('a4', 'portrait')->setWarnings(true)->download('job_checklist_' . Carbon::parse(now())->toDateTimeString() . '.pdf');
    }

    public function generatePdf($job_id)
    {
        $job = Job::find($job_id);

        $quote = Quote::find($job->quote_id);
        $meta = unserialize($quote->meta)['meta'];
        $accessoryData = null;
        $hardwareData = null;
        // Accessory.
        $accids = (isset($meta['quote_accessories'])) ? $meta['quote_accessories'] : [];
        foreach ($accids AS $acc => $qty)
        {

            $accessory = Accessory::find($acc);
            $accessoryData .= "<tr><td>{$accessory->sku} - {$accessory->name}</td><td>$qty</td></tr>";
        }

        $accids = (isset($meta['quote_pulls'])) ? $meta['quote_pulls'] : [];
        foreach ($accids AS $acc => $qty)
        {
            $hardware = Hardware::find($acc);
            $hardwareData .= "<tr><td>(PULL) {@$hardware->sku} - $hardware->description</td><td>$qty</td></tr>";
        }

        $accids = (isset($meta['quote_knobs'])) ? $meta['quote_knobs'] : [];
        foreach ($accids AS $acc => $qty)
        {
            $hardware = Hardware::find($acc);
            $hardwareData .= "<tr><td>(KNOB) {@$hardware->sku} - $hardware->description</td><td>$qty</td></tr>";
        }


        $cabinets = null;
        $quoteCabinets = QuoteCabinet::where('quote_id', $quote->id)->get();
        foreach ($quoteCabinets AS $quoteCabinet)
        {
            $cabinet = Cabinet::find($quoteCabinet->cabinet_id);
            $cabinets .= $cabinet->frugal_name;
            if ($cabinet->color)
                $cabinets .= " ({$quoteCabinet->color})";
            $cabinets .= "<br/>";
        }

        $check = null;
        $cat = null;
        foreach (Checklist::orderBy('category')->get() AS $checklist)
        {
            if ($checklist->category != $cat)
            {
                if ($cat)
                    $check .= "</table><br/><br/><b>Signature: ________________________________________________________</b>";
                $check .= "<h4 style='background-color: yellow; border: 2px solid #000000; padding: 10px; text-align: center;'>$checklist->category</h4>";
                $cat = $checklist->category;
                $check .= "<table border=1 cellpadding='4' width='100%'>";
            }

            $check .= "<tr><td width='95%'>$checklist->question</td><td><input type='text' width='20px'></td></tr>";
        }
        $check .= "</table><br/><br/><b>Signature: ________________________________________________________</b>";


        $special = (isset($meta['quote_special']) && $meta['quote_special']) ? "<h4>Special Instructions:</h4><p>" .$meta['quote_special'] . "</p>": null;

        // Cabinet List
        $cabList = null;
        $quoteCabinets = QuoteCabinet::where('quote_id', $quote->id)->get();
        foreach ($quoteCabinets AS $quoteCabinet)
        {
            $cabinet = Cabinet::find($quoteCabinet->cabinet_id);
            if (!isset($cabinet->frugal_name))
            {
                $cabList .= "Unassigned Cabinet Name";
            }
            else
            {
                $cabList .= "<h4>{$cabinet->frugal_name}</h4>
          <p style='font-size:12px;'>";
            }
            if ($cabinet->override)
            {
                $cabData = unserialize($quoteCabinet->override);
            }
            else
            {
                $cabData = unserialize($quoteCabinet->data);
            }
            $instItems = 0;
            $cabItems = 0;
            $attCount = 0;
            foreach ($cabData AS $item)
            {
                if (!isset($item['attachment']))
                {
                    if (!isset($item['description']))
                    {
                        $item['description'] = null;
                    }

                    $cabList .= "($item[sku]) - $item[description] x " . $item['qty'] . " - $item[price]<br/>";
                    $cabItems += $item['qty'];
                    $instItems += $item['qty']; // Installer items.
                }
                else
                {
                    if (!isset($item['description']))
                    {
                        $item['description'] = null;
                    }

                    $cabList .= "Attachment: ($item[sku]) - $item[description] x " . $item['qty'] . "- $item[price]<br/> ";
                    $attCount += $item['qty'];
                    $cabItems += $item['qty'];
                }
            }
            $cabList = nl2br($cabList) . "</p>";
            if ($cabinet->wood_xml)
            {
                $cabList .= "<h5>Additional Wood Products added to Order</h5>";
                foreach (QuoteGeneratorNew::returnWoodArray($cabinet) as $wood)
                {
                    $cabList .= "($wood[sku]) - $wood[description] x " . $wood['qty'] . " - $wood[price]<br/>";

                }
            }

        }

        $questionData = "<table border=1 cellpadding=1 width='100%'>
        <tr>
        <td><b>Question</b></td><td><b>Answer</b></td>
        </tr>";

        $quoteAnswers = QuestionAnswer::where('quote_id', $quote->id)->get();
        foreach ($quoteAnswers AS $answer)
        {
            $question = Question::find($answer->question_id);
            if (!$question)
            {
                continue;
            }

            if (!$question->active)
            {
                continue;
            }

            if ($answer->answer == 'on')
            {
                $answer->answer = 'Y';
            }

            if (!$question->on_checklist)
            {
                continue;
            }
            $questionData .= "<tr><td>{$question->question}</td><td>{$answer->answer}</td></tr>";
        }
        $questionData .= "</table><br/><br/>";

        $data = "
        <div style='font-size: 12px;'>
        <h4 style='background-color: yellow; border: 2px solid #000000; padding: 10px; text-align: center;'>Customer</h4>
        <div style='text-align:center'>";

        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $data = "
        {$contact->name} <br/>
        {$customer->address} <br/>
        {$customer->city}, {$customer->state} {$customer->zip}<br/>
        M: {$contact->mobile} / H: {$contact->home} / A: {$contact->alternate} <br/>

        <br/>
        <b>Cabinet(s): $cabinets</b>
        <br/>
        </div>
        $check

        <br/><br/>

        <table border='1' cellpadding='4' width='100%'>
        <tr>
            <td><b>Accessory</b></td><td><b>QTY</b></td>
        </tr>
        {$accessoryData}
        </table>
        <br/>
        <table border='1' cellpadding='4' width='100%'>
        <tr>
            <td><b>Hardware</b></td><td><b>QTY</b></td>
        </tr>
        {$hardwareData}
        </table>

        {$special}

        {$questionData}
        {$cabList}

        </div>
        ";


        return $data;
    }

    public function export(Request $request)
    {
        $jobs = Job::whereClosed(true);

        if ($request->has('start_date'))
        {
            $start = Carbon::parse($request->start_date)->toDateString();
            $jobs = $jobs->where('closed_on', '>=', $start);
        }
        $jobs = $jobs->orderBy('closed_on', 'ASC')->get();
        $data = "Job Closed,Customer,Designer\n";
        foreach ($jobs AS $job)
        {
            if (!$job->quote || !$job->quote->lead || !$job->quote->lead->customer) continue;
            $designer = ($job->quote->lead->user) ? $job->quote->lead->user->name : "Unassigned Designer";
            $data .= Carbon::parse($job->closed_on)->format("m/d/y") . "," . $job->quote->lead->customer->name . "," .
                $designer . "\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="jobexp.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    public function getFromContractorNotes(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);

        return Response::json(
          [
            'response' => 'success',
            'contactor_notes' => $schedule->contractor_notes ?: ''
          ]
        );
    }

    public function getInstaller(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);

        return Response::json(
          [
            'response' => 'success',
            'user_id' => ($schedule) ? $schedule->user_id : '0'
          ]
        );
    }

    public function scheduleSaveInstaller(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
        {
            $schedule = new JobSchedule();
            $schedule->job_id = $request->job_id;
            $schedule->group_id = $request->group_id;
        }
        $schedule->user_id = $request->user_id;
        $schedule->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contractor Saved'
          ]
        );
    }

    public function schedules($id, Request $request)
    {
        $job = Job::find($id);
        $customer = $job->quote->lead->customer;
        $start_date = Carbon::parse($job->start_date)->format('m/d/y');

        $cabinetOnlyInstallers = User::get();
        $cabinetInstallerDayOnes = User::whereGroupId(4)->orWhere('id', 60)->get();
        $cabinetDeliveryDayOnes = User::whereGroupId(8)->orWhere('id', 60)->get();
        $cabinetInstallerDayTwos = User::whereGroupId(4)->orWhere('id', 60)->get();
        $graniteInstallerDayTwos = User::whereGroupId(3)->get();
        $graniteInstallerDayFours = User::whereGroupId(3)->orWhere('id', 60)->get();
        $plumberInstallerDayFives = User::whereGroupId(1)->orWhere('id', 60)->get();
        $electricianInstallerDayFives = User::whereGroupId(2)->orWhere('id', 60)->get();
        $tileInstallers = User::whereGroupId(11)->get();
        $fftInstallers = User::whereGroupId(5)->get();
        $contractorInstallers = User::orderBy('name', 'ASC')->get();

        return view('job.schedule', compact(
                                              'customer',
                                              'job',
                                              'start_date',
                                              'cabinetOnlyInstallers',
                                              'cabinetInstallerDayOnes',
                                              'cabinetDeliveryDayOnes',
                                              'cabinetInstallerDayTwos',
                                              'graniteInstallerDayTwos',
                                              'graniteInstallerDayFours',
                                              'plumberInstallerDayFives',
                                              'electricianInstallerDayFives',
                                              'tileInstallers',
                                              'fftInstallers',
                                              'contractorInstallers'
                                            ));
    }

    public function displayJobSchedules(Request $request)
    {
        $job_id = $request->job_id;
        $job = Job::find($job_id);

        $rows = [];
        $data = '';

        // If Cabinet ONLY then we need a special schedule with NO designation
        if ($job->quote->type && $job->quote->type->name == 'Cabinet Only')
        {
        // Cabinet installer - day 1. // 60 is Everyone dude.
            $dayOne = JobSchedule::whereJobId($job->id)->whereGroupId(9)->orderBy('start', 'ASC')->whereAux(false)->first();
            //$installers = User::get();
            $sid = ($dayOne) ? $dayOne->id : 0;
            /* foreach ($installers AS $installer)
            {
              /* $installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                                    ->title("Select Shipper")
                                    ->linkText(isset($dayOne->designation_id) && $dayOne->designation_id ?
                                        $dayOne->user->name : "No Cabinet Shipper Assigned")
                                    ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/1/designation/9")->render(); */
              /* $installers = 'Select Shipper: ';
              $installers .= '<select class="form-control" id="installer_id" name="installer_id">';
              $installers .= '<option value="">No Cabinet Shipper Assigned</option>';
              $installers .= '<option value="' . $installer->id . '">' . $installer->name . '</option>';
              $installers .= '</select>'; */
          /*  } */

            $schedule_id = '';
            if($dayOne) $schedule_id = $dayOne->id;
            $installers = '<a href="#" onclick="ShowModalEditCabinetOnlyInstaller(\'' . $schedule_id . '\', \'Cabinet Shipper\', \'9\');">' . (($dayOne) ? (isset($dayOne->group_id) && $dayOne->group_id ?
            ($dayOne->user ? $dayOne->user->name : "No Cabinet Shipper Assigned") : "No Cabinet Shipper Assigned") . '</a>' : 'No Cabinet Shipper Assigned') . '</a>';

            $color = ($sid && $dayOne->sent) ? $this->colorSuccess : $this->colorInfo;
            $bgColor = ($sid && $dayOne->sent) ? $this->bgColorSuccess : $this->bgColorInfo;
            $startFormat = ($dayOne) ? Carbon::parse($dayOne->start)->format('m/d/y h:i a') : "No Start Set";
            $endFormat = ($dayOne) ? Carbon::parse($dayOne->end)->format('m/d/y h:i a') : "No End Set";
            $startEdit = ($dayOne) ? '<a href="#" onclick="ShowModalEditDate(' . $dayOne->id . ', \'start\')">' . $startFormat . '</a>' : null;
            $endEdit = ($dayOne) ? '<a href="#" onclick="ShowModalEditDate(' . $dayOne->id . ', \'end\')">' . $endFormat . '</a>' : null;
            $start = ($dayOne) ? $startEdit : "Set Contractor First";
            $end = ($dayOne) ? $endEdit : "Set Contractor First";
            $checked = ($dayOne && $dayOne->complete) ? "<i class='fa fa-check text-success'></i>" : null;
            $send = ($dayOne) ? "<span class='pull-right'>" . '<a href="#" onclick="ShowModalCloseContractor(' . $dayOne->id . ');" class="btn btn-success btn-sm"><i class="fa fa-exclamation"></i> Close Contractor</a>' . "</span>" : null;

            if (!$dayOne)
            {
                $default = "No schedule found";
            }
            else
            {
                $default = $dayOne && $dayOne->default_email ?
                    '<a href="/schedule/' . $dayOne->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled<a>' :
                    '<a href="/schedule/' . $dayOne->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled<a>';
            }
            $rows[] = ['<font color="' . $color . '"><h3>1 ' . $checked . '</h3></font>', '<font color="' . $color . '">Cabinet Shipping Manager</font>', $default, $installers . $send, $start, $end, null, null, null];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        } // only if cabinet only

        // Cabinet installer - day 1.
        $rows = [];
        $dayOne = JobSchedule::whereJobId($job->id)->whereGroupId(4)->orderBy('start', 'ASC')->whereAux(false)->first();
        //$installers = User::whereGroupId(4)->orWhere('id', 60)->get();
        $opts = [];
        $sid = ($dayOne) ? $dayOne->id : 0;

        /* $installers = Editable::init()->id("idDe_$job->id")->placement('right')->type('select')
                              ->title("Select Cabinet Installer")
                              ->linkText(isset($dayOne->designation_id) && $dayOne->designation_id ?
                                  $dayOne->user->name : "No Cabinet Installer Assigned")
                              ->source($opts)->url("/job/{$job->id}/schedule/$sid/day/1/designation/4")->render();
        */
        $color = ($sid && $dayOne->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $dayOne->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        $schedule_id = '';
        if($dayOne) $schedule_id = $dayOne->id;
        $installers = '<a href="#" onclick="ShowModalEditCabinetInstallerDayOne(\'' . $schedule_id . '\', \'Cabinet Installer\', \'4\');">' . (($dayOne) ? (isset($dayOne->group_id) && $dayOne->group_id ?
        ($dayOne->user ? $dayOne->user->name : "No Cabinet Installer Assigned") : "No Cabinet Installer Assigned") . '</a>' : 'No Cabinet Installer Assigned') . '</a>';

        $notes = ($dayOne) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $dayOne->id . ');">' . (($dayOne->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($dayOne) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $dayOne->id . ');">' . (($dayOne->customer_notes) ?: "No Notes") . '</a>' : null;

        $send = ($dayOne) ? "<span class='pull-right'>" . '<a href="/schedule/' . $dayOne->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $dayOne->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;
        $locked = ($dayOne) ? $dayOne->locked ? "<a href='/schedule/" . $dayOne->id . "/lock'><i class='fa fa-lock'></i></a>" : "<a href='/schedule/" . $dayOne->id . "/lock'><i class='fa fa-unlock'></i></a>" : null;
        $startFormat = ($dayOne) ? Carbon::parse($dayOne->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($dayOne) ? Carbon::parse($dayOne->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($dayOne) ? '<a href="#" onclick="ShowModalEditDate(' . $dayOne->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($dayOne) ? '<a href="#" onclick="ShowModalEditDate(' . $dayOne->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($dayOne) ? $startEdit : "Set Contractor First";
        $end = ($dayOne) ? $endEdit : "Set Contractor First";
        $checked = ($dayOne && $dayOne->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $cnotes = ($dayOne) ? $dayOne->contractor_notes : null;
        if ($dayOne)
        {
            $default = ($dayOne && $dayOne->default_email) ?
                '<a href="/schedule/' . $dayOne->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $dayOne->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        else $default = null;
        if ($job->quote->type->name == 'Full Kitchen' || $job->quote->type->name == 'Cabinet and Install' || $job->quote->type->name == 'Builder')
        {
            $rows[] = ['<font color="' . $color . '"><h3>1 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Cabinet Installer</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Cabinet Delivery - day 1.
        $rows = [];
        $delivery = JobSchedule::whereJobId($job->id)->whereGroupId(8)->orderBy('start', 'ASC')->whereAux(false)->first();
        //$installers = User::whereGroupId(8)->orWhere('id', 60)->get();
        $sid = ($delivery) ? $delivery->id : 0;
        $color = ($sid && $delivery->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $delivery->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        $schedule_id = '';
        if($delivery) $schedule_id = $delivery->id;
        $installers = '<a href="#" onclick="ShowModalEditCabinetDeliveryDayOne(\'' . $schedule_id . '\', \'Cabinet Delivery\', \'8\');">' . (($delivery) ? (isset($delivery->group_id) && $delivery->group_id ?
        ($delivery->user ? $delivery->user->name : "No Cabinet Delivery Assigned") : "No Cabinet Delivery Assigned") . '</a>' : 'No Cabinet Delivery Assigned') . '</a>';

        $notes = ($delivery) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $delivery->id . ');">' . (($delivery->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($delivery) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $delivery->id . ');">' . (($delivery->customer_notes) ?: "No Notes") . '</a>' : null;

        $send = ($delivery) ? "<span class='pull-right'>" . '<a href="/schedule/' . $delivery->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $delivery->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $locked = ($delivery) ? $delivery->locked ? "<a href='/schedule/" . $delivery->id . "/lock'><i class='fa fa-lock'></i></a>" : "<a href='/schedule/" . $delivery->id . "/lock'><i class='fa fa-unlock'></i></a>" : null;

        $startFormat = ($delivery) ? Carbon::parse($delivery->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($delivery) ? Carbon::parse($delivery->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($delivery) ? '<a href="#" onclick="ShowModalEditDate(' . $delivery->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($delivery) ? '<a href="#" onclick="ShowModalEditDate(' . $delivery->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($delivery) ? $startEdit : "Set Contractor First";
        $end = ($delivery) ? $endEdit : "Set Contractor First";
        $checked = ($delivery && $delivery->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $cnotes = ($delivery) ? $delivery->contractor_notes : null;
        if ($delivery)
        {
            $default = ($delivery && $delivery->default_email) ?
                '<a href="/schedule/' . $delivery->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $delivery->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        else $default = null;
        if ($job->quote->type->name == 'Full Kitchen' || $job->quote->type->name == 'Cabinet and Install' || $job->quote->type->name == 'Builder')
        {
            $rows[] = ['<font color="' . $color . '"><h3>1 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Cabinet Delivery</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Cabinet installer - day 2.
        $rows = [];
        if ($dayOne)
        {
            $dayTwo = JobSchedule::whereJobId($job->id)->whereGroupId(4)->where('start', '!=', $dayOne->start)
                                 ->whereAux(false)->first();
        }
        else
        {
            $dayTwo = null;
        }
        if ($dayTwo && $dayTwo->start == $dayOne->start)
        {
            unset($dayTwo);
        } // There's only one schedule for a cabinet so day 2 isn't set.
        //$installers = User::whereGroupId(4)->orWhere('id', 60)->get();
        $sid = ($dayTwo) ? $dayTwo->id : 0;
        $color = ($sid && $dayTwo->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $dayTwo->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        $schedule_id = '';
        if($dayTwo) $schedule_id = $dayTwo->id;
        $installers = '<a href="#" onclick="ShowModalEditCabinetInstallerDayTwo(\'' . $schedule_id . '\', \'Cabinet Installer\', \'4\');">' . (($dayTwo) ? (isset($dayTwo->group_id) && $dayTwo->group_id ?
        ($dayTwo->user ? $dayTwo->user->name : "No Cabinet Installer Assigned") : "No Cabinet Installer Assigned") . '</a>' : 'No Cabinet Installer Assigned') . '</a>';

        $startFormat = ($dayTwo) ? Carbon::parse($dayTwo->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($dayTwo) ? Carbon::parse($dayTwo->end)->format('m/d/y h:i a') : "No End Set";

        $notes = ($dayTwo) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $dayTwo->id . ');">' . (($dayTwo->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($dayTwo) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $dayTwo->id . ');">' . (($dayTwo->customer_notes) ?: "No Notes") . '</a>' : null;

        $send = ($dayTwo) ? "<span class='pull-right'>" . '<a href="/schedule/' . $dayTwo->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $dayTwo->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $startEdit = ($dayTwo) ? '<a href="#" onclick="ShowModalEditDate(' . $dayTwo->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($dayTwo) ? '<a href="#" onclick="ShowModalEditDate(' . $dayTwo->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($dayTwo) ? $startEdit : "Set Contractor First";
        $end = ($dayTwo) ? $endEdit : "Set Contractor First";
        $default = null;

        $locked = ($dayTwo) ? $dayTwo->locked ? "<a href='/schedule/" . $dayTwo->id . "/lock'><i class='fa fa-lock'></i></a>" : "<a href='/schedule/" . $dayTwo->id . "/lock'><i class='fa fa-unlock'></i></a>" : null;

        if ($dayTwo)
        {
            $default = ($dayTwo && $dayTwo->default_email) ?
                '<a href="/schedule/' . $dayTwo->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $dayTwo->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }

        if ($job->quote->type->name == 'Full Kitchen' || $job->quote->type->name == 'Cabinet and Install' || $job->quote->type->name == 'Builder')
        {
            $rows[] = ['<font color="' . $color . '"><h3>2 ' . $locked . '</h3></font>', '<font color="' . $color . '">Cabinet Installer</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, null];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Granite installer - day 2.
        $rows = [];
        $granite = JobSchedule::whereJobId($job->id)->whereGroupId(3)->orderBy('start', 'ASC')->whereAux(false)->first();
        $sid = ($granite) ? $granite->id : 0;
        $color = ($sid && $granite->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $granite->sent) ? $this->bgColorSuccess : $this->bgColorInfo;
        //$installers = User::whereGroupId(4)->get();

        $schedule_id = '';
        if($granite) $schedule_id = $granite->id;
        $installers = '<a href="#" onclick="ShowModalEditGraniteInstallerDayTwo(\'' . $schedule_id . '\', \'Granite Company\', \'3\');">' . (($granite) ? (isset($granite->group_id) && $granite->group_id ?
        ($granite->user ? $granite->user->name : "No Granite Company Assigned") : "No Granite Company Assigned") . '</a>' : 'No Granite Company Assigned') . '</a>';

        $notes = ($granite) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $granite->id . ');">' . (($granite->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($granite) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $granite->id . ');">' . (($granite->customer_notes) ?: "No Notes") . '</a>' : null;

        $send = ($granite) ? "<span class='pull-right'>" . '<a href="/schedule/' . $granite->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $granite->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $locked = ($granite) ? $granite->locked ? "<a href='/schedule/" . $granite->id . "/lock'><i class='fa fa-lock'></i></a>" : "<a href='/schedule/" . $granite->id . "/lock'><i class='fa fa-unlock'></i></a>" : null;

        $startFormat = ($granite) ? Carbon::parse($granite->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($granite) ? Carbon::parse($granite->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($granite) ? '<a href="#" onclick="ShowModalEditDate(' . $granite->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($granite) ? '<a href="#" onclick="ShowModalEditDate(' . $granite->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($granite) ? $startEdit : "Set Contractor First";
        $end = ($granite) ? $endEdit : "Set Contractor First";
        $checked = ($granite && $granite->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $default = null;
        if ($granite)
        {
            $default = ($granite && $granite->default_email) ?
                '<a href="/schedule/' . $granite->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $granite->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        $cnotes = ($granite) ? $granite->contractor_notes : null;
        if ($job->quote->type->name == 'Full Kitchen' || $job->quote->type->name == 'Granite Only')
        {
            $rows[] = ['<font color="' . $color . '"><h3>2 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Granite Company</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Day 4 - Granite again
        $rows = [];
        if ($granite)
        {
            $dayFour = JobSchedule::whereJobId($job->id)->whereGroupId(3)->where('start', '!=', $granite->start)
                                  ->whereAux(false)->first();
        }
        else $dayFour = null;
        $sid = ($dayFour) ? $dayFour->id : 0;
        $color = ($sid && $dayFour->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $dayFour->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        //$installers = User::whereGroupId(3)->orWhere('id', 60)->get();

        $schedule_id = '';
        if($dayFour) $schedule_id = $dayFour->id;
        $installers = '<a href="#" onclick="ShowModalEditGraniteInstallerDayFour(\'' . $schedule_id . '\', \'Granite Company\', \'3\');">' . (($dayFour) ? (isset($dayFour->group_id) && $dayFour->group_id ?
        ($dayFour->user ? $dayFour->user->name : "No Granite Company Assigned") : "No Granite Company Assigned") . '</a>' : 'No Granite Company Assigned') . '</a>';

        $startFormat = ($dayFour) ? Carbon::parse($dayFour->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($dayFour) ? Carbon::parse($dayFour->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($dayFour) ? '<a href="#" onclick="ShowModalEditDate(' . $dayFour->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($dayFour) ? '<a href="#" onclick="ShowModalEditDate(' . $dayFour->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($dayFour) ? $startEdit : "Set Contractor First";
        $end = ($dayFour) ? $endEdit : "Set Contractor First";
        $locked = ($dayFour) ? $dayFour->locked ? "<a class='get' href='/schedule/$dayFour->id/lock'><i class='fa fa-lock'></i></a>" :
            "<a class='get' href='/schedule/$dayFour->id/lock'><i class='fa fa-unlock'></i></a>" : null;

        $send = ($dayFour) ? "<span class='pull-right'>" . '<a href="/schedule/' . $dayFour->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $dayFour->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $default = null;
        if ($dayFour)
        {
            $default = ($dayFour && $dayFour->default_email) ?
                '<a href="/schedule/' . $dayFour->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $dayFour->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }

        if ($job->quote->type->name == 'Full Kitchen' || $job->quote->type->name == 'Granite Only')
        {
            $rows[] = ['<font color="' . $color . '"><h3>4 ' . $locked . '</h3></font>', '<font color="' . $color . '">Granite Company</font>', $installers . $send, $default, $start, $end, null, null, null];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Day 5 - Plumber
        $rows = [];
        $plumber = JobSchedule::whereJobId($job->id)->whereGroupId(1)->whereAux(false)->first();
        $sid = ($plumber) ? $plumber->id : 0;
        $color = ($sid && $plumber->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $plumber->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        //$installers = User::whereGroupId(1)->orWhere('id', 60)->get();

        $schedule_id = '';
        if($plumber) $schedule_id = $plumber->id;
        $installers = '<a href="#" onclick="ShowModalEditPlumberInstallerDayFive(\'' . $schedule_id . '\', \'Plumber\', \'1\');">' . (($plumber) ? (isset($plumber->group_id) && $plumber->group_id ?
        ($plumber->user ? $plumber->user->name : "No Plumber Assigned") : "No Plumber Assigned") . '</a>' : 'No Plumber Assigned') . '</a>';

        $send = ($plumber) ? "<span class='pull-right'>" . '<a href="/schedule/' . $plumber->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $plumber->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $notes = ($plumber) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $plumber->id . ');">' . (($plumber->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($plumber) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $plumber->id . ');">' . (($plumber->customer_notes) ?: "No Notes") . '</a>' : null;

        $startFormat = ($plumber) ? Carbon::parse($plumber->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($plumber) ? Carbon::parse($plumber->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($plumber) ? '<a href="#" onclick="ShowModalEditDate(' . $plumber->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($plumber) ? '<a href="#" onclick="ShowModalEditDate(' . $plumber->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($plumber) ? $startEdit : "Set Contractor First";
        $end = ($plumber) ? $endEdit : "Set Contractor First";
        $checked = ($plumber && $plumber->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $locked = ($plumber) ? $plumber->locked ? "<a class='get' href='/schedule/$plumber->id/lock'><i class='fa fa-lock'></i></a>" :
            "<a class='get' href='/schedule/$plumber->id/lock'><i class='fa fa-unlock'></i></a>" : null;

        $default = null;
        if ($plumber)
        {
            $default = ($plumber && $plumber->default_email) ?
                '<a href="/schedule/' . $plumber->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $plumber->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        $cnotes = ($plumber) ? $plumber->contractor_notes : null;
        if ($job->quote->type->name == 'Full Kitchen')
        {
            $rows[] = ['<font color="' . $color . '"><h3>5 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Plumber</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // Day 5 - Electrician
        $rows = [];
        $electrician = JobSchedule::whereJobId($job->id)->whereGroupId(2)->whereAux(false)->first();
        $sid = ($electrician) ? $electrician->id : 0;
        $color = ($sid && $electrician->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $electrician->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        //$installers = User::whereGroupId(2)->orWhere('id', 60)->get();

        $schedule_id = '';
        if($electrician) $schedule_id = $electrician->id;
        $installers = '<a href="#" onclick="ShowModalEditElectricianInstallerDayFive(\'' . $schedule_id . '\', \'Electrician\', \'2\');">' . (($electrician) ? (isset($electrician->group_id) && $electrician->group_id ?
        ($electrician->user ? $electrician->user->name : "No Electrician Assigned") : "No Electrician Assigned") . '</a>' : 'No Electrician Assigned') . '</a>';

        $send = ($electrician) ? "<span class='pull-right'>" . '<a href="/schedule/' . $electrician->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $electrician->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $notes = ($electrician) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $electrician->id . ');">' . (($electrician->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($electrician) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $electrician->id . ');">' . (($electrician->customer_notes) ?: "No Notes") . '</a>' : null;

        $startFormat = ($electrician) ? Carbon::parse($electrician->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($electrician) ? Carbon::parse($electrician->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($electrician) ? '<a href="#" onclick="ShowModalEditDate(' . $electrician->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($electrician) ? '<a href="#" onclick="ShowModalEditDate(' . $dayOne->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($electrician) ? $startEdit : "Set Contractor First";
        $end = ($electrician) ? $endEdit : "Set Contractor First";
        $locked = ($electrician) ? $electrician->locked ? "<a class='get' href='/schedule/$electrician->id/lock'><i class='fa fa-lock'></i></a>" :
            "<a class='get' href='/schedule/$electrician->id/lock'><i class='fa fa-unlock'></i></a>" : null;

        $checked = ($electrician && $electrician->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $default = null;
        if ($electrician)
        {
            $default = ($electrician && $electrician->default_email) ?
                '<a href="/schedule/' . $electrician->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $electrician->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        $cnotes = ($electrician) ? $electrician->contractor_notes : null;
        if ($job->quote->type->name == 'Full Kitchen')
        {
            $rows[] = ['<font color="' . $color . '"><h3>5 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Electrician</font>', $installers . $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // ------------ TILE JOB ------------------ //
        $rows = [];
        if ($job->quote->tiles()->count() > 0) // Got a tile job..
        {
            $contractor = JobSchedule::whereJobId($job->id)->whereGroupId(11)->whereAux(false)->first();
            $sid = ($contractor) ? $contractor->id : 0;
            $color = ($sid && $contractor->sent) ? $this->colorSuccess : $this->colorInfo;
            $bgColor = ($sid && $contractor->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

            // $contractors = User::whereGroupId(11)->get();

            $schedule_id = '';
            if($contractor) $schedule_id = $contractor->id;
            $contractors = '<a href="#" onclick="ShowModalEditTileInstaller(\'' . $schedule_id . '\', \'Flooring Contractor\', \'11\');">' . (($contractor) ? (isset($contractor->group_id) && $contractor->group_id ?
            ($contractor->user ? $contractor->user->name : "No Flooring Contractor Assigned") : "No Flooring Contractor Assigned") . '</a>' : 'No Flooring Contractor Assigned') . '</a>';

            $send = ($contractor) ? "<span class='pull-right'>" . '<a href="/schedule/' . $contractor->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
            '<a href="#" onclick="ShowModalCloseContractor(' . $contractor->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

            $notes = ($contractor) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $contractor->id . ');">' . (($contractor->notes) ?: "No Notes") . '</a>' : null;

            $customerNotes = ($contractor) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $contractor->id . ');">' . (($contractor->customer_notes) ?: "No Notes") . '</a>' : null;

            $startFormat = ($contractor) ? Carbon::parse($contractor->start)->format('m/d/y h:i a') : "No Start Set";
            $endFormat = ($contractor) ? Carbon::parse($contractor->end)->format('m/d/y h:i a') : "No End Set";
            $startEdit = ($contractor) ? '<a href="#" onclick="ShowModalEditDate(' . $contractor->id . ', \'start\')">' . $startFormat . '</a>' : null;
            $endEdit = ($contractor) ? '<a href="#" onclick="ShowModalEditDate(' . $contractor->id . ', \'end\')">' . $endFormat . '</a>' : null;
            $start = ($contractor) ? $startEdit : "Set Contractor First";
            $end = ($contractor) ? $endEdit : "Set Contractor First";
            $locked = ($contractor) ? $contractor->locked ? "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-lock'></i></a>" :
                "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-unlock'></i></a>" : null;
            $checked = ($contractor && $contractor->complete) ? "<i class='fa fa-check text-success'></i>" : null;
            $default = null;
            if ($contractor)
            {
                $default = ($contractor && $contractor->default_email) ?
                    '<a href="/schedule/' . $contractor->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $contractor->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
            }
            $cnotes = ($contractor) ? $contractor->contractor_notes : null;
            if ($job->quote->type->name == 'Full Kitchen')
            {
                $rows[] = ['<font color="' . $color . '"><h3>6 ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">Flooring Contractor</font>', $contractors. $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

                $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
            }
        }

        // Day 6 - Walkthrough FFT Contractor(5)
        $rows = [];
        $day = $job->quote->tiles()->count() > 0 ? 8 : 7;

        $contractor = JobSchedule::whereJobId($job->id)->whereGroupId(5)->whereAux(false)->first();
        $sid = ($contractor) ? $contractor->id : 0;
        $color = ($sid && $contractor->sent) ? $this->colorSuccess : $this->colorInfo;
        $bgColor = ($sid && $contractor->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

        //$contractors = User::whereGroupId(5)->get();

        $schedule_id = '';
        if($contractor) $schedule_id = $contractor->id;
        $contractors = '<a href="#" onclick="ShowModalEditFftInstaller(\'' . $schedule_id . '\', \'FFT Contractor\', \'5\');">' . (($contractor) ? (isset($contractor->group_id) && $contractor->group_id ?
        ($contractor->user ? $contractor->user->name : "No FFT Contractor Assigned") : "No FFT Contractor Assigned") . '</a>' : 'No FFT Contractor Assigned') . '</a>';

        $send = ($contractor) ? "<span class='pull-right'>" . '<a href="/schedule/' . $contractor->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
        '<a href="#" onclick="ShowModalCloseContractor(' . $contractor->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

        $notes = ($contractor) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $contractor->id . ');">' . (($contractor->notes) ?: "No Notes") . '</a>' : null;

        $customerNotes = ($contractor) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $contractor->id . ');">' . (($contractor->customer_notes) ?: "No Notes") . '</a>' : null;

        $startFormat = ($contractor) ? Carbon::parse($contractor->start)->format('m/d/y h:i a') : "No Start Set";
        $endFormat = ($contractor) ? Carbon::parse($contractor->end)->format('m/d/y h:i a') : "No End Set";
        $startEdit = ($contractor) ? '<a href="#" onclick="ShowModalEditDate(' . $contractor->id . ', \'start\')">' . $startFormat . '</a>' : null;
        $endEdit = ($contractor) ? '<a href="#" onclick="ShowModalEditDate(' . $contractor->id . ', \'end\')">' . $endFormat . '</a>' : null;
        $start = ($contractor) ? $startEdit : "Set Contractor First";
        $end = ($contractor) ? $endEdit : "Set Contractor First";
        $locked = ($contractor) ? $contractor->locked ? "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-lock'></i></a>" :
            "<a class='get' href='/schedule/$contractor->id/lock'><i class='fa fa-unlock'></i></a>" : null;

        $checked = ($contractor && $contractor->complete) ? "<i class='fa fa-check text-success'></i>" : null;
        $default = null;
        if ($contractor)
        {
            $default = ($contractor && $contractor->default_email) ?
                '<a href="/schedule/' . $contractor->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $contractor->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
        }
        $cnotes = ($contractor) ? $contractor->contractor_notes : null;
        if ($job->quote->type->name == 'Full Kitchen')
        {
            $rows[] = ['<font color="' . $color . '"><h3>' . $day . ' ' . $checked . $locked . '</h3></font>', '<font color="' . $color . '">FFT Contractor</font>', $contractors. $send, $default, $start, $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        // #20 - Auxillary schedule days.
        $rows = [];
        $auxs = JobSchedule::whereJobId($job->id)->whereAux(true)->get();
        $everyone = User::orderBy('name', 'ASC')->get();

        /* foreach ($everyone AS $errbody)
            $opts[] = ['value' => $errbody->id, 'text' => $errbody->name]; */

        foreach ($auxs AS $aux)
        {
            $rows = [];
            $contractors = '<a href="#" onclick="ShowModalEditContractorInstaller(' . $aux->id . ', \'Contractor\');">' . (($aux->user) ? ($aux->user ? $aux->user->name : "No Contractor Assigned") : "No Contractor Assigned") . '</a>';

            $color = ($aux->sent) ? $this->colorSuccess : $this->colorInfo;
            $bgColor = ($aux->sent) ? $this->bgColorSuccess : $this->bgColorInfo;

            $send = ($aux->start != '0000-00-00 00:00:00') ? "<span class='pull-right'>" . '<a href="/schedule/' . $aux->id . '/send" class="btn btn-warning btn-sm"><i class="fa fa-exclamation"></i> Send Notification</a>' .
            '<a href="#" onclick="ShowModalCloseContractor(' . $aux->id . ');" class="btn btn-success btn-sm"><i class="fa fa-check"></i> Close Contractor</a>' : null;

            $trash = "<span class='pull-right'><a href='/schedule/$aux->id/delete'><i class='fa fa-trash-o'></i></a></span>";

            $notes = ($aux) ? '<a href="#" onclick="ShowModalEditScheduleNotes(' . $aux->id . ');">' . (($aux->notes) ?: "No Notes") . '</a>' : null;

            $customerNotes = ($aux) ? '<a href="#" onclick="ShowModalEditCustomerNotes(' . $aux->id . ');">' . (($aux->customer_notes) ?: "No Notes") . '</a>' : null;

            $startFormat = ($aux->start == '0000-00-00 00:00:00' ||  is_null($aux->start)) ? "No Start Set" : Carbon::parse($aux->start)->format('m/d/y h:i a');
            $endFormat = ($aux->end == '0000-00-00 00:00:00' ||  is_null($aux->end)) ? "No End Set" : Carbon::parse($aux->end)->format('m/d/y h:i a');
            $startEdit = ($aux) ? '<a href="#" onclick="ShowModalEditDate(' . $aux->id . ', \'start\')">' . $startFormat . '</a>' : null;
            $endEdit = ($aux) ? '<a href="#" onclick="ShowModalEditDate(' . $aux->id . ', \'end\')">' . $endFormat . '</a>' : null;
            $start = ($aux) ? $startEdit : "Set Contractor First";
            $end = ($aux) ? $endEdit : "Set Contractor First";
            $checked = ($aux && $aux->complete) ? "<i class='fa fa-check text-success'></i>" : null;
            $default = null;
            $locked = ($aux) ? $aux->locked ? "<a class='get' href='/schedule/$aux->id/lock'><i class='fa fa-lock'></i></a>" :
                "<a class='get' href='/schedule/$aux->id/lock'><i class='fa fa-unlock'></i></a>" : null;

            if ($aux)
            {
                $default = ($aux && $aux->default_email) ?
                    '<a href="/schedule/' . $aux->id . '/default" class="btn btn-success"><i class="fa fa-check"></i> Default Enabled</a>' : '<a href="/schedule/' . $aux->id . '/default" class="btn btn-danger"><i class="fa fa-times"></i> Default Disabled</a>';
            }
            $cnotes = ($aux) ? $aux->contractor_notes : null;
            $rows[] = [$trash . $checked . $locked, '<font color="' . $color . '">' . (($aux->group) ? $aux->group->name : "No Designation") . '</font>', $contractors . $send, $default, $start,
                $end, $notes, $customerNotes, $cnotes];

            $data .= Formatter::GenerateTableRows($rows, 9, $bgColor);
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );

    }

    public function createAuxSchedule(Request $request)
    {
        $schedule = new JobSchedule;
        $schedule->job_id = $request->job_id;
        $schedule->user_id = 0;
        $schedule->aux = 1;
        $schedule->locked = 1;
        $schedule->default_email = 1;
        $schedule->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Additional Schedule Added.'
          ]
        );
    }

    public function close(Request $request)
    {
        $job = Job::find($request->job_id);
        $job->closed = 1;
        $job->closed_on = Carbon::now();
        $job->save();

        $exists = Fft::whereJobId($job->id)->count();
        if ($exists == 0)
        {
            $fft = new Fft();
            $fft->job_id = $job->id;
            $fft->user_id = 0;
            $fft->customer_id = 0;
            $fft->warranty = 0;
            $fft->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Job Closed.',
            'url' => route('jobs.index')
          ]
        );
    }

    public function lockToggle($id, Request $request)
    {
        $schedule = JobSchedule::find($id);
        $schedule->locked = ($schedule->locked) ? 0 : 1;
        $schedule->save();

        return redirect()->back()->with('success', 'Schedule ' .  (($schedule->locked) ? 'Locked' : 'Unlocked'));
    }

    public function scheduleDelete($id, Request $request)
    {
        JobSchedule::find($id)->delete();
        return redirect()->back()->with('success', 'Schedule Deleted.');
    }

    public function scheduleSend($id)
    {
        $schedule = JobSchedule::find($id);
        if(!$schedule->job->quote->lead->customer)
        {
              return redirect()->back()->with('error', 'Schedule Not Send because no Customer yet.');
        }

        ScheduleEngine::send($schedule);
        switch ($schedule->group_id)
        {
            case 3 :
                $allSets = JobSchedule::whereJobId($schedule->job->id)->whereGroupId($schedule->group->id)
                    ->get();
                foreach ($allSets AS $set)
                {
                    $set->sent = 1;
                    $set->save();
                }
        }
        $schedule->sent = 1;
        $schedule->save();
        return redirect()->back()->with('success', 'Schedule Sent.');
    }

    public function scheduleClose(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);
        $schedule->contractor_notes = $request->notes;
        $schedule->complete = 1;
        $schedule->save();
        JobBoard::checkSchedulesForClosing($schedule->job);

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule Closed'
          ]
        );
    }

    public function defaultEmail($id, Request $request)
    {
        $schedule = JobSchedule::find($id);
        $schedule->default_email = ($schedule->default_email) ? 0 : 1;
        $schedule->save();

        return redirect()->back()->with('success', 'Default Email ' . ($schedule->default_email ? 'Disabled' : 'Enabled'));
    }

    /**
     * Get Schedule Date
     * @return json
     */
    public function getScheduleDate(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
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
            if(empty($schedule->{$request->type}) || is_null($schedule->{$request->type}))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($schedule->{$request->type})->format('m/d/Y');
            $timeFormat = Carbon::parse($schedule->{$request->type})->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    /**
     * Set Schedule Date
     * @return json
     */
    public function setScheduleDate(Request $request)
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

        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $schedule->{$request->type} = $dateFormat . ' ' . $timeFormat;
        $schedule->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    /**
     * Get Schedule Notes
     * @return json
     */
    public function getScheduleNotes(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
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
            return Response::json(
              [
                'response' => 'success',
                'notes' => $schedule->notes
              ]
            );
        }
    }

    /**
     * Set Schedule Notes
     * @return json
     */
    public function setScheduleNotes(Request $request)
    {
        $notes = $request->notes;

        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $schedule->notes = $notes;
        $schedule->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule Notes set.'
          ]
        );
    }

    /**
     * Get Schedule Customer Notes
     * @return json
     */
    public function getScheduleCustomerNotes(Request $request)
    {
        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
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
            return Response::json(
              [
                'response' => 'success',
                'customer_notes' => $schedule->customer_notes
              ]
            );
        }
    }

    /**
     * Set Schedule Customer Notes
     * @return json
     */
    public function setScheduleCustomerNotes(Request $request)
    {
        $customer_notes = $request->customer_notes;

        $schedule = JobSchedule::find($request->schedule_id);
        if(!$schedule)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $schedule->customer_notes = $customer_notes;
        $schedule->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule Customer Notes set.'
          ]
        );
    }

    public function xmlSave($id, Request $request)
    {
        $job = Job::find($id);
        foreach (Input::all() AS $key => $val)
        {
            if (preg_match("/c_/", $key))
            {
                $id = trim(str_replace("c_", null, $key));
                $file = Input::file("cabinet_{$id}");
                if ($file)
                {
                    $xml = file_get_contents($file->getRealPath());
                    QuoteGeneratorNew::setCabinetData($job->quote, $xml, $id);

                    //update last_xml_override
                    $job->last_override_xml = Carbon::now()->format('Y-m-d H:i:s');
                    $job->save();
                }
            }
        }
        return redirect(route('jobs.index'))->with('success', 'XML Overriden');
    }

    public function getOverrideXmlData($job_id)
    {
        $job = Job::find($job_id);

        $fields = '';
        foreach ($job->quote->cabinets AS $cabinet)
        {
           $fields .= '<label for="file" class="col-md-4 control-label">' . $cabinet->cabinet->frugal_name . ' Override:</label><div class="col-md-8"><input type="file" class="form-control" name="cabinet_' . $cabinet->id . '" id="cabinet_' . $cabinet->id . '" required><p class="help-block mt-1 text-muted" style="font-size: 12px;">' . $cabinet->description . '</p></div><input type="hidden" name="c_' . $cabinet->id . '" val="Y" />';
        }

        return Response::json(
          [
            'response' => 'success',
            'fields' => $fields,
            'last_override' => $job->last_override_xml
          ]
        );
    }

    /**
     * Set Start Date
     * @return json
     */
    public function setStartDate(Request $request)
    {
        $date = $request->date;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
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

        $job = Job::find($request->job_id);
        if(!$job)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $job->start_date = $dateFormat;
        $job->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Start Date set.',
            'data' => $job->start_date
          ]
        );
    }

    /**
     * Get Start Date
     * @return json
     */
    public function getStartDate(Request $request)
    {
        $job = Job::find($request->job_id);
        if(!$job)
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
            return Response::json(
              [
                'response' => 'success',
                'start_date'  => ($job->start_date == '0000-00-00') ? "" : $job->start_date
              ]
            );
        }
    }

    public function markPaid($id)
    {
        $job = Job::find($id);
        $job->paid = 1;
        $job->save();

        return redirect()->back()->with('success', 'Job Archived');
    }

    public function review($id)
    {
        $job = Job::find($id);
        $job->reviewed = Carbon::now();
        $job->save();

        // #366 - Send email to customer with attachment of list of items they are responsible for for their job.
        $emailMsg = '';
        if ($job->quote->responsibilities()->count() > 0)
        {
          libxml_use_internal_errors(true);

          $file = uniqid();
          $data = View::make('emails.rpdf')->withJob($job)->render();
          PDF::loadHTML($data)->setPaper('a4', 'portrait')->setWarnings(false)->save(public_path() . '/tmp/' . $file . '.pdf');
          // Render a PDF as as an attachment.
          Mail::send('emails.responsibilities', ['job' => $job], function ($message) use ($job, $file) {
              $message->to($job->quote->lead->customer->contacts->first()->email);
              $message->attach(public_path() . '/tmp/' . $file . '.pdf');
              $message->subject("Thank you for choosing Frugal Kitchens and Cabinets!");
          });
          @unlink(public_path() . '/tmp/' . $file . '.pdf');

          $emailMsg = ' Email sent.';
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Job Review Set.' . $emailMsg
          ]
        );

    }

    public function unlock($id)
    {
        $job = Job::find($id);
        $job->locked = 0;
        $job->save();
        return redirect()->back()->with('success', 'Job Unlocked');
    }

    public function sendSchedules($id, Request $request)
    {
        $job = Job::find($id);
        $body = View::make('emails.schedules')->withJob($job)->render();
        $users = User::where('active', '1')->orderBy('name')->get();

        return view('job.send_schedule', compact('body', 'job', 'users'));
    }

    /**
     * Send the email to the customer with the schedules.
     *
     * @param $id
     * @return null
     */

    public function finalSendSchedules($id, Request $request)
    {
        if ($request->has('user_id') && $request->user_id > 0)
        {
            $email = User::find($request->user_id)->email;
        }
        else
        {
            $email = $request->email;
        }
        if(ScheduleEngine::sendSchedulesTocustomer(Job::find($id), $request->body, $email) == 'no customer')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Can\'t send job schedule, because no customer.'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Job Schedule sent.'
          ]
        );
    }

    public function construction($id)
    {
        $job = Job::find($id);
        $job->construction = 1;
        $job->save();

        $quote = Quote::find($job->quote_id);
        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Quote Not found. Can\'t send email.'
              ]
            );
        }

        $lead = Lead::find($quote->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Lead Not found. . Can\'t send email.'
              ]
            );
        }
        $customer = Customer::find($lead->customer_id);
        if(!$customer)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Customer Not found. . Can\'t send email.'
              ]
            );
        }

        $contact = Contact::where('customer_id', $customer->id)->first();
        if(!$contact)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Arrival Set. Contact Not found. . Can\'t send email.'
              ]
            );
        }

        // Now we should probably send it.
        $subject = "[Frugal Kitchens/$customer->name] Construction Verification!";
        $data = [
            'customer' => $customer,
            'contact'  => $contact,
            'content'  => "Hi $contact->name, This is construction verification body message"
        ];
        @Mail::send('emails.notification', $data, function ($message) use ($contact, $subject) {
            $message->to([
                'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject($subject);
        });

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Job Construction set. Email sent to orders@frugalkitchens.com'
          ]
        );
    }
}
