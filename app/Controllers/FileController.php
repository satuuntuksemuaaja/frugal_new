<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\File;
use FK3\Models\Quote;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class FileController extends Controller
{
    public $auditPage = "File";

    /*
     * Show File Index.
     */
    public function index(Request $request)
    {
        $quotes = Quote::all();
        return view('files.index', compact('quotes'));
    }

    /*
     * File Upload.
     */
    public function upload(Request $request)
    {
        $quote_id = $request->quote_id;

        $fileName = $request->file('file')->getClientOriginalName();
        $filePath = $request->file('file')->store('user_files');

        $file = new File();
        $file->location = $filePath;
        $file->description = $request->description;
        $file->user_id = Auth::user()->id;
        $file->quote_id = '-1';
        $file->save();

        return redirect()->back()->with('success', 'File Uploaded');
    }

    /*
     * Display Files Data.
     */
    public function displayFiles(Request $request)
    {
        $items = File::leftJoin('users', 'files.user_id', '=', 'users.id')
                      ->select(
                                'files.id',
                                'files.location',
                                'files.description',
                                'users.name as user_name',
                                'files.attached',
                                'files.created_at'
                              )
                      ->where('files.deleted_at', null)
                      ->where('files.quote_id', '-1');
        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="' . route('download_file', ['file_id' => $item->id]) . '" target="_blank"><i class="fa fa-download"></i></a>';
              $objItems[] = $item->description;
              $objItems[] = $item->user_name . ' on ' . Carbon::parse($item->created_at)->format('m/d/Y H:i:s');
              $objItems[] = '<a href="' . route('delete_file', ['id' => $item->id]) . '"><i class="fa fa-trash"></i></a>';

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function downloadFile($id, Request $request)
    {
        $file = File::find($id);

        return response()->download(public_path("app/") . $file->location);
    }

    public function deleteFile($id, Request $request)
    {
        $file = File::find($id);
        $file->delete();

        return redirect()->back()->with('success', 'File Deleted');
    }
}
