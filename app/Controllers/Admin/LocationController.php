<?php

namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Location;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;

class LocationController extends Controller
{
    public $auditPage = "Stores";

    /*
     * Show stores Index.
     */
    public function index()
    {
        return view('admin.location.index');
    }

    /**
     * Display Locations Data
     * @return Array
     */

    public function displayLocations()
    {
        $items = Location::where('deleted_at', null)->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $editLink = route('stores.edit', ['id' => $item->id]);
              $objItems[] = '<a href="' . $editLink . '">' . $item->name . '</a>';
              $objItems[] = $item->address;
              $objItems[] = $item->city;
              $objItems[] = $item->state;
              $objItems[] = $item->number;

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Location::where('deleted_at', null)->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Create new location
     * @return mixed
     */
    public function create()
    {
        return view('admin.location.create');
    }

    /**
     * Store a new store
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name || empty($request->address)) {
            throw new FrugalException("You must specify a name, and an address.");
        }

        $location = new Location();
        $location->name = $request->name;
        $location->address = $request->address;
        $location->city = $request->city;
        $location->state = $request->state;
        $location->number = $request->number;
        $location->save();

        return redirect(route('stores.index'))->with('success', 'Store added!');
    }

    /**
     * Edit location
     * @return mixed
     */
    public function edit($id)
    {
        $location = Location::find($id);
        return view('admin.location.edit', compact('location'));
    }

    /**
     * Update store
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update($id, Request $request)
    {
        if (!$request->name || empty($request->address)) {
            throw new FrugalException("You must specify a name, and an address.");
        }

        $location = Location::find($id);

        if(!$location) throw new FrugalException("Location not found.");

        $location->name = $request->name;
        $location->address = $request->address;
        $location->city = $request->city;
        $location->state = $request->state;
        $location->number = $request->number;
        $location->save();

        return redirect(route('stores.index'))->with('success', 'Store updated!');
    }

    /**
     * Delete store
     * @param $id
     * @return array
     */
    public function destroy($id)
    {
        $location = Location::find($id);
        $location->delete();

        return redirect(route('stores.index'))->with('success', 'Store deleted!');;
    }
}
