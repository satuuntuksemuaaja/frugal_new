<?php

namespace FK3\Controllers\Admin;

use FK3\Models\AuthorizationList;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class AuthorizationController extends Controller
{
    public $auditPage = "Authorization";

    /*
     * Show authorization Index.
     */
    public function index()
    {
        return view('admin.authorizations.index');
    }

    /**
     * Display Authorization Data
     * @return Array
     */

    public function displayAuthorizations()
    {
        $items = AuthorizationList::orderBy('item', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditAuthorization(' . $item->id . ');">' . $item->item . '</a>';
              $objItems[] = '<a href="#" onclick="DeleteAuthorization(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = AuthorizationList::orderBy('item', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Authorization Data
     * @return Array
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

        $authorization = new AuthorizationList();
        $authorization->item = $item;
        $authorization->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Authorization Added'
          ]
        );
    }

    public function getAuthorization(Request $request)
    {
        $authorization_id = $request->authorization_id;

        $authorization = AuthorizationList::find($authorization_id);

        if(!$authorization)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Authorization not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $authorization->item,
            'item' => $authorization->item,
            'authorization_id' => $authorization->id
          ]
        );
    }

    /**
     * Update Authorization Data
     * @return Array
     */

    public function updateAuthorization(Request $request)
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

        $authorization = AuthorizationList::find($request->authorization_id);
        $authorization->item = $item;
        $authorization->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Authorization Updated'
          ]
        );
    }

    public function deleteAuthorization(Request $request)
    {
        $authorization = AuthorizationList::find($request->authorization_id);
        $authorization->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Authorization Deleted'
          ]
        );
    }
}
