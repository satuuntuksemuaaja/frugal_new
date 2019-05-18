<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Granite;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class GraniteController extends Controller
{
    public $auditPage = "Granite";

    /*
     * Show payment Index.
     */
    public function index()
    {
        return view('admin.granites.index');
    }

    /**
     * Display Granites Data
     * @return Array
     */

    public function displayGranites()
    {
        $items = Granite::where('deleted_at', null)->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditGranite(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->price;
              $objItems[] = $item->removal_price;
              $objItems[] = '<a href="#" onclick="DeleteGranite(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Granite::where('deleted_at', null)->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Granite Data
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
        $removal_price = $request->removal_price ?: '0';

        $granite = new Granite();
        $granite->name = $name;
        $granite->price = $price;
        $granite->removal_price = $removal_price;
        $granite->active = '1';
        $granite->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Granite Added'
          ]
        );
    }

    public function getGranite(Request $request)
    {
        $granite_id = $request->granite_id;

        $granite = Granite::find($granite_id);

        if(!$granite)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Granite not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $granite->name,
            'name' => $granite->name,
            'price' => $granite->price,
            'removal_price' => $granite->removal_price,
            'granite_id' => $granite->id
          ]
        );
    }

    /**
     * Update Granite Data
     * @return Array
     */

    public function updateGranite(Request $request)
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
        $removal_price = $request->removal_price ?: '0';

        $granite = Granite::find($request->granite_id);
        $granite->name = $name;
        $granite->price = $price;
        $granite->removal_price = $removal_price;
        $granite->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Granite Updated'
          ]
        );
    }

    public function deleteGranite(Request $request)
    {
        $granite = Granite::find($request->granite_id);
        $granite->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Granite Deleted'
          ]
        );
    }
}
