<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Promotion;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class PromotionController extends Controller
{
    public $auditPage = "Promotion";

    /*
     * Show promotion Index.
     */
    public function index()
    {
        return view('admin.promotions.index');
    }

    /**
     * Display Promotion Data
     * @return Array
     */

    public function displayPromotions()
    {
        $items = Promotion::orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditPromotion(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->modifier;
              $objItems[] = $item->verbiage;
              $objItems[] = $item->active ? 'Yes' : 'No';
              $objItems[] = '<a href="#" onclick="DeletePromotion(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Promotion::orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Promotion Data
     * @return Array
     */

    public function store(Request $request)
    {
        $name = $request->name;
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty'
              ]
            );
        }
        $qualifier = $request->qualifier;
        if($qualifier == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Qualifier cannot be empty'
              ]
            );
        }
        $discount_amount = $request->discount_amount;
        if($discount_amount == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Discount Amount cannot be empty'
              ]
            );
        }
        $verbiage = $request->verbiage;
        if($verbiage == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Verbiage Contract cannot be empty'
              ]
            );
        }

        $active = $request->active;
        $modifier = $request->modifier;
        $condition = $request->condition;

        $promotion = new Promotion();
        $promotion->name = $name;
        $promotion->active = $active;
        $promotion->modifier = $modifier;
        $promotion->condition = $condition;
        $promotion->qualifier = $qualifier;
        $promotion->discount_amount = $discount_amount;
        $promotion->verbiage = $verbiage;
        $promotion->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Promotion Added'
          ]
        );
    }

    public function getPromotion(Request $request)
    {
        $promotion_id = $request->promotion_id;

        $promotion = Promotion::find($promotion_id);

        if(!$promotion)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Promotion not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $promotion->name,
            'name' => $promotion->name,
            'active' => $promotion->active,
            'modifier' => $promotion->modifier,
            'condition' => $promotion->condition,
            'qualifier' => $promotion->qualifier,
            'discount_amount' => $promotion->discount_amount,
            'verbiage' => $promotion->verbiage,
            'promotion_id' => $promotion->id
          ]
        );
    }

    /**
     * Update Promotion Data
     * @return Array
     */

    public function updatePromotion(Request $request)
    {
        $name = $request->name;
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty'
              ]
            );
        }
        $qualifier = $request->qualifier;
        if($qualifier == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Qualifier cannot be empty'
              ]
            );
        }
        $discount_amount = $request->discount_amount;
        if($discount_amount == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Discount Amount cannot be empty'
              ]
            );
        }
        $verbiage = $request->verbiage;
        if($verbiage == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Verbiage Contract cannot be empty'
              ]
            );
        }

        $active = $request->active;
        $modifier = $request->modifier;
        $condition = $request->condition;

        $promotion = Promotion::find($request->promotion_id);
        $promotion->name = $name;
        $promotion->active = $active;
        $promotion->modifier = $modifier;
        $promotion->condition = $condition;
        $promotion->qualifier = $qualifier;
        $promotion->discount_amount = $discount_amount;
        $promotion->verbiage = $verbiage;
        $promotion->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Promotion Updated'
          ]
        );
    }

    public function deletePromotion(Request $request)
    {
        $promotion = Promotion::find($request->promotion_id);
        $promotion->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Promotion Deleted'
          ]
        );
    }
}
