<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Responsibility;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class ResponsibilityController extends Controller
{
    public $auditPage = "Responsibilities";

    /*
     * Show payment Index.
     */
    public function index()
    {
        return view('admin.responsibilities.index');
    }

    /**
     * Display Responsibility Data
     * @return Array
     */

    public function displayResponsibilities()
    {
        $items = Responsibility::get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditResponsibility(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = '<a href="#" onclick="DeleteResponsibility(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Responsibility::count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Responsibility Data
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
                'message' => 'Responsibility cannot be empty'
              ]
            );
        }

        $responsibility = new Responsibility();
        $responsibility->name = $name;
        $responsibility->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Responsibility Added'
          ]
        );
    }

    public function getResponsibility(Request $request)
    {
        $responsibility_id = $request->responsibility_id;

        $responsibility = Responsibility::find($responsibility_id);

        if(!$responsibility)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Responsibility not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $responsibility->name,
            'name' => $responsibility->name,
            'responsibility_id' => $responsibility->id
          ]
        );
    }

    /**
     * Update Responsibility Data
     * @return Array
     */

    public function updateResponsibility(Request $request)
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

        $responsibility = Responsibility::find($request->responsibility_id);
        $responsibility->name = $name;
        $responsibility->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Responsibility Updated'
          ]
        );
    }

    public function deleteResponsibility(Request $request)
    {
        $responsibility = Responsibility::find($request->responsibility_id);
        $responsibility->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Responsibility Deleted'
          ]
        );
    }
}
