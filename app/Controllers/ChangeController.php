<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
use FK3\Models\Contact;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Job;
use FK3\Models\Quote;
use FK3\Models\Task;
use FK3\Models\ChangeOrder;
use FK3\Models\ChangeOrderDetail;
use FK3\Models\TaskNote;
use FK3\Models\Customer;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;
use Mail;

class ChangeController extends Controller
{
    public $auditPage = "Change Orders";

    /*
     * Show Change Index.
     */
    public function index(Request $request)
    {
        $jobs = Job::all();

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

          $selectJob .= '<option value="' . $job->id .  '">' . htmlspecialchars(@$customer->name, ENT_QUOTES) . ' (' . $quote->name . ' - ' . $job->id . ')</option>';

        }

        return view('changes.index', compact('selectJob'));
    }

    public function displayChanges(Request $request)
    {
        $all = $request->all;

        if ($all == 'true')
        {
            $orders = ChangeOrder::orderBy('id', 'asc');
        }
        else
        {
            $orders = ChangeOrder::whereClosed(false)->orderBy('id', 'asc');
        }

        $orders = $orders->get();

        $newItems = array();
        $total = 0;
        if ( !empty( $orders ) )
        {
            foreach ( $orders as $order )
            {
              $objItems = array();

              $job = Job::find($order->job_id);
              if(!$job) continue;

              $quote = Quote::find($job->quote_id);
              if(!$quote) continue;

              $lead = Lead::find($quote->lead_id);
              if (!$lead) continue;

              $customer = Customer::find($lead->customer_id);
              $user = User::find($order->user_id);

              $orderDetailsOrderableCount = ChangeOrderDetail::where('change_order_id', $order->id)
                                                ->where('orderable', '1')
                                                ->count();

              $orderDetailsCount = ChangeOrderDetail::where('change_order_id', $order->id)
                                                ->count();

              if ($orderDetailsOrderableCount > 0)
              {
                  if ($order->ordered)
                  {
                      $ordered = "Yes";
                  }
                  else
                  {
                      $ordered = "No";
                  }
              }
              else
              {
                  $ordered = "N/A";
                  $closed = $order->closed ? "<span class='pull-right' style='color:red;'>(closed)</span>" : null;
                  $closeAction = "<i class='fa fa-times'></i>";

                  $objItems[] = "<a href='/change/$order->id'><b>#$order->id</b></a> <span class='pull-right'><a href='/change/$order->id/close'>&nbsp;$closeAction</a></span>";
                  $objItems[] = "<a href='/profile/{$customer->id}/view'>{$customer->name}</a> $closed";
                  $objItems[] = $order->created_at->format("m/d/y");
                  $objItems[] = $user->name;
                  $objItems[] = ($order->sent) ? Carbon::parse($order->sent_on)->format("m/d/y") : "No";
                  $objItems[] = ($order->signed) ? Carbon::parse($order->signed_on)->format("m/d/y") . " (<a class='get' href='/change/$order->id/send'>re-send to customer)</a>" : "No (<a class='get' href='/change/$order->id/send'>send to customer)</a>";
                  $objItems[] = $ordered;
                  $objItems[] = $orderDetailsCount;

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

    public function create(Request $request)
    {
        $job_id = $request->job_id;

        $order = new ChangeOrder;
        $order->job_id = $job_id;
        $order->user_id = Auth::user()->id;
        $order->save();

        return redirect(url('/change/' . $order->id));
    }

    public function view($id, Request $request)
    {
        $order = ChangeOrder::find($id);
        $job = Job::find($order->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);

        return view('changes.view', compact('order', 'customer', 'request'));
    }

    public function displayChangesItems(Request $request)
    {
        $change_order_id = $request->order_id;
        $orderDetails = ChangeOrderDetail::leftJoin('users', 'change_order_details.user_id', '=', 'users.id')
                                          ->leftJoin('users as userOrderedBy', 'change_order_details.ordered_by', '=', 'userOrderedBy.id')
                                          ->select(
                                                    'change_order_details.id',
                                                    'change_order_details.description',
                                                    'change_order_details.price',
                                                    'users.name',
                                                    'change_order_details.created_at',
                                                    'change_order_details.orderable',
                                                    'change_order_details.ordered_on',
                                                    'userOrderedBy.name as user_ordered_by_name'
                                                  )
                                          ->where('change_order_id', $change_order_id)
                                          ->get();

        $newItems = array();
        $total = 0;
        if ( !empty( $orderDetails ) )
        {
            $totalPrice = 0;
            foreach ( $orderDetails as $orderDetail )
            {
              $objItems = array();

              $objItems[] = $orderDetail->description . '<span class="pull-right"><a href="#" onclick="ShowModalDeleteConfirm(' . $orderDetail->id . ')"><i class="fa fa-trash"></i></a></span>';
              $objItems[] = $orderDetail->price;
              $objItems[] = $orderDetail->name;
              $objItems[] = $orderDetail->created_at->format("m/d/y");
              $objItems[] = ($orderDetail->orderable) ? "Yes" : "No";
              $objItems[] = ($orderDetail->ordered_on) ? Carbon::parse($orderDetail->ordered_on)->format("m/d/y") : "N/A";
              $objItems[] = ($orderDetail->user_ordered_by_name) ? $orderDetail->user_ordered_by_name : 'Nobody';

              $newItems[] = $objItems;
              $total++;
              $totalPrice += $orderDetail->price;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems,
              'totalPrice' => number_format($totalPrice, 2)
            );
    }

    public function deleteItem(Request $request)
    {
        $order_detail_id = $request->order_detail_id;
        ChangeOrderDetail::find($order_detail_id)->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item Deleted.'
          ]
        );
    }

    public function saveDetailItem(Request $request)
    {
        $order_id = $request->order_id;
        $description = $request->description;
        $price = @(double)$request->price;
        if($price == '') $price = 0;
        $orderable = $request->orderable;

        $orderDetail = new ChangeOrderDetail();
        $orderDetail->change_order_id = $order_id;
        $orderDetail->description = $description;
        $orderDetail->price = $price;
        $orderDetail->orderable = $orderable;
        $orderDetail->user_id = Auth::user()->id;
        $orderDetail->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item Added.'
          ]
        );
    }

    public function send($id)
    {
        $order = ChangeOrder::find($id);
        $order->sent = 1;
        $order->sent_on = Carbon::now();
        $order->save();
        $data['order'] = $order;

        $job = Job::find($order->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $contact = Contact::where('customer_id', $customer->id)->first();

        $customer = $contact;

        Mail::send('emails.changerequest', $data, function ($message) use ($customer)
        {
            $message->to(['changeorder@frugalkitchens.com', $customer->email]);
            $message->subject("[$customer->name] (PENDING APPROVAL) A new change order has been requested.");

        });
        return redirect()->back()->with('success', 'Email sent');
    }

    public function displayOrderAuthItems(Request $request)
    {
        $change_order_id = $request->order_id;
        $orderDetails = ChangeOrderDetail::where('change_order_id', $change_order_id)
                                          ->get();

        $newItems = array();
        $total = 0;
        if ( !empty( $orderDetails ) )
        {
            $totalPrice = 0;
            foreach ( $orderDetails as $orderDetail )
            {
              $objItems = array();

              $objItems[] = $orderDetail->description . '<span class="pull-right"><a href="#" onclick="ShowModalDeleteConfirm(' . $orderDetail->id . ')">&nbsp;<i class="fa fa-trash"></i></a></span>';
              $objItems[] = $orderDetail->price;

              $newItems[] = $objItems;
              $total++;
              $totalPrice += $orderDetail->price;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems,
              'totalPrice' => number_format($totalPrice, 2)
            );
    }

    public function saveAuthSign(Request $request)
    {
        $order_id = $request->order_id;
        $signature = $request->signature;

        $order = ChangeOrder::find($order_id);
        if(!$order)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Order Not Found.'
            ]
          );
        }
        $order->signature = $request->signature;
        $order->signature_img = $request->signature_img;
        $order->signed_on = Carbon::now()->format('Y-m-d H:i:s');
        $order->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Set.'
          ]
        );
    }

    public function removeAuthSign(Request $request)
    {
        $order_id = $request->order_id;

        $order = ChangeOrder::find($order_id);
        $order->signature = '';
        $order->signature_img = '';
        $order->signed_on = null;
        $order->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Removed.'
          ]
        );
    }

    /**
     * Decline can be run outside of Authentication so simply return
     * a string.
     *
     * @param $id
     * @return string
     */
    public function decline($id)
    {
        $change = ChangeOrder::find($id);
        $change->declined = 1;
        $change->closed = 1;
        $change->save();
        return redirect(route('changes'))->with('success', 'This change order has been declined. Please contact our offices at 770.460.4331 if you need anything else.');
    }

    public function close($id)
    {
        $change = ChangeOrder::find($id);
        $change->closed = 1;
        $change->save();
        return redirect(route('changes'))->with('success', 'Change Order closed.');
    }

}
