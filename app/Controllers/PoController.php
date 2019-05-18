<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\Quote;
use FK3\Models\Fft;
use FK3\Models\Po;
use FK3\Models\PoItem;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Customer;
use FK3\Models\Vendor;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class PoController extends Controller
{
    public $auditPage = "Purchase Order";

    /*
     * Show Task Index.
     */
    public function index(Request $request)
    {
        $customers = Customer::orderBy('id', 'asc')->get();
        $vendors = Vendor::orderBy('name', 'asc')->get();

        return view('po.index', compact('customers', 'vendors', 'punchData'));
    }

    public function displayPo(Request $request)
    {
        $old = $request->old;

        if($old == 'true')
        {
            $pos = Po::all();
        }
        else
        {
            $pos = Po::where('parent_id', '0')
                      ->orderBy('submitted', 'ASC')
                      ->get();
        }

        $newItems = array();
        $total = 0;
        if ( !empty( $pos ) )
        {
            $totalPrice = 0;
            foreach ( $pos as $po )
            {
              $objItems = array();

              if ($old == 'true')
              {
                  $newItems[] = $this->render($po, $po->parent_id);

                  $children = Po::where('id', $po->parent_id)->get();
                  foreach ($children as $child)
                  {
                      if (!$child->archived)
                          $newItems[] = $this->render($child, $child->parent_id);
                  }
              }
              else
              {
                  if (!$po->archived)
                  {
                      $newItems[] = $this->render($po, $po->parent_id);
                  }
                  $childrenCount = Po::where('id', $po->parent_id)->count();
                  if ($childrenCount > 0)
                  {
                      $children = Po::where('id', $po->parent_id)->get();
                      foreach ($children as $child)
                      {
                          if (!$child->archived)
                          {
                            $newItems[] = $this->render($child, $child->parent_id);
                          }
                      }
                  }
              }

              $total++;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function render($po, $child = false)
    {
        $objItems = array();

        $color = $child ? 'color-primary' : null;
        $ordered = Carbon::parse($po->submitted);
        if ($ordered->timestamp > 0)
        {
            $ordered = $ordered->format("m/d/y");
        }
        else $ordered = "<span class='btn btn-danger'>Not Ordered</span>";

        $status = '<span id="span_status_pri_' . $po->id . '">' . $po->status . '</span>';
        if ($po->status == 'draft')
        {
            $status .= "<span id='span_status_sec_" . $po->id . "' class='pull-right'> (<a id='a_status_" . $po->id . "' href='#' onclick='OrderPo(" . $po->id . ");'>order</a>)</span>";
        }
        else
        {
            if ($po->status == 'ordered')
            {
                $status .= "<span id='span_status_sec_" . $po->id . "' class='pull-right'> (<a id='a_status_" . $po->id . "' href='#' onclick='ConfirmPo(" . $po->id . ");'>confirm</a>)</span>";
            }
        }
        if ($status == 'complete')
        {
            $status = "<span style='color: #25fe29;'>COMPLETE</span>";
        }

        $job = Job::find($po->job_id);
        $quote = 0;
        if($job) $quote = Quote::find($job->quote_id);
        if ($job && $quote)
        {
            $files = "<span class='pull-right'><a data-toggle='tooltip' data-placement='right'
                    title='Drawings' data-toggle='modal'
                    href='#' onclick='ShowModalDrawing(" . $quote->id . ")'><i class='fa fa-image'></i></a></span>";
        }
        else $files = null;
        $delete = "<span class='pull-right'>";

        $poItemsCount = PoItem::where('po_id', $po->id)->count();
        $delete .= ($poItemsCount == 0) ? "<span class='pull-right'>
                  <a href='#' data-toggle='tooltip' data-placement='right'
                    title='Delete PO' onclick='ShowModalDeleteConfirm(". $po->id . ")'><i class='fa fa-times'></i></a> &nbsp; &nbsp; " : null;
        $delete .= (Auth::user()->id == 5 || Auth::user()->id == 1 || Auth::user()->id == 7) && !$po->archived
            ? " <a href='#' onclick='ShowModalArchiveConfirm(". $po->id . ")'><i class='fa fa-eraser'></i></a>"
            : null;
        $delete .= "</span>";

        if (!$po->company_invoice) $po->company_invoice = 'empty';
        if (!$po->projected_ship) $po->projected_ship = 'empty';

        $objItems[] = "$color <a href='/po/$po->id'>$po->number</a>" . $files;
        $objItems[] = "<a href='/po/$po->id'>$po->title</a> {$delete}";

        $customer = Customer::find($po->customer_id);
        $objItems[] =   ($customer) ? "<a href='/profile/{$customer->id}/view'>{$customer->name}</a>" : "Internal Purchase Order";

        $vendor = Vendor::find($po->vendor_id);
        $objItems[] = ($vendor) ? $vendor->name : "Unknown Vendor";
        $objItems[] = '<a href="#" onclick="ShowModalType(' . $po->id . ')" id="po_type_' . $po->id . '">' . $po->type . '</a>';

        $poUser = User::find($po->user_id);
        $objItems[] = ($poUser) ? $poUser->name : "System";
        $objItems[] = $status;
        $objItems[] = $ordered;
        $objItems[] = '<a href="#" onclick="ShowModalCompanyInvoice(' . $po->id . ')" id="po_company_invoice_' . $po->id . '">' . $po->company_invoice . '</a>';
        $objItems[] = '<a href="#" onclick="ShowModalProjectedShip(' . $po->id . ')" id="po_projected_ship_' . $po->id . '">' . $po->projected_ship . '</a>';

        return $objItems;
    }

    /**
     * Set Archive Po
     * @return json
     */
    public function setArchived(Request $request)
    {
        $po = Po::find($request->po_id);
        if(!$po)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Po not found.'
              ]
            );
        }
        $archived = '1';
        if($po->archived == '1') $archived = '0';
        else if($po->archived == '0') $archived = '1';

        $po->archived = $archived;
        $po->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Archive set.'
          ]
        );
    }

    public function getPoType(Request $request)
    {
        $po_id = $request->po_id;
        $po = Po::find($po_id);

        return Response::json(
          [
            'response' => 'success',
            'type' => $po->type
          ]
        );
    }

    /**
     * Set Po Type
     * @return json
     */
    public function setPoType(Request $request)
    {
        $type = $request->type;

        if($type == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please input Type.'
            ]
          );
        }

        $po = Po::where('id', $request->po_id)->first();
        if(!$po)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Purchase Order not found.'
              ]
            );
        }
        $po->type = $type;
        $po->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Type set.',
            'type' => $po->type
          ]
        );
    }

    public function getCompanyInvoice(Request $request)
    {
        $po_id = $request->po_id;
        $po = Po::find($po_id);

        return Response::json(
          [
            'response' => 'success',
            'company_invoice' => $po->company_invoice
          ]
        );
    }

    /**
     * Set Company Invoice
     * @return json
     */
    public function setCompanyInvoice(Request $request)
    {
        $company_invoice = $request->company_invoice;

        if($company_invoice == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please input Company Invoice.'
            ]
          );
        }

        $po = Po::where('id', $request->po_id)->first();
        if(!$po)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Purchase Order not found.'
              ]
            );
        }
        $po->company_invoice = $company_invoice;
        $po->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Company Invoice set.',
            'company_invoice' => $po->company_invoice
          ]
        );
    }

    public function getProjectedShip(Request $request)
    {
        $po_id = $request->po_id;
        $po = Po::find($po_id);

        return Response::json(
          [
            'response' => 'success',
            'projected_ship' => $po->projected_ship
          ]
        );
    }

    /**
     * Set Projected Ship
     * @return json
     */
    public function setProjectedShip(Request $request)
    {
        $projected_ship = $request->projected_ship;

        if($projected_ship == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please input Projected Ship.'
            ]
          );
        }

        $po = Po::where('id', $request->po_id)->first();
        if(!$po)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Purchase Order not found.'
              ]
            );
        }
        $po->projected_ship = $projected_ship;
        $po->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Projected Shipe set.',
            'projected_ship' => $po->projected_ship
          ]
        );
    }

    /**
     * Generate new Purchase Order
     *
     * @param Po $po
     */
    static public function getNumber(Po $po)
    {
        $start = 1000;
        $count = Po::where('customer_id', $po->customer_id)->count();
        $number = $start + $count;

        $customer = Customer::find($po->customer_id);
        if ($customer)
        {
            $add = null;
            if ($customer->id < 10)
            {
                $add = '000';
            }
            else
            {
                if ($customer->id < 100)
                {
                    $add = '00';
                }
                else
                {
                    if ($customer->id < 1000)
                    {
                        $add = '0';
                    }
                }
            }
            $po->number = $add . $customer->id . '-' . $number;
        }
        else
        {
            $po->number = '0000-' . $number;
        }
        $po->save();
    }

    /**
     * Create Purchase Order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePo(Request $request)
    {
        $po = new Po();
        if ($request->title == '' || $request->vendor_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'A vendor must be selected and a description is required.'
              ]
            );
        }
        $po->user_id = Auth::user()->id;
        $po->customer_id = $request->customer_id;
        $po->title = $request->title;
        $po->vendor_id = $request->vendor_id;
        $po->status = 'draft';
        $po->type = 'Other';
        $po->job_id = '0';

        // Get Job Number if there is one.
        $customer = Customer::find($request->customer_id);
        $lead = Lead::where('customer_id', $customer->id)->first();
        $quotes = Quote::where('lead_id', $lead->id)->get();
        foreach ($quotes AS $quote)
        {
            $job = Job::where('quote_id', $quote->id)->first();
            if ($job)
            {
                $po->job_id = $job->id;
            }
        }
        $po->save();
        $this->getNumber($po);

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Created.',
            'redirect_url' => route('view_po', ['id' => $po->id])
          ]
        );
    }

    public function viewPo($id, Request $request)
    {
        $po = Po::find($id);
        $customer = Customer::find($po->customer_id);

        $jobItems = JobItem::where('job_id', $po->job_id)
                          ->where('instanceof', 'FFT')
                          ->where('po_item_id', '0')
                          ->where('reference', '<>', '')
                          ->get();

        //Punch dropdown
        $punchData = '';
        foreach($jobItems as $item)
        {
            $punchData .= '<option value="' . $item->id . '">' . $item->reference . '</option>';
        }

        // Check FFT for FFT/Warranty/Service Item
        $ffts = collect(new Fft());
        $title = '';

        if ($po->job_id)
        {
            $ffts = Fft::where('job_id', $po->job_id)->get();
        }
        else
        {
            // Get Jobs for a customer.
            $lead = Lead::where('customer_id', $po->customer_id)->first();
            if($lead)
            {
              $quotes = Quote::where('lead_id', $lead->id)->get();
              foreach ($quotes AS $quote)
              {
                  $job = Job::where('quote_id', $quote->id)->first();
                  if ($job)
                  {
                      $ffts = Fft::where('job_id', $job->id)->get();
                  }
              }
            }
        }

        $warrantyOptsHeader = '<select class="form-control" name="warranty_id" id="warranty_id" required><option value="0">-- Select Warranty -- </option>';
        $fftOptsHeader = '<select class="form-control" name="fft_id" id="fft_id" required><option value="0">-- Select FFT -- </option>';
        $serviceOptsHeader = '<select class="form-control" name="service_id" id="service_id" required><option value="0">-- Select Service Work -- </option>';
        $warrantyOpts = '';
        $fftOpts = '';
        $serviceOpts = '';

        $selectOpts = '';
        foreach ($ffts as $fft)
        {
            $job = Job::find($fft->job_id);
            if (!$job) continue;
            if ($fft->warranty)
            {
                $jobItems = JobItem::where('job_id', $fft->job_id)
                                    ->where('instanceof', 'Warranty')
                                    ->get();

                foreach ($jobItems as $item)
                {
                  $warrantyOpts .= '<option value="' . $item->id . '">(' . $item->id . ') ' . $item->reference . '</option>';
                }
            }
            else if ($fft->service)
            {
                $jobItems = JobItem::where('job_id', $fft->job_id)
                                    ->where('instanceof', 'Service')
                                    ->get();

                foreach ($jobItems as $item)
                {
                  $serviceOpts .= '<option value="' . $item->id . '">(' . $item->id . ') ' . $item->reference . '</option>';
                }
            }
            else
            {
                $jobItems = JobItem::where('job_id', $fft->job_id)
                                    ->where('instanceof', 'FFT')
                                    ->get();

                foreach ($jobItems as $item)
                {
                  $fftOpts .= '<option value="' . $item->id . '">(' . $item->id . ') ' . $item->reference . '</option>';
                }
            }
        }
        if ($fftOpts != '')
        {
            $title = 'From FFT:';
            $fftOpts = $fftOptsHeader . $fftOpts . '</select>';
            $selectOpts = $fftOpts;
        }
        else if ($serviceOpts != '')
        {
            $title = 'From Service Work:';
            $serviceOpts = $serviceOptsHeader . $serviceOpts . '</select>';
            $selectOpts = $serviceOpts;
        }
        else if ($warrantyOpts != '')
        {
            $title = 'From Warranty:';
            $warrantyOpts = $warrantyOptsHeader . $warrantyOpts . '</select>';
            $selectOpts = $warrantyOpts;
        }

        return view('po.view', compact('po', 'customer', 'punchData', 'title', 'selectOpts'));
    }

    public function order($id)
    {
        $po = Po::find($id);
        $po->status = 'ordered';
        $po->submitted = Carbon::now();
        $po->save();
        // If there are any items linked then we need to set them as ordered
        $poItems = PoItem::where('po_id', $po->id)->get();
        foreach ($poItems as $item)
        {
            if ($item->punch)
            {
                $ji = JobItem::find($item->job_item_id);
                $ji->ordered = Carbon::now();
                $ji->save();
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Order set.',
            'po_id' => $po->id
          ]
        );
    }

    public function confirm($id)
    {
        $po = Po::find($id);
        $po->status = 'confirmed';
        $po->save();

        $poItems = PoItem::where('po_id', $po->id)->get();
        foreach ($poItems as $item)
        {
            if ($item->punch)
            {
                $ji = JobItem::find($item->job_item_id);
                $ji->confirmed = Carbon::now();
                $ji->save();
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Confirm set.',
            'po_id' => $po->id
          ]
        );
    }

    /**
     * Delete Purchase Order
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function delete(Request $request)
    {
        $po_id = $request->po_id;
        Po::find($po_id)->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Deleted.'
          ]
        );
    }

    public function displayPoItem(Request $request)
    {
        $po_id = $request->po_id;

        $po = Po::find($po_id);

        $items = PoItem::leftJoin('users as receiveUser', 'po_items.received_by', 'receiveUser.id')
                        ->select(
                                  'po_items.*',
                                  'receiveUser.name as receivedByName'
                                )
                        ->where('po_items.po_id', $po_id)
                        ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->qty;

              $itemName = '';
              $status = '';
              if ($item->received_by)
              {
                  $unverify = "<a href='/item/$item->id/unverify'> (Unverify Item)</a>";

                  $status = "Received on " . Carbon::parse($item->received)
                          ->format("m/d/y h:i a") . " by " . $item->receivedByName . " " . $unverify;
              }
              elseif ($po->status == 'confirmed')
              {
                  $status = "<a href='/po/$po->id/item/$item->id/receive' class='btn btn-success'><i class='fa fa-arrow-right'></i> Receive Item</a>";
              }
              elseif ($po->archived)
              {
                  $status = "<i>This PO has been archived/closed</i>";
              }
              else
              {
                  $status = "<b>PO Not Confirmed</b>";
              }

              if ($item->punch) $item->item .= "<br/><span class='text-danger'>** This is a punch list item **</span>";
              $itemName = $item->item;

              $fftJobItem = JobItem::where('id', $item->fft_id)->first();
              if ($fftJobItem)
                  $itemName .= "<br/><small class='text-success'>(FFT) {$fftJobItem->reference}</small>";

              $serviceJobItem = JobItem::where('id', $item->service_id)->first();
              if ($serviceJobItem)
                  $itemName .= "<br/><small class='text-info'>(Service) {$serviceJobItem->reference}</small>";

              $warrantyJobItem = JobItem::where('id', $item->warranty_id)->first();
              if ($warrantyJobItem)
                  $itemName .= "<br/><small class='text-danger'>(Warranty) {$warrantyJobItem->reference}</small>";

              $objItems[] = $itemName;
              $objItems[] = $status;
              $objItems[] = "<a href='#' onclick='ShowModalDeleteItemConfirm({$item->id})'><i class='fa fa-trash-o'></i></a>";

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Quote::where('closed', '0')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function newItem(Request $request)
    {
        $po_id = $request->po_id;
        $qty = $request->qty;
        $item = $request->item;
        $punch_item_id = $request->punch_item_id;

        $po = Po::find($po_id);
        $desc = $item;
        if ($punch_item_id)
        {
            $ji = JobItem::find($punch_item_id);
            $desc = $ji->reference;
        }

        $item = new PoItem();
        $item->po_id = $po->id;
        $item->item = $desc;
        $item->user_id = Auth::user()->id;
        $item->qty = $qty;
        $item->punch = $punch_item_id ? true : false;
        $item->job_item_id = $punch_item_id ?: 0;
        $item->warranty_id = $request->warranty_id ?: 0;
        $item->fft_id = $request->fft_id ?: 0;
        $item->service_id = $request->service_id ?: 0;
        $item->save();

        if (!empty($ji))
        {
            $ji->po_item_id = $item->id;
            $ji->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Item Added.'
          ]
        );
    }

    public function unverify($id, Request $request)
    {
        $item = PoItem::find($id);
        $item->received_by = 0;
        $item->received = null;
        $item->save();

        return redirect()->back()->with('success', 'Item Unverify Set');
    }

    public function receive($id, $iid, Request $request)
    {
        $item = PoItem::find($iid);
        $item->received = Carbon::now();
        $item->received_by = Auth::user()->id;
        $item->save();
        // Is this item linked to a Punch item? If so we need to update it too.
        if ($item->punch)
        {
            $ji = JobItem::find($item->job_item_id);
            $ji->received = Carbon::now();
            $ji->verified = Carbon::now();
            $ji->save();
        }

        // Check to see if we need to close the PO.
        $po = Po::find($id);
        $close = true;
        foreach ($po->items AS $item)
        {
            if (!$item->received_by)
            {
                $close = false;
            }
        }
        if ($close)
        {
            $po->status = 'complete';
            $po->archived = 1;
            $po->save();
            $this->updateCompletedPunch($po);

            return redirect()->back()->with('success', 'Item Received');

        }
        return redirect()->back()->with('success', 'Item Received');
    }

    /**
     * Sends an update notification when ALL purchase orders have been completed.
     *
     * @param Po $po
     */
    public function updateCompletedPunch(Po $po)
    {
        // First let's see how many other POs are tied to this.
        $root = explode("-", $po->number);
        $root = $root[0];
        $pass = true;
        foreach (Po::where('number', 'like', $root . '-%')->get() as $p)
        {
            if (!$p->archived)
            {
                $pass = false;
            }
        }
        if (!$pass) return;

        try
        {
            $customer = $po->job ? $po->job->quote->lead->customer->contacts()
                ->first()->name : $po->customer->contacts()->first()->name;
        } catch (Exception $e)
        {
            $customer = null;
        }

        Mail::send('emails.completedpo', ['po' => $po, 'customer' => $customer], function ($message) use ($po)
        {
            $message->to("schedules@frugalkitchens.com", "Frugal Schedules")
                ->subject("Purchase Order #$po->number has been received.");
        });

    }

    public function removeItem(Request $request)
    {
        PoItem::find($request->po_item_id)->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Po Item Deleted.'
          ]
        );
    }

    /**
     *
     * @param $id
     * @return mixed
     */
    public function spawn($id)
    {
        $po = Po::find($id);
        $child = new Po();
        $child->customer_id = $po->customer_id;
        $child->title = "Replacement/Misc Order (Original #$po->number)";
        $child->user_id = $po->user_id;
        $child->status = 'draft';
        $child->parent_id = $po->id;
        $child->job_id = $po->job_id;
        $child->vendor_id = '0';
        $child->save();
        self::getNumber($child);

        return redirect(route('view_po', ['id' => $child->id]));
    }

    /**
     * Purchase Order Exporter
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function export()
    {
        $data = null;
        $rows = [];
        $rows[] = ['PO #', 'Name', 'Vendor', 'Ordered Date', 'Ship Date', 'Order Type'];
        foreach (Po::whereArchived(false)->get() as $po)
        {
            $customer = Customer::find($po->customer_id);
            $vendor = Vendor::find($po->vendor_id);
            $rows[] = [
                $po->id,
                $customer ? $customer->name : "Unknown",
                $vendor ? $vendor->name : '',
                Carbon::parse($po->submitted)->format("m/d/y"),
                $po->projected_ship,
                $po->type
            ];
        }
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }
        // Next Report
        $data .= "\n\n\n";
        $rows = [];
        $rows[] = ['Item', 'Name', 'Status', 'Order Date', 'Completed', 'Created'];
        foreach (Po::whereArchived(false)->get() as $po)
        {
            $jobItems = JobItem::where('job_id', $po->job_id)->get();
            foreach ($jobItems as $item)
            {
                $customer = Customer::find($po->customer_id);
                $rows[] = [
                   str_replace(",", "-", $item->item),
                   $customer ? $customer->name: '',
                   $item->received_by ? "Received on ". Carbon::parse($item->received)->format("m/d/y") : "Not Received",
                   Carbon::parse($po->submitted)->timestamp > 0 ? Carbon::parse($po->submitted)->format("m/d/y") : "Not Ordered",
                   $item->received_by ? "Yes" : "No",
                   $item->created_at->format("m/d/y h:i a")
                ];
            }
        }
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }



        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="pos.csv"',
        ];

        return Response::make($data, 200, $headers);
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
     * Create new task
     * @return mixed
     */
    public function create()
    {
        return view('po.create');
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
}
