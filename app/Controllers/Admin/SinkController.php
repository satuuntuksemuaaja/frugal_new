<?php

namespace FK3\Controllers\Admin;

use FK3\Models\Sink;
use FK3\Exceptions\FrugalException;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use File;
use Response;

class SinkController extends Controller
{
    public $auditPage = "Sinks";

    /*
     * Show sink Index.
     */
    public function index()
    {
        return view('admin.sinks.index');
    }

    /**
     * Display Sinks Data
     * @return Array
     */

    public function displaySinks()
    {
        $items = Sink::where('active', '1')->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditSink(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->price;
              $objItems[] = $item->material;
              $objItems[] = '<a href="#" onclick="DeleteSink(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Sink::where('active', '1')->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Display Inactive Sinks Data
     * @return Array
     */

    public function displayInactiveSinks()
    {
        $items = Sink::where('active', '0')->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditSink(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->price;
              $objItems[] = $item->material;
              $objItems[] = '<a href="#" onclick="DeleteSink(' . $item->id . ');"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Sink::where('active', '0')->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function store(Request $request)
    {
        $name = $request->name;
        $price = $request->price;
        $material = $request->material;

        $sink = new Sink();
        $sink->name = $name;
        $sink->price = $price;
        $sink->material = $material;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('sinks');
            $sink->image = $filePath;
        }

        $sink->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Sink Added'
          ]
        );
    }

    public function downloadFile($id)
    {
        $sink = Sink::find($id);

        return response()->download(public_path("app/") . $sink->image);
    }

    public function getSink(Request $request)
    {
        $sink = Sink::find($request->sink_id);

        return Response::json(
          [
            'response' => 'success',
            'name' => $sink->name,
            'price' => $sink->price,
            'material' => $sink->material,
            'image' => $sink->image,
            'sink_id' => $sink->id,
            'active' => $sink->active
          ]
        );
    }

    public function updateSink(Request $request)
    {
        $name = $request->name;
        $price = $request->price;
        $material = $request->material;

        $sink = Sink::find($request->sink_id);
        $sink->name = $name;
        $sink->price = $price;
        $sink->material = $material;

        if ($request->hasFile('image'))
        {
            $fileName = $request->file('image')->getClientOriginalName();
            $filePath = $request->file('image')->store('sinks');
            $sink->image = $filePath;
        }

        $sink->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Sink Updated'
          ]
        );
    }

    public function deleteSink(Request $request)
    {
        $sink = Sink::find($request->sink_id);
        $sink->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Sink Deleted'
          ]
        );
    }

    public function redeactivateSink(Request $request)
    {
        $sink = Sink::find($request->sink_id);

        $message = "Reactivated";
        if($sink->active == '0')
        {
            $sink->active = '1';
        }
        else
        {
            $sink->active = '0';
            $message = "Deactivated";
        }
        $sink->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Sink ' . $message,
            'active' => $sink->active
          ]
        );
    }
}
