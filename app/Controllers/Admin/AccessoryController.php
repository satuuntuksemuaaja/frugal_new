<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Accessory;
use FK3\Models\Vendor;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use File;
use Response;

class AccessoryController extends Controller
{
    public $auditPage = "Accessories";

    /*
     * Show accessory Index.
     */
    public function index()
    {
        $vendors = Vendor::all();
        return view('admin.accessories.index', compact('vendors'));
    }

    /**
     * Display Accessories Data
     * @return Array
     */

    public function displayAccessories()
    {
        $items = Accessory::where('deleted_at', null)->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditAccessory(' . $item->id . ');">' . $item->sku . '</a>';
              $objItems[] = $item->name;
              $objItems[] = @$item->vendor->name;
              $objItems[] = $item->description;
              $objItems[] = '<a href="#" onclick="DeleteAccessory(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Accessory::where('deleted_at', null)->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function store(Request $request)
    {
        $sku = $request->sku;
        $name = $request->name;
        $description = $request->description;
        $price = $request->price;
        $vendor_id = $request->vendor_id;
        $on_site = $request->on_site ? '1' : '0';

        $accessory = new Accessory();
        $accessory->sku = $sku;
        $accessory->name = $name;
        $accessory->description = $description;
        $accessory->price = $price;
        $accessory->vendor_id = $vendor_id;
        $accessory->on_site = $on_site;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('accessories');
            $accessory->image = $filePath;
        }

        $accessory->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Accessory Added'
          ]
        );
    }

    public function downloadFile($id)
    {
        $accessory = Accessory::find($id);

        return response()->download(public_path("app/") . $accessory->image);
    }

    public function getAccessory(Request $request)
    {
        $accessory = Accessory::find($request->accessory_id);

        return Response::json(
          [
            'response' => 'success',
            'sku' => $accessory->sku,
            'name' => $accessory->name,
            'description' => $accessory->description,
            'price' => $accessory->price,
            'vendor_id' => $accessory->vendor_id,
            'on_site' => $accessory->on_site,
            'image' => $accessory->image,
            'accessory_id' => $accessory->id
          ]
        );
    }

    public function updateAccessory(Request $request)
    {
        $sku = $request->sku;
        $name = $request->name;
        $description = $request->description;
        $price = $request->price;
        $vendor_id = $request->vendor_id;
        $on_site = $request->on_site ? '1' : '0';

        $accessory = Accessory::find($request->accessory_id);
        $accessory->sku = $sku;
        $accessory->name = $name;
        $accessory->description = $description;
        $accessory->price = $price;
        $accessory->vendor_id = $vendor_id;
        $accessory->on_site = $on_site;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('accessories');
            $accessory->image = $filePath;
        }

        $accessory->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Accessory Updated'
          ]
        );
    }

    public function deleteAccessory(Request $request)
    {
        $accessory = Accessory::find($request->accessory_id);
        $accessory->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Accessory Deleted'
          ]
        );
    }
}
