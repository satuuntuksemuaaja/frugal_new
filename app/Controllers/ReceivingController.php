<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Po;
use FK3\Models\PoItem;
use FK3\Models\Customer;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class ReceivingController extends Controller
{
    public $auditPage = "Receiving";

    /*
     * Show Receiving Index.
     */
    public function index(Request $request)
    {
        return view('receiving.index');
    }

    /**
     * Display Receiving Data
     * @return Array
     */
    public function displayReceiving()
    {
        $items = Po::leftJoin('customers', 'pos.customer_id', '=', 'customers.id')
                    ->select(
                              'pos.*',
                              'customers.name as cust_name'
                            )
                    ->where('pos.archived', '0')
                    ->where('pos.status', 'confirmed');

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = ($item->cust_name != '') ? $item->cust_name : "Unknown Customer/Internal";
              $objItems[] = $item->type;
              $objItems[] = '<a href="' . route('view_receiving', ['id' => $item->id]) . '" class="btn btn-primary">' . $item->number . '</a>';

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function view($id, Request $request)
    {
        $po = Po::find($id);
        $customer = Customer::find($po->customer_id);
        return view('receiving.view', compact('po', 'customer'));
    }

    /**
     * Display Receiving Data
     * @return Array
     */
    public function displayReceivingPo(Request $request)
    {
        $po_id = $request->po_id;

        $po = Po::find($po_id);

        $items = PoItem::leftJoin('users', 'po_items.received_by', '=', 'users.id')
                        ->select(
                                    'po_items.*',
                                    'users.name as receivedByName'
                                )
                        ->where('po_items.po_id', $po->id);

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              if ($item->received_by)
              {
                $unverify = "<a href='" . route('receiving_unverify', ['id' => $item->id]) . "'> (Unverify Item)</a>";
                $status = "Received on " . Carbon::parse($item->received)->format("m/d/y h:i a") . " by " . $item->receivedByName . " " . $unverify;
              }
              elseif ($po->status == 'confirmed')
                $status = '<a href="' . route('receiving_receive', ['id' => $po->id, 'iid' => $item->id]) . '" class="btn btn-success"><i class="fa fa-arrow-right"></i> Receive Item</a>';
              elseif ($po->archived)
                $status = "<i>This PO has been archived/closed</i>";
              else
                $status = "<b>PO Not Confirmed</b>";

              $objItems[] = $item->qty;
              $objItems[] = $item->item;
              $objItems[] = $status;

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function receive($id, $iid, Request $request)
    {
        $item = PoItem::find($iid);
        $item->received = Carbon::now();
        $item->received_by = Auth::user()->id;
        $item->save();
        // Check to see if we need to close the PO.
        $po = Po::find($id);
        $close = true;
        $poItems = PoItem::where('po_id', $po->id)->get();
        foreach ($poItems AS $item)
        {
          if (!$item->received_by) $close = false;
        }
        if ($close)
        {
          $po->status = 'complete';
          $po->archived = 1;
          $po->save();
          return redirect(route('pos.index'))->with('success', 'PO Completed.');
        }
        return redirect()->back()->with('success', 'PO Item set received.');
    }

    public function unverify($id, Request $request)
    {
        $item = PoItem::find($id);
        $item->received_by = 0;
        $item->received = null;
        $item->save();
        return redirect()->back()->with('success', 'PO Item set unverify.');
    }

}
