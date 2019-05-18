<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Payout;
use FK3\Models\PayoutItem;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class PayoutItemController extends Controller
{
    public $auditPage = "Payout";

    /*
     * Store Payout Item.
     */
    public function store(Request $request)
    {
        $item = $request->item;
        if($item == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item cannot be empty'
              ]
            );
        }
        $amount = $request->amount;
        if($amount == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Amount cannot be empty'
              ]
            );
        }

        $payoutItem = new PayoutItem();
        $payoutItem->payout_id = $request->payout_id;
        $payoutItem->item = $item;
        $payoutItem->amount = $amount;
        $payoutItem->save();
		
		$total = $this->updatePayoutTotal($payoutItem->payout_id);
		
		Payout::where('id', $payoutItem->payout_id)
				->update(['total' => $total]);

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Item Added',
			'total' => $total
          ]
        );
    }
	
	public function updatePayoutTotal($payout_id)
	{
		$payout = Payout::find($payout_id);
		
		$payoutItems = PayoutItem::where('payout_id', $payout->id)->get();
		
		$totalPayoutItems = 0;
		foreach($payoutItems as $payoutItem)		
		{
			$totalPayoutItems += $payoutItem->amount;
		}
		
		return $totalPayoutItems;
	}

    /*
     * Display Payout Items Data.
     */
    public function displayPayoutItems(Request $request)
    {
        $payoutItems = PayoutItem::where('payout_id', $request->payout_id)->get();

        $data = '';
        foreach($payoutItems as $payoutItem)
        {
            $data .= "<tr>";
            $data .= '<td><a href="#" onclick="EditPayoutItem(' . $payoutItem->id . ');">' . $payoutItem->item . '</a></td>';
            $data .= "<td>" . $payoutItem->amount . "</td>";
            $data .= '<td><a href="#" onclick="DeletePayoutItem(' . $payoutItem->id . ');"><i class="fa fa-trash"></i></a></td>';
            $data .= "</tr>";
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function getPayoutItem(Request $request)
    {
        $payout_item_id = $request->payout_item_id;

        $payoutItem = PayoutItem::find($payout_item_id);

        if(!$payoutItem)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Item not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $payoutItem->name,
            'item' => $payoutItem->item,
            'amount' => $payoutItem->amount,
            'payout_item_id' => $payoutItem->id
          ]
        );
    }

    /**
     * Update Payout Item Data
     * @return Array
     */

    public function updatePayoutItem(Request $request)
    {
        $item = $request->item;
        if($item == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item cannot be empty'
              ]
            );
        }
        $amount = $request->amount;
        if($amount == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Amount cannot be empty'
              ]
            );
        }

        $payoutItem = PayoutItem::find($request->payout_item_id);
        $payoutItem->item = $item;
        $payoutItem->amount = $amount;
        $payoutItem->save();
		
		$total = $this->updatePayoutTotal($payoutItem->payout_id);
		
		Payout::where('id', $payoutItem->payout_id)
				->update(['total' => $total]);

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item Updated',
			'total' => $total
          ]
        );
    }

    public function deletePayoutItem(Request $request)
    {
        $payoutItem = PayoutItem::find($request->payout_item_id);
		$payout_id = $payoutItem->payout_id;
        $payoutItem->delete();
		
		$total = $this->updatePayoutTotal($payout_id);
		
		Payout::where('id', $payout_id)
				->update(['total' => $total]);

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item Deleted',
			'total' => $total
          ]
        );
    }
}
