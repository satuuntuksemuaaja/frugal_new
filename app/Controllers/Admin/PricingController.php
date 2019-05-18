<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Extra;
use FK3\Models\Group;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;
use Auth;

class PricingController extends Controller
{
    public $auditPage = "Pricings";

    /*
     * Show pricing Index.
     */
    public function index()
    {
        $extras = Extra::all();
        $groups = Group::all();
        return view('admin.pricing.index', compact('extras', 'groups'));
    }

    /**
     * Display Pricing Data
     * @return Array
     */

    public function displayPricing()
    {
        $items = Extra::where('deleted_at', null)->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditPricing(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->price;
              $objItems[] = @$item->group->name;
              $objItems[] = '<a href="#" onclick="DeletePricing(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Extra::where('deleted_at', null)->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Pricing Data
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
        $price = $request->price ?: '0';
        $user_id = Auth::user()->id;
        $group_id = $request->group_id;

        $extra = new Extra();
        $extra->name = $name;
        $extra->price = $price;
        $extra->user_id = $user_id;
        $extra->group_id = $group_id;
        $extra->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Pricing Added'
          ]
        );
    }

    public function getPricing(Request $request)
    {
        $extra_id = $request->extra_id;

        $extra = Extra::find($extra_id);

        if(!$extra)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Pricing not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $extra->name,
            'name' => $extra->name,
            'price' => $extra->price,
            'group_id' => $extra->group_id,
            'extra_id' => $extra->id
          ]
        );
    }

    /**
     * Update Pricing Data
     * @return Array
     */

    public function updatePricing(Request $request)
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
        $price = $request->price ?: '0';
        $user_id = Auth::user()->id;
        $group_id = $request->group_id;

        $extra = Extra::find($request->extra_id);
        $extra->name = $name;
        $extra->price = $price;
        $extra->user_id = $user_id;
        $extra->group_id = $group_id;
        $extra->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Pricing Updated'
          ]
        );
    }

    public function deletePricing(Request $request)
    {
        $extra = Extra::find($request->extra_id);
        $extra->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Extra Deleted'
          ]
        );
    }
}
