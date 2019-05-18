<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\QuoteAddon;
use FK3\Models\QuoteAppliance;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteCountertop;
use FK3\Models\QuoteGranite;
use FK3\Models\QuoteQuestion;
use FK3\Models\QuoteQuestionAnswer;
use FK3\Models\QuoteQuestionCondition;
use FK3\Models\QuoteResponsibility;
use FK3\Models\QuoteTile;
use FK3\Models\Responsibility;
use FK3\Models\Promotion;
use FK3\Models\Cabinet;
use FK3\Models\Lead;
use FK3\Models\Job;
use FK3\Models\Sink;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\Customer;
use FK3\Models\Appliance;
use FK3\Models\Accessory;
use FK3\Models\Hardware;
use FK3\Models\Vendor;
use FK3\Models\Extra;
use FK3\Models\Addon;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use FK3\vl\quotes\QuoteGeneratorNew;
use Response;
use Redirect;
use SimpleXMLElement;
use Storage;
use Auth;
use PDF;
use View;
use Mail;

class QuoteController extends Controller
{
    public $auditPage = "Quote";

    // Editable Routes Here

    private function editable(Quote $quote)
    {

        if ($quote->accepted == 1 && !Auth::user()->superuser)
        {
            return false;
        }
        else
        {
            return true;
        }

    }

    public $quoteGenerator = '';
    /*
     * Show quote Index.
     */
    public function index(Request $request)
    {
        $leads = Lead::leftJoin('customers', 'leads.customer_id', '=', 'customers.id')
                      ->select(
                                'leads.id',
                                'customers.name as cust_name',
                                'customers.city as cust_city',
                                'customers.state as cust_state'
                               )
                      ->orderBy('id', 'DESC')->get();

        $quoteTypes = QuoteType::orderBy('name')->where('active', 1)->get();
        $users = User::orderBy('name')->where('active', 1)->get();

        return view('quote.index', compact('leads', 'quoteTypes', 'users', 'request'));
    }

    /**
     * Display Quotes Data
     * @return Array
     */
    public function displayQuotes()
    {
        $items = Quote::join('leads', 'quotes.lead_id', '=', 'leads.id')
                      ->leftJoin('customers', 'leads.customer_id', '=', 'customers.id')
                      ->leftJoin('users as designerUser', 'leads.user_id', '=', 'designerUser.id')
                      ->leftJoin('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                      ->leftJoin('statuses', 'leads.status_id', '=', 'statuses.id')
                      ->select(
                                'customers.id as cust_id',
                                'customers.name as cust_name',
                                'designerUser.name as designer_name',
                                'quotes.created_at',
                                'quote_types.name as type_name',
                                'statuses.name as status_name',
                                'quotes.final',
                                'quotes.accepted',
                                'quotes.id',
                                'quotes.paperwork',
                                'quotes.meta',
                                'quotes.title as quote_title'
                              )
                      ->where('quotes.closed', '0')
                      ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $meta = unserialize($item->meta);
              if (!is_array($meta))
              {
                  $meta = [];
              }
              $objItems = array();

              $viewQuote = '<a data-toggle="tooltip" title="View Quote" href="' . route('quote_view', ['id' => $item->id])  . '"><i class="fa fa-search"></i></a>';
              $download = '';
              if ($item->paperwork)
              {
                  $download = '<a data-toggle="tooltip" title="Download Contract" href="' . route('quote_contract', ['id' => $item->id]) . '" target="_blank"><i class="fa fa-download"></i></a>';
              }
              $appliances = '';
              if (!empty($meta['meta']['quote_appliances']))
              {
                  $appliances = '<a data-toggle="tooltip" title="Appliance Settings" href="#" onclick="ShowModalAppliance(' . $item->id . ');"><i class="fa fa-wrench"></i></a>';
              }
              $drawing = '<a data-toggle="tooltip" title="Drawings" href="#" onclick="ShowModalDrawing(' . $item->id . ');"><i class="fa fa-picture-o"></i></a>';
              $addTask = '<a data-toggle="tooltip" title="Add Task" href="#" onclick="ShowModalAddTask(' . $item->id . ');"><i class="fa fa-openid"></i></a>';
              $duplicateQuote = '<a data-toggle="tooltip" title="Duplicate Quote" href="#" onclick="ShowModalDuplicate(' . $item->id . ');"><i class="fa fa-refresh"></i></a>';
              $archive = '<a data-toggle="tooltip" title="Archive" href="#" onclick="ShowModalArchiveConfirm(' . $item->id . ')"><i class="fa fa-eraser"></i></a>';

              $objItems[] = $viewQuote . ' ' . $download . ' ' . $appliances . ' ' . $drawing . ' ' . $addTask . ' ' . $duplicateQuote . ' ' . $archive;

              $title = ($item->quote_title) ? " <span class='text-info'>($item->quote_title)</span> " : null;
              $objItems[] = '<a href="' . route('view_profile', ['id' => $item->cust_id]) . '">' . $item->cust_name . $title . '</a><br><small>ID: ' . $item->cust_id . '</small>';

              $designerName = '--no designer assigned--';
              if($item->designer_name != '') $designerName = $item->designer_name;
              $objItems[] = $designerName;

              $objItems[] = Carbon::parse($item->created_at)->age;

              $typeName = '--no type set--';
              if($item->type_name != '') $typeName = $item->type_name;
              $final = '';
              if($item->final == '1') $final = '<span class="btn btn-success btn-sm" style="float:right;" onclick="SetQuoteFinal(' . $item->id . ');">Final</span>';
              else if($item->final == '0') $final = '<span class="btn btn-primary btn-sm" style="float:right;" onclick="SetQuoteFinal(' . $item->id . ');">Initial</span>';
              $accepted = '';
              if($item->accepted == '0' && $item->final == '1') $accepted = '<span class="btn btn-primary btn-sm" style="float:right;" onclick="location.href = \'' . route('quote_financing', ['id' => $item->id]) . '\';">Need Financing</span>';
              $objItems[] = $typeName . $final . $accepted;

              $statusName = $item->status_name;
              if($statusName == "") $statusName = '--unknown--';
              $objItems[] = $statusName;

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Quote::where('closed', '0')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Display Quote Files Data
     * @return Array
     */
    public function displayFiles(Request $request)
    {
      $quote_id = $request->quote_id;

      $items = File::leftJoin('users', 'files.user_id', '=', 'users.id')
                        ->select(
                                  'files.id',
                                  'files.location',
                                  'files.description',
                                  'users.name as user_name',
                                  'files.attached',
                                  'files.created_at'
                                )
                        ->where('files.quote_id', $quote_id)
                        ->where('files.deleted_at', null)
                        ->get();

      if ( !empty( $items ) )
      {
          $newItems = array();
          foreach ( $items as $item )
          {
            $objItems = array();

            $objItems[] = '<a href="' . route('quote_download_file', ['id' => $quote_id, 'file_id' => $item->id]) . '" target="_blank"><i class="fa fa-download"></i></a>';
            $objItems[] = $item->description;
            $objItems[] = $item->user_name . ' on ' . Carbon::parse($item->created_at)->format('m/d/Y H:i:s');

            $attached = 'No';
            if($item->attached == '1') $attached = 'Yes';
            $objItems[] = $attached;
            $objItems[] = '<a href="#" onclick="DoDeleteFile(' . $quote_id . ',' . $item->id . ')"><i class="fa fa-trash"></i></a>';

            $newItems[] = $objItems;
          }
      }

      // Get Total
      $total = File::where('deleted_at', null)->count();

      return array( 	'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'data' => $newItems
          );
    }

    /**
     * Show an existing quote
     * @param Quote $quote
     * @return mixed
     */
    public function show(Quote $quote)
    {

    }

    /**
     * Create new quote
     * @return mixed
     */
    public function create()
    {
        return view('quote.create');
    }

    /**
     * Store a new hardware
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {

    }

    /**
     * Update a quote.
     * @param Quote $quote
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Quote $quote, Request $request)
    {

    }

    /**
     * Store a new quote
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function saveQuote(Request $request)
    {
        $lead_id = $request->lead_id;
        $quote_type_id = $request->quote_type_id;

        if($lead_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select customer.'
            ]
          );
        }

        if($quote_type_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select quote type.'
            ]
          );
        }

        $quote = new Quote();
        $quote->lead_id = $lead_id;
        $quote->quote_type_id = $quote_type_id;
        $quote->meta = '';
        $quote->title = '';
        $quote->picking_slab = '';
        $quote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Added.',
            'quote_id' => $quote->id
          ]
        );
    }

    public function start($id, Request $request)
    {
        $quote = Quote::find($id);
        $quoteType = QuoteType::find($quote->quote_type_id);

        $title = '';
        if($quoteType->cabinets == '1') $title = 'Cabinets | \'s ' . $quoteType->name;
        return view('admin.quote.start', compact('quote', 'quoteType', 'title'));
    }

    public function saveCabinet($id, Request $request)
    {
      $quote = Quote::find($id);
      if ($request->hasFile('xml'))
      {
          $this->processUploadedXML($id, $request);
          return Redirect::to("/quote/$quote->id/cabinets");
      }
      $quote->save();
      return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function setQuoteFinal(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = new Quote();
        $oldquote = Quote::find($quote_id);
        $quote->lead_id = $oldquote->lead_id;
        $quote->meta = '';
        $quote->title = '';
        $quote->picking_slab = '';
        $quote->final = 1;
        $quote->quote_type_id = $oldquote->quote_type_id;
        $quote->title = '';
        $quote->save();
        $oldquote->closed = 1;
        $oldquote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Final set.',
            'new_quote_id' => $quote->id
          ]
        );
    }

    public function financing($id, Request $request)
    {
        $quote = Quote::find($id);

        return view('quote.financing', compact('quote'));
    }

    public function financingSave($id, $type, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        $meta['meta']['finance']['type'] = $type;
        switch ($type)
        {
            case 'all':
                $meta['meta']['finance']['terms'] = $request->terms;
                break;
            case 'partial':
                unset($meta['meta']['finance']['method']);    // Just in case for #146
                $meta['meta']['finance']['down_cash'] = $request->down_cash;
                $meta['meta']['finance']['down_credit'] = $request->down_credit;
                $meta['meta']['finance']['downpayment'] = $request->downpayment;
                $meta['meta']['finance']['terms'] = $request->terms;
                $dpt = $request->down_cash + $request->down_credit;
                if ($dpt != $request->downpayment)
                {
                    return redirect()->back()->with('error', 'Credit and Cash downpayments should equal the total downpayment amount. Try again.');
                }
                break;
            case 'none':
                $meta['meta']['finance']['method'] = $request->method;
                $meta['meta']['finance']['no_cash'] = $request->no_cash;
                $meta['meta']['finance']['no_credit'] = $request->no_credit;

                break;
        }

        $quote->meta = serialize($meta);
        $quote->save();

        return redirect(route('quote_view', ['id' => $quote->id]))->with('success', 'Finance Set.');
    }

    public function view($id, Request $request)
    {
        $quote = Quote::find($id);
        if(!$quote) abort(404);
        $job = Job::where('quote_id', $quote->id)->first();
        $quoteType = QuoteType::where('id', $quote->quote_type_id)->first();
        $quoteTypes = QuoteType::where('active', '1')->get();
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);

        $this->quoteGenerator = new QuoteGeneratorNew($quote, true);

        return view('quote.view', compact('quote', 'lead', 'customer', 'quoteType', 'quoteTypes', 'job', 'request'));
    }

    public function uploadFile($id, Request $request)
    {
        $fileName = $request->file('file')->getClientOriginalName();
        $filePath = $request->file('file')->store('quote_files');

        $file = new File();
        $file->location = $filePath;
        $file->description = $request->description;
        $file->user_id = Auth::user()->id;
        $file->quote_id = $id;
        $file->save();

        $previousUrl = app('url')->previous();

        return redirect()->to($previousUrl . '?upload_file=1&quote_id=' . $id);
    }

    public function duplicate(Request $request)
    {
        $quote_id = $request->quote_id;
        $title = $request->title;
        if($title == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Title cannot be empty'
              ]
            );
        }
        $existTitleQuote = Quote::where('title', $title)->first();
        if($existTitleQuote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote with title \'' . $title . '\' already exist. Please enter another title.'
              ]
            );
        }

        $quote = Quote::find($quote_id);
        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote not found.'
              ]
            );
        }

        //duplicate quote
        $newQuote = $quote->replicate();
        $newQuote->title = $title;
        $newQuote->save();

        //duplicate quote_addons
        $quoteAddons = QuoteAddon::where('quote_id', $quote_id)->get();
        foreach($quoteAddons as $quoteAddon)
        {
            $newQuoteAddOn = $quoteAddon->replicate();
            $newQuoteAddOn->quote_id = $newQuote->id;
            $newQuoteAddOn->save();
        }

        //duplicate quote_appliances
        $quoteAppliances = QuoteAppliance::where('quote_id', $quote_id)->get();
        foreach($quoteAppliances as $quoteAppliance)
        {
            $newQuoteAppliance = $quoteAppliance->replicate();
            $newQuoteAppliance->quote_id = $newQuote->id;
            $newQuoteAppliance->save();
        }

        //duplicate quote_cabinets
        $quoteCabinets = QuoteCabinet::where('quote_id', $quote_id)->get();
        foreach($quoteCabinets as $quoteCabinet)
        {
            $newQuoteCabinet = $quoteCabinet->replicate();
            $newQuoteCabinet->quote_id = $newQuote->id;
            $newQuoteCabinet->save();
        }

        //duplicate quote_granites
        $quoteGranites = QuoteGranite::where('quote_id', $quote_id)->get();
        foreach($quoteGranites as $quoteGranite)
        {
            $newQuoteGranite = $quoteGranite->replicate();
            $newQuoteGranite->quote_id = $newQuote->id;
            $newQuoteGranite->save();
        }

        //duplicate quote_responsibilities
        $quoteResponsibilities = QuoteResponsibility::where('quote_id')->get();
        foreach($quoteResponsibilities as $quoteResponsibility)
        {
            $newQuoteResponsibility = $quoteResponsibility->replicate();
            $newQuoteResponsibility->quote_id = $newQuote->id;
            $newQuoteResponsibility->save();
        }

        //duplicate quote_tiles
        $quoteTiles = QuoteTile::where('quote_id', $quote_id)->get();
        foreach($quoteTiles as $quoteTile)
        {
            $newQuoteTile = $quoteTile->replicate();
            $newQuoteTile->quote_id = $newQuote->id;
            $newQuoteTile->save();
        }

        //duplicate quote_countertops
        $quoteCountertops = QuoteCountertop::where('quote_id', $quote_id)->get();
        foreach($quoteCountertops as $quoteCountertop)
        {
            $newQuoteCountertop = $quoteCountertop->replicate();
            $newQuoteCountertop->quote_id = $newQuote->id;
            $newQuoteCountertop->save();
        }

        //duplicate quote_question_answers
        $quoteQuestionAnswers = QuoteQuestionAnswer::where('quote_id', $quote_id)->get();
        foreach($quoteQuestionAnswers as $quoteQuestionAnswer)
        {
            $newQuoteQuestionAnswer = $quoteQuestionAnswer->replicate();
            $newQuoteQuestionAnswer->quote_id = $newQuote->id;
            $newQuoteQuestionAnswer->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote duplicated.'
          ]
        );
    }

    /**
     * Set Archive Quote
     * @return json
     */
    public function setArchived(Request $request)
    {
        $quote = Quote::where('id', $request->quote_id)->first();
        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote not found.'
              ]
            );
        }
        $archived = '1';
        if($quote->closed == '1') $archived = '0';
        else if($quote->closed == '0') $archived = '1';

        $quote->closed = $archived;
        $quote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Archive set.'
          ]
        );
    }

    /**
     * Set Delete Quote
     * @return json
     */
    public function delete(Request $request)
    {
        $quote = Quote::where('id', $request->quote_id)->first();
        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Quote not found.'
              ]
            );
        }

        $quote->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Deleted set.'
          ]
        );
    }

    public function downloadFile($id, $file_id, Request $request)
    {
        $file = File::find($file_id);

        return response()->download(public_path("app/") . $file->location);
    }

    public function deleteFile(Request $request)
    {
        $file_id = $request->file_id;
        $file = File::find($file_id);
        @unlink(public_path('app') . '/' . $file->location);
        $file->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'File deleted.'
          ]
        );
    }

    public function quoteSaveTask(Request $request)
    {
      $subject = $request->subject;
      $due_date = $request->due_date;
      $due_time = $request->due_time;
      $urgent = $request->urgent;
      $assigned_id = $request->assigned_id;
      $body = $request->body;
      $quote_id = $request->quote_id;

      if($subject == '' || $body == '')
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Subject and Details cannot empty.'
            ]
          );
      }

      $quote = Quote::find($quote_id);
      $lead = Lead::find($quote->lead_id);
      $customer_id = 0;
      if($lead) $customer_id = $lead->customer_id;

      $task = new Task;
      $task->user_id = Auth::user()->id;
      $task->assigned_id = $assigned_id;
      $task->subject = $subject;
      $task->body = $body;
      $task->job_id = '0';
      $task->customer_id = $customer_id;
      $task->urgent = $urgent;
      $task->closed = 0;
      if ($request->due_date != '')
      {
          $due_date = Carbon::parse($due_date)->format('Y-m-d');
      }
      if ($request->due_time != '')
      {
          $due_time = Carbon::parse($due_date)->format('H:i:s');
      }

      if($due_date != '' && $due_time != '') $task->due = $due_date . ' ' . $due_time;

      $task->save();

      if ($task->assigned_id > 0)
      {
        $user = User::find($task->assigned_id);
        $user->task_id = $task->id;
        $user->save();

        $customer = Customer::find($task->customer_id);
        $urgentMessage = ($task->urgent) ? "** URGENT ** (Reply TC or LM when Complete) - " : null;
        \FK3\vl\core\SMS::command('directory.send',
                  ['target' => $user->mobile,
                  'message' => "($task->id) $customer {$urgentMessage} New Task: $task->subject : $task->body"]);
      }

      // Create a Google Calendar Event
      try
      {
        if ($due_date != '')
        {
          $params = [];
          $params['title'] = $task->subject;
          $params['location'] = "Task #{$task->id} in frugalk.com";
          $params['description'] = $task->body;
          $params['start'] = Carbon::parse($task->due);
          $params['end'] = Carbon::parse($task->due)->addMinutes(30);
          \FK3\vl\core\Google::event(User::find($task->assigned_id), $params);
        }
      }
      catch (Exception $e)
      {

      }

      return Response::json(
        [
          'response' => 'success',
          'message' => 'Task Added.'
        ]
      );
    }

    /**
     * Display Quote Details
     * @return Array
     */
    public function displayQuoteDetails(Request $request)
    {
      $quote_id = $request->quote_id;

      $newItems = array();
      $total = 0;
      $quoteDetailsRows = $this->quoteGenerator->getQuoteDetails();
      for($x = 0; $x < count($quoteDetailsRows); $x++)
      {
          $newItems[] = $quoteDetailsRows[$x];
          $total++;
      }

      return array( 	'iTotalRecords' => $total,
            'iTotalDisplayRecords' => $total,
            'data' => $newItems
          );
    }

    public function cabinets($id, Request $request)
    {
        $quoteCabinets = QuoteCabinet::where('quote_id', $id)
                                      ->get();

        $quote = Quote::find($id);
        $quoteType = QuoteType::find($quote->quote_type_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $cabinets = Cabinet::orderBy('frugal_name', 'asc')->get();

        $next = $this->checkNextStep($quote);

        return view('quote.cabinet', compact('quoteCabinets', 'quote', 'quoteType', 'lead', 'customer', 'cabinets', 'next'));
    }

    public function checkNextStep($quote)
    {
        $next = '';
        $quoteCabinetColorCount = QuoteCabinet::where('quote_id', $quote->id)->where('color', '')->count();
        if ($quote->cabinets()->count() > 0 && $quoteCabinetColorCount == 0)
        {
            if ($quote->type->name == 'Cabinet Only')
            {
                $next .= '';
            }
            else if ($quote->type->name == 'Cabinet and Install' || $quote->type->name == 'Builder')
            {
              $next .= '<a href="' . route('quote_accessories', ['id' => $quote->id]) . '" class="btn btn-info"><i class="fa fa-arrow-right"></i> Next</a>';
            }
            else
            {
              $next .= '<a href="' . route('quote_granite', ['id' => $quote->id]) . '" class="btn btn-info"><i class="fa fa-arrow-right"></i> Next</a>';
            }
        }
        return $next;
    }

    public function getCheckNextStep(Request $request)
    {
        $quote = Quote::find($request->quote_id);

        $next = $this->checkNextStep($quote);

        return Response::json(
          [
            'response' => 'success',
            'data' => $next
          ]
        );
    }

    public function displayQuoteCabinets(Request $request)
    {
        $quote_id = $request->quote_id;

        $items = QuoteCabinet::leftJoin('cabinets', 'quote_cabinets.cabinet_id', '=', 'cabinets.id')
                            ->leftJoin('vendors', 'cabinets.vendor_id', '=', 'vendors.id')
                            ->select(
                                      'quote_cabinets.*',
                                      'cabinets.frugal_name as cabinetFrugalName',
                                      'vendors.colors as vendorColors'
                                    )
                            ->where('quote_id', $quote_id);
        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $cab = ($item->cabinetFrugalName) ? $item->cabinetFrugalName : "Select Cabinet";
            	if ($cab == 'Select Cabinet')
            	{
            		$pass = false;
            	}

            	$color = ($item->color) ? $item->color : "No Color";
            	$colorPulse = ($item->cabinetFrugalName && $item->vendorColors &&  ! $item->color) ? "class='pulse-red'" : null;
            	if ($colorPulse)
            	{
            		$pass = false;
            	}

            	$inches = ($item->inches) ? $item->inches : "On floor";
            	$price  = $item->price;
            	if ( ! $price)
            	{
            		$pass = false;
            	}

            	// now make them all x-editable
                //if ($cabinet->customer_removed) $cabinet->description .= "<br/><small>** Cabinet being removed by customer! ** </small>";
              $pulse = ( ! $item->cabinetFrugalName) ? "class='pulse-red'" : null;

              $objItems[] = $item->description;
              $objItems[] = "<a href='#' onclick='ShowModalCabinetEdit(" . $item->id . ")' title='Edit'>$cab</a>";
              $objItems[] = $price;
              $objItems[] = "<a href='#' onclick='ShowModalCabinetEdit(" . $item->id . ")' title='Edit'><span {$colorPulse}>$color</span></a>";
              $objItems[] = $inches;
              $objItems[] = "<a href='#' onclick='ShowModalCabinetDeleteConfirm(" . $item->id . ")' title='Remove'><i class='fa fa-trash-o'></i></a>";

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function uploadFileCabinet($id, Request $request)
    {
        $quote = Quote::find($id);
        if ($request->has('xml'))
        {
            $this->processUploadedXML($id, $request);
            return Redirect::to("/quote/$quote->id/cabinets");
        }
        $quote->save();
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function processUploadedXML($id, Request $request)
    {
        $file = $request->file('xml');
        $xml = file_get_contents($file->getRealPath());
        $quote = Quote::find($id);
        QuoteGeneratorNew::setCabinetData($quote, $xml);
        return Redirect::to("/quote/$quote->id/cabinets");
    }

    public function getQuoteCabinetData(Request $request)
    {
        $quoteCabinet = QuoteCabinet::find($request->quote_cabinet_id);
        if(!$quoteCabinet)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            $colorList = '';
            if($quoteCabinet->cabinet)
            {
              $colors = $quoteCabinet->cabinet->vendor->colors;
              $colors = explode("\n", $colors);
              $oneColorOnly = false;
              if(count($colors) == 1) $oneColorOnly = true;
              foreach ($colors AS $color)
          		{
                $selected = '';
                $color = trim(preg_replace('/\s+/', ' ', $color));
                if($quoteCabinet->color == $color || $oneColorOnly)
                {
                    $selected = 'selected';
                }
          			$colorList .= '<option value="' . $color . '" ' . $selected . '>' . $color . '</option>';
          		}
            }

            return Response::json(
              [
                'response' => 'success',
                'cabinet_id' => $quoteCabinet->cabinet_id,
                'price' => $quoteCabinet->price,
                'color' => $colorList,
                'inches' => $quoteCabinet->inches,
                'description' => $quoteCabinet->description,
                'are_we_removing_cabinets' => $quoteCabinet->are_we_removing_cabinets
              ]
            );
        }
    }

    public function saveQuoteCabinetData(Request $request)
    {
        $quoteCabinet = QuoteCabinet::find($request->quote_cabinet_id);
        if(!$quoteCabinet)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $cabinet_id = $request->cabinet_id;
        $price = $request->price;
        $color = $request->color;

        $colors = $quoteCabinet->cabinet->vendor->colors;
        $colors = explode("\n", $colors);
        $oneColorOnly = false;
        if(count($colors) == 1) $oneColorOnly = true;
        foreach ($colors AS $colorVendor)
        {
          $selected = '';
          $color = trim(preg_replace('/\s+/', ' ', $color));
          if($oneColorOnly)
          {
              $color = $colorVendor;
          }
        }

        $inches = $request->inches;
        $description = $request->description;
        $are_we_removing_cabinets = $request->are_we_removing_cabinets;

        $quoteCabinet->cabinet_id = $cabinet_id;
        $quoteCabinet->price = $price ? $price : 0;
        $quoteCabinet->color = $color ? $color : '';
        $quoteCabinet->inches = $inches ? $inches : 0;
        $quoteCabinet->description = $description ? $description : '';
        $quoteCabinet->are_we_removing_cabinets = $are_we_removing_cabinets;
        $quoteCabinet->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Cabinet Edited'
          ]
        );
    }

    public function deleteQuoteCabinetData(Request $request)
    {
        $quote_cabinet_id = $request->quote_cabinet_id;

        $quoteCabinet = QuoteCabinet::find($quote_cabinet_id);
        if(!$quoteCabinet)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }

        $quoteCabinet->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Cabinet Deleted.'
          ]
        );
    }

    public function paperwork($id)
    {
        $quote = Quote::find($id);
        $quote->paperwork = 1;
        $quote->save();
        \FK3\vl\leads\StatusManager::setlead($quote->lead, 10);
        return redirect()->back();
    }

    public function needsPaperwork($id)
    {
        $quote = Quote::find($id);
        \FK3\vl\leads\StatusManager::setlead($quote->lead, 11);
        return redirect()->back();
    }

    public function getQuoteType(Request $request)
    {
        $quote = Quote::find($request->quote_id);

        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            $quoteType = QuoteType::find($quote->quote_type_id);

            return Response::json(
              [
                'response' => 'success',
                'quote_type_id' => @$quoteType->id
              ]
            );
        }
    }

    public function setQuoteType(Request $request)
    {
      $quote = Quote::find($request->quote_id);
      $quote_type_id = $request->quote_type_id;

      if(!$quote)
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'No Data'
            ]
          );
      }
      else
      {
          $quote->quote_type_id = $quote_type_id;
          $quote->save();

          $quoteType = QuoteType::find($quote->quote_type_id);

          return Response::json(
            [
              'response' => 'success',
              'message' => 'Quote Type set',
              'quote_type_id' => $quoteType->id,
              'name' => $quoteType->name
            ]
          );
      }
    }

    public function getQuoteTitle(Request $request)
    {
        $quote = Quote::find($request->quote_id);

        if(!$quote)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'success',
                'title' => $quote->title ? $quote->title : 'Main Quote'
              ]
            );
        }
    }

    public function setQuoteTitle(Request $request)
    {
      $quote = Quote::find($request->quote_id);
      $quote_title = $request->quote_title;

      if(!$quote)
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'No Data'
            ]
          );
      }
      else
      {
          $quote->title = $quote_title;
          $quote->save();

          return Response::json(
            [
              'response' => 'success',
              'message' => 'Quote Title set',
              'title' => $quote->title
            ]
          );
      }
    }

    public function led($id, Request $request)
    {
        $quote = Quote::find($id);

        return view('quote.stages.led', compact('quote', 'request'));
    }

    public function displayTiles(Request $request)
    {
        $quote_id = $request->quote_id;

        $quoteTiles = QuoteTile::where('quote_id', $quote_id)->get();

        $total = 0;
        $newItems = array();
        foreach($quoteTiles as $tile)
        {
            $objItems = array();
            $objItems[] = $tile->description . '<a href="#" style="float:right;" onclick="DeleteTile(' . $tile->id . ')"><i class="fa fa-trash"></i></a>';
            $objItems[] = $tile->linear_feet_counter;
            $objItems[] = $tile->backsplash_height;
            $objItems[] = $tile->pattern;
            $objItems[] = $tile->sealed;
            $newItems[] = $objItems;

            $total++;
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveTiles(Request $request)
    {
        $quote_id = $request->quote_id;

        $description = $request->description;
        $linear_feet_counter = $request->linear_feet_counter;
        $backsplash_height = $request->backsplash_height;
        $pattern = $request->pattern;
        $sealed = $request->sealed;

        $quoteTile = new QuoteTile();
        $quoteTile->quote_id = $quote_id;
        $quoteTile->description = $description ? $description : 'Main Tile';
        $quoteTile->linear_feet_counter = ( is_float($linear_feet_counter) || is_numeric($linear_feet_counter) ) ? $linear_feet_counter : 0;
        $quoteTile->backsplash_height = ( is_float($backsplash_height) || is_numeric($backsplash_height) ) ? $backsplash_height : 0;
        $quoteTile->pattern = $pattern;
        $quoteTile->sealed = $sealed;
        $quoteTile->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Tile Added.'
          ]
        );
    }

    public function deleteTiles(Request $request)
    {
        QuoteTile::find($request->id)->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Quote Tile Deleted.'
          ]
        );
    }

    function saveLed($id, Request $request)
    {
      $quote = Quote::find($id);
      $meta = unserialize($quote->meta);

      if ($request->has('quote_led_12'))
      {
          $meta['meta']['quote_led_12'] = $request->quote_led_12;
      }
      if ($request->has('quote_led_60'))
      {
          $meta['meta']['quote_led_60'] = $request->quote_led_60;
      }
      if ($request->has('quote_led_transformers'))
      {
          $meta['meta']['quote_led_transformers'] = $request->quote_led_transformers;
      }
      if ($request->has('quote_led_connections'))
      {
          $meta['meta']['quote_led_connections'] = $request->quote_led_connections;
      }
      if ($request->has('quote_led_couplers'))
      {
          $meta['meta']['quote_led_couplers'] = $request->quote_led_couplers;
      }
      if ($request->has('quote_led_switches'))
      {
          $meta['meta']['quote_led_switches'] = $request->quote_led_switches;
      }
      if ($request->has('quote_led_feet'))
      {
          $meta['meta']['quote_led_feet'] = $request->quote_led_feet;
      }
      if ($request->has('quote_puck_lights'))
      {
          $meta['meta']['quote_puck_lights'] = $request->quote_puck_lights;
      }

      $meta['meta']['progress_led'] = true;

      $meta = serialize($meta);
      $quote->meta = $meta;
      $quote->save();

      return redirect()->back()->with('success', 'LED Information Updated');
    }

    public function addons($id, Request $request)
    {
        $quote = Quote::find($id);
        $addons = Addon::where('active', '1')->orderBy('item')->get();

        return view('quote.stages.addons', compact('quote', 'addons', 'request'));
    }

    public function displayAddons(Request $request)
    {
        $quote_id = $request->quote_id;

        $quoteAddons = QuoteAddon::leftJoin('addons', 'quote_addons.addon_id', '=', 'addons.id')
                                  ->select(
                                            'quote_addons.*',
                                            'addons.item as addonsItem'
                                          )
                                  ->where('quote_addons.quote_id', $quote_id)
                                  ->get();

        $total = 0;
        $newItems = array();
        foreach($quoteAddons as $addon)
        {
            $objItems = array();
            $objItems[] = '<a href="#" onclick="EditAddon(' . $addon->id . ')">' . $addon->addonsItem . '</a><br/><small>' . $addon->description . '</small><a href="#" style="float:right;" onclick="DeleteAddon(' . $addon->id . ')"><i class="fa fa-trash"></i></a>';
            $objItems[] = $addon->qty;
            $objItems[] = $addon->price;
            $objItems[] = $addon->qty * $addon->price;
            $newItems[] = $objItems;

            $total++;
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveAddons(Request $request)
    {
        $quote_id = $request->quote_id;
        $item_id = $request->item_id;
        $qty = $request->qty;
        $price = $request->price;
        $description = $request->description;

        if($item_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select an item.'
            ]
          );
        }

        $quoteAddon = new QuoteAddon();
        $quoteAddon->quote_id = $quote_id;
        $quoteAddon->addon_id = $item_id;

        $price = ( is_float($price) || is_numeric($price) ) ? $price : 0;
        if($price == 0) $price = Addon::find($item_id)->price;
        $quoteAddon->price = $price;
        $quoteAddon->qty = ( is_numeric($qty) ) ? $qty : 1;
        $quoteAddon->description = $description;
        $quoteAddon->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Addon Item Added.'
          ]
        );
    }

    public function updateAddons(Request $request)
    {
        $quote_id = $request->quote_id;
        $quote_addon_id = $request->quote_addon_id;
        $item_id = $request->item_id;
        $qty = $request->qty;
        $price = $request->price;
        $description = $request->description;

        if($item_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select an item.'
            ]
          );
        }
        $quoteAddon = QuoteAddon::find($quote_addon_id);
        if(!$quoteAddon) $quoteAddon = new QuoteAddon();
        $quoteAddon->quote_id = $quote_id;
        $quoteAddon->addon_id = $item_id;

        $price = ( is_float($price) || is_numeric($price) ) ? $price : 0;
        if($price == 0) $price = Addon::find($item_id)->price;
        $quoteAddon->price = $price;
        $quoteAddon->qty = ( is_numeric($qty) ) ? $qty : 1;
        $quoteAddon->description = $description;
        $quoteAddon->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Addon Item Updated.'
          ]
        );
    }

    public function deleteAddons(Request $request)
    {
        $id = $request->id;
        QuoteAddon::find($id)->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Addon Item Deleted.'
          ]
        );
    }

    public function getQuoteAddons(Request $request)
    {
        $id = $request->quote_addon_id;
        $quoteAddon = QuoteAddon::find($id);

        return Response::json(
          [
            'response' => 'success',
            'quote_addon_id' => $quoteAddon->id,
            'item_id' => $quoteAddon->addon_id,
            'price' => $quoteAddon->price,
            'qty' => $quoteAddon->qty,
            'description' => $quoteAddon->description
          ]
        );
    }

    public function displayResponsibility(Request $request)
    {
        $quote_id = $request->quote_id;

        $responsibilties = Responsibility::where('active', '1')->orderBy('name', 'asc')->get();

        $total = 0;
        $newItems = array();
        foreach($responsibilties as $responsibility)
        {
            $objItems = array();

            $quoteRes = QuoteResponsibility::where('quote_id', $quote_id)
                                            ->where('responsibility_id', $responsibility->id)
                                            ->first();

            $checked = '';
            if($quoteRes) $checked = 'checked';

            $objItems[] = '<input type="checkbox" name="rs_' . $responsibility->id . '" id="rs_' . $responsibility->id . '" ' . $checked . '/>';
            $objItems[] = $responsibility->name;
            $newItems[] = $objItems;

            $total++;
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveResponsibilty($id, Request $request)
    {
        $quote = Quote::find($id);
        // Lets itterate through all available responsibilities and add/remove if necessary.
        foreach (Responsibility::all() as $r)
        {
            $quote->responsibilities()->whereResponsibilityId($r->id)->delete();
            if ($request->has("rs_$r->id")) // checked.
            {
                (new QuoteResponsibility)->create([
                    'quote_id'          => $id,
                    'responsibility_id' => $r->id
                ]);
            }
        }
        return redirect()->back()->with('success', 'Quote Responsibilities set.');
    }

    public function questionaire($id)
    {
        $quote = Quote::find($id);

        return view('quote.stages.questionaire', compact('quote', 'request'));
    }

    public function saveQuestionaire($id, Request $request)
    {
        $quote = Quote::find($id);
        foreach ($request->all() AS $key => $val)
        {
            if (preg_match("/question_/i", $key))
            {
                $key = str_replace("question_", null, $key);
                $question = QuoteQuestion::find($key);
                if (!$val && $val != '0')
                {
                        return redirect()->back()->with('error', 'You must answer "' . $question->question . '"')->withInput($request->all());

                }
                $answer = QuoteQuestionAnswer::whereQuoteId($quote->id)
                    ->whereQuestionId($key)
                    ->first();
                if (!$answer)
                {
                    $answer = new QuoteQuestionAnswer;
                }

                if ($val == 'on')
                {
                    $val = 'Y';
                }

                $answer->question_id = $key;
                $answer->quote_id = $quote->id;
                $answer->answer = $val;
                $answer->group_id = '2';
                $answer->active = '1';
                $answer->save();

            }
        }
        $meta = unserialize($quote->meta);
        $meta['meta']['progress_questionaire'] = true;
        $quote->meta = serialize($meta);
        $quote->save();

        return redirect()->back()->with('success', 'Question Answered set.');
    }

    public function additional($id)
    {
        $quote = Quote::find($id);
        $promotions = Promotion::whereActive(true)->get();

        return view('quote.stages.additional', compact('quote', 'promotions', 'request'));
    }

    public function additionalSave($id, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if ($this->validatePrices($request) !== true)
        {
            return redirect()->back()->with('error', '<b>Price Validation Failed.</b> All misc items, plumbing, electrical and installer items must be entered in ITEM - PRICE format!')->withInput($request->all());
        }

        $meta['meta']['quote_misc'] = $request->quote_misc;
        $meta['meta']['quote_plumbing_extras'] = $request->quote_plumbing_extras;
        $meta['meta']['quote_electrical_extras'] = $request->quote_electrical_extras;
        $meta['meta']['quote_installer_extras'] = $request->quote_installer_extras;
        $meta['meta']['quote_special'] = $request->quote_special;
        $meta['meta']['quote_coupon'] = $request->quote_coupon;
        $meta['meta']['quote_discount'] = $request->quote_discount;
        $meta['meta']['quote_discount_reason'] = $request->quote_discount_reason;
        $meta['meta']['progress_additional'] = true;

        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->promotion_id = $request->promotion_id;
        $quote->save();

        return redirect()->back()->with('success', 'Additional Data set.');
    }

    private function validatePrices($request)
    {
        if ($request->quote_misc)
        {
            foreach (explode("\n", trim($request->quote_misc)) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if ($request->quote_plumbing_extras)
        {
            foreach (explode("\n", trim($request->quote_plumbing_extras)) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if ($request->quote_electrical_extras)
        {
            foreach (explode("\n", trim($request->quote_electrical_extras)) AS $items)
            {
                $x = explode("-", $items);
                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        if (trim($request->quote_installer_extras))
        {
            foreach (explode("\n", $request->quote_installer_extras) AS $items)
            {
                $x = explode("-", $items);
                // dd($x);

                if (!isset($x[1]) || !$x[1]) return false;
                if (!is_numeric(trim($x[1]))) return false;
            }
        }
        return true;
    }

    public function hardware($id, Request $request)
    {
      $quote = Quote::find($id);

      return view('quote.stages.hardware', compact('quote', 'request'));
    }

    public function hardwareSave($id, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if ($request->has('pulls'))
        {
            $pulls = [];
            $locations = [];
            foreach ($request->all() AS $key => $val)
            {
                if (preg_match("/pull_/i", $key))
                {
                    $key = str_replace("pull_", null, $key);
                    if ($val > 0)
                    {
                        $pulls[$key] = $val;
                    }
                }
                if (preg_match("/location_/i", $key))
                {
                    $key = str_replace("location_", null, $key);
                    if ($val != '')
                    {
                        $locations[$key] = $val;
                    }
                }
            }

            $meta['meta']['quote_pulls'] = $pulls;
            $meta['meta']['quote_pulls_location'] = $locations;
            $meta['meta']['progress_pulls'] = true;

        } // if Pulls

        if ($request->has('knobs'))
        {
            $knobs = [];
            $locations = [];
            foreach ($request->all() AS $key => $val)
            {
                if (preg_match("/knob_/i", $key))
                {
                    $key = str_replace("knob_", null, $key);
                    if ($val > 0)
                    {
                        $knobs[$key] = $val;
                    }
                }

                if (preg_match("/location_/i", $key))
                {
                    $key = str_replace("location_", null, $key);
                    if ($val != '')
                    {
                        $locations[$key] = $val;
                    }
                }
            }

            $meta['meta']['quote_knobs'] = $knobs;
            $meta['meta']['quote_knobs_location'] = $locations;
            $meta['meta']['progress_knobs'] = true;

        } // if Pulls
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();

        return redirect()->back()->with('success', 'Hardware in Quote Set.');
    }

    public function hardwareDelete($id, $type, $hid)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        switch ($type)
        {
            case 'pull':
                foreach ($meta['meta']['quote_pulls'] AS $pull => $qty)
                {
                    if ($pull == $hid)
                    {
                        unset($meta['meta']['quote_pulls'][$pull]);
                        unset($meta['meta']['quote_pulls_location'][$pull]);
                    }
                }

                break;
            case 'knob':
                foreach ($meta['meta']['quote_knobs'] AS $knob => $qty)
                {
                    if ($knob == $hid)
                    {
                        unset($meta['meta']['quote_knobs'][$knob]);
                        unset($meta['meta']['quote_knobs_location'][$knob]);
                    }
                }

                break;
        }
        $quote->meta = serialize($meta);
        $quote->save();

        return redirect()->back()->with('success', 'Hardware in Quote Deleted.');

    }

    public function getQuoteCabinetXml(Request $request)
    {
        $quote_id = $request->quote_id;
        $cabinet_id = $request->cabinet_id;

        $cabinet = QuoteCabinet::find($cabinet_id);
        if (!isset($cabinet->cabinet->frugal_name))
        {
            $cabList = "Unassigned Cabinet Name";
        }
        else
        {
            $cabList = "<span style='font-size:10px;'>";
        }

        $cabData = unserialize($cabinet->data);
        $instItems = 0;
        $cabItems = 0;
        $attCount = 0;
        foreach ($cabData AS $item)
        {
            if (!isset($item['attachment']))
            {
                if (!isset($item['description']))
                {
                    $item['description'] = null;
                }

                $cabList .= "($item[sku]) - $item[description] x " . $item['qty'] . " - $item[price]<br/>";
                $cabItems += $item['qty'];
                $instItems += $item['qty']; // Installer items.
            }
            else
            {
                if (!isset($item['description']))
                {
                    $item['description'] = null;
                }

                $cabList .= "Attachment: ($item[sku]) - $item[description] x " . $item['qty'] . "- $item[price]<br/> ";
                $attCount += $item['qty'];
                $cabItems += $item['qty'];
            }
        }

        $cabList = nl2br($cabList) . "</span>";
        if ($cabinet->wood_xml)
        {
            $cabList .= "<h5>Additional Wood Products added to Order</h5>";
            foreach (QuoteGeneratorNew::returnWoodArray($cabinet) as $wood)
            {
                $cabList .= "($wood[sku]) - $wood[description] x " . $wood['qty'] . " - $wood[price]<br/>";

            }
        }
        return Response::json(
          [
            'response' => 'success',
            'cab_name' => '<h4>' . $cabinet->cabinet->frugal_name . '</h4>',
            'data' => $cabList
          ]
        );
    }

    public function contract($id, Request $request)
    {
        $quote = Quote::find($id);

        $html = View::make('pdf.contract')
            ->withQuote($quote)
            ->render();
        if ($request->show)
        {
            return $html;
        }
        else
        {
            return PDF::loadHtml($html)->setPaper('a4', 'portrait')->setWarnings(true)->download('quote_contract_' . $id . '_' . Carbon::parse(now())->toDateTimeString() . '.pdf');
        }
    }

    public function getQuoteSnapshots(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = Quote::find($quote_id);

        $data = '';
        foreach ($quote->snapshots AS $shot)
        {
          $headers = '';
          $debug = unserialize($shot->debug);
          $headers = '<thead><th>Item</th><th>Amount</th><th>Total</th></thead>';
          $trow = $debug;
          $data .= '<td>' . $shot->created_at->format('m/d/y h:i a') . '</td>';
          $data .= '<td><a href="/snapshots/$quote->id/$shot->location">' . $shot->location . '</a></td>';
          $data .= '<td>' . $headers . '</td>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function getQuoteAppliances(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = Quote::find($quote_id);

        $meta = unserialize($quote->meta)['meta'];
        $appids = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];

        // First check and make sure we have the records written.
        foreach ($appids as $app)
        {
            if ($quote->appliances()->whereApplianceId($app)->count() == 0)
                (new QuoteAppliance)->create([
                   'quote_id' => $quote->id,
                   'appliance_id' => $app,
                   'brand' => '',
                   'model' => '',
                   'size' => ''
                ]);
        }

        $data = '';
        $headers = '';
        $headers = '<thead><th>Appliance</th><th>Brand</th><th>Model</th><th>Size</th></thead>';
        foreach ($quote->appliances AS $appliance)
        {
          $data .= '<tr>';
          $data .= '<td>' . $appliance->appliance->name . '</td>';
          $data .= '<td><input type="text" name="app_' . $appliance->id . '_brand" value="' . $appliance->brand . '"></td>';
          $data .= '<td><input type="text" name="app_' . $appliance->id . '_model" value="' . $appliance->model . '"></td>';
          $data .= '<td><input type="text" name="app_' . $appliance->id . '_size" value="' . $appliance->size . '"></td>';
          $data .= '</tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $headers . $data
          ]
        );
    }

    /**
     * Save appliance settings
     * @param $id
     */
    public function appSettingsSave($id, Request $request)
    {
        foreach ($request->all() as $key => $val)
        {
            if (preg_match("/app_/", $key))
            {
                $key = str_replace("app_", null, $key);
                $x = explode("_", $key);
                $aid = $x[0]; // _id
                $type = $x[1]; //_brand, model size
                QuoteAppliance::find($aid)->update([$type => $val ?: '']);
            }
        }
        return redirect(route('quotes.index'))->with('success', 'Appliance Settings Saved');
    }

    /**
     * Send customer a link to fill in their appliances makes and models.
     * @param $id
     */
    public function appSettingsSend($id)
    {
        $quote = Quote::find($id);
        $data = [
            'quote' => $quote,

        ];
        $contact = $quote->lead->customer->contacts()->first();
        Mail::send('emails.appliances', $data, function ($message) use ($contact) {
            $message->to([
                $contact->email => $contact->name,
                //       'orders@frugalkitchens.com' => 'Frugal Orders'
            ])->subject("IMPORTANT! Please confirm your appliances for your Frugal Kitchens and Cabinets Job");
        });
        return redirect(route('quotes.index'))->with('success', 'Appliance Settings Sent');
    }

    public function appliances($id, Request $request)
    {
        $quote = Quote::find($id);

        if ($request->has('moving')) // ajax processing to the page to allow movement
        {
            if ($quote->type->name == 'Full Kitchen' || $quote->type->name == 'Granite Only')
            {
                $meta = unserialize($quote->meta);
                if ($quote->granites()->count() == 0)
                {
                    return redirect()->back()->with('error', 'Granite information is missing. Please click save on each granite column.');
                }
                return redirect(route('quote_appliances', ['id' => $quote->id]));
            }
            return redirect(route('quote_appliances', ['id' => $quote->id]));

        }
        $sinks = Sink::orderBy('name')->where('active', '1')->get();
        return view('quote.stages.appliances', compact('quote', 'sinks'));
    }

    public function getSinkData($quote_id)
    {
        $quote = Quote::find($quote_id);

        $meta = unserialize($quote->meta);
        $meta = $meta['meta'];
        if ( ! isset($meta['sinks']))
        {
          $meta['sinks'] = [];
        }
        $sinkData = '';
        foreach ($meta['sinks'] AS $idx => $sink)
        {
          if ($sink)
          {
            if ( ! isset($meta['sink_plumber']))
            {
              $meta['sink_plumber'] = [];
            }

            $plumber = (in_array($sink, $meta['sink_plumber'])) ? "<span class='pull-right'><i class='fa fa-check'></i></span>" : null;
            $sinkData .= '<tr><td>' . Sink::find($sink)->name.$plumber.'</td><td><a href="' . route('quote_remove_sink', ['id' => $quote->id, 'instance' => $idx]) . '"><i class="fa fa-trash-o"></i></a></td></tr>';
          }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $sinkData
          ]
        );
    }

    public function saveSink($id, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        $meta['meta']['sinks'][] = $request->sink_id;
        if ($request->has('plumber_needed'))
        {
            $meta['meta']['sink_plumber'][] = $request->sink_id;
        }

        $quote->meta = serialize($meta);
        $quote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Sink Added'
          ]
        );
    }

    public function sinkDelete($id, $instance)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        if (!isset($meta['meta']['sinks']))
        {
            $meta['meta']['sinks'] = [];
        }
        unset($meta['meta']['sinks'][$instance]);
        $quote->meta = serialize($meta);
        $quote->save();

        return redirect()->back()->with('success', 'Sink Deleted');
    }

    public function displayAppliances(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = Quote::find($quote_id);
        $meta = unserialize($quote->meta);
        $meta = $meta['meta'];

        $applianceStore = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];

        $appliances = Appliance::where('active', '1')->orderBy('name', 'asc')->get();

        $total = 0;
        $newItems = array();
        foreach($appliances as $appliance)
        {
            $objItems = array();

            $checked = (in_array($appliance->id, $applianceStore)) ? 'checked' : null;

            $objItems[] = '<input type="checkbox" name="app_' . $appliance->id . '" id="app_' . $appliance->id . '" ' . $checked . '/>';
            $objItems[] = $appliance->name;
            $objItems[] = '$' . number_format($appliance->price, 2);
            $newItems[] = $objItems;

            $total++;
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveAppliance($id, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);

        if ($request->has('sink_id'))
        {
            $meta['meta']['sink_id'] = $request->sink_id;
            if ($request->has('sink_id2'))
            {
                $meta['meta']['sink_id2'] = $request->sink_id2;
            }
        }
        if ($request->has('appliances'))
        {
            $meta['meta']['quote_appliances'] = [];
            foreach ($request->all() AS $key => $val)
            {
                if (preg_match('/app_/', $key))
                {
                    $key = trim(str_replace("app_", null, $key));
                    $meta['meta']['quote_appliances'][] = $key;
                }
            }
        }
        $meta['meta']['progress_appliance'] = true;
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();
        return redirect()->back()->with('success', 'Quote Appliance set.');
    }

    public function accessories($id)
    {
        $quote = Quote::find($id);
        return view('quote.stages.accessories', compact('quote'));
    }

    public function displayAccessories(Request $request)
    {
        $quote_id = $request->quote_id;

        $quote = Quote::find($quote_id);
        $meta = unserialize($quote->meta);
        $meta = $meta['meta'];

        $accessoriesStore = (isset($meta['quote_accessories'])) ? $meta['quote_accessories'] : [];

        $accessories = Accessory::orderBy('name', 'ASC')->get();

        $total = 0;
        $newItems = array();
        foreach($accessories as $accessory)
        {
            $objItems = array();

            $value = (isset($accessoriesStore[$accessory->id])) ? $accessoriesStore[$accessory->id] : 0;

            $objItems[] = '<input type="text" name="acc_' . $accessory->id . '" style="width:50%" value="' . $value . '" />';
            $objItems[] = $accessory->sku;
            $objItems[] = $accessory->description;
            $objItems[] = '$' . number_format($accessory->price, 2);
            $newItems[] = $objItems;

            $total++;
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function accessoriesSave($id, Request $request)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);

        $accessories = [];
        foreach ($request->all() AS $key => $val)
        {
            if (preg_match("/acc_/i", $key))
            {
                $key = str_replace("acc_", null, $key);
                if ($val > 0)
                {
                    $accessories[$key] = $val;
                }
            }
        }

        $meta['meta']['progress_accessories'] = true;
        $meta['meta']['quote_accessories'] = $accessories;
        $meta = serialize($meta);
        $quote->meta = $meta;
        $quote->save();

        return redirect()->back()->with('success', 'Quote Accessories set.');
    }

    public function displayAccessoriesInQuote(Request $request)
    {
        $quote = Quote::find($request->quote_id);

        $meta = unserialize($quote->meta);
        $meta = $meta['meta'];

        $data = '';
        foreach ($meta['quote_accessories'] AS $idx => $acc)
        {
            $accessory = Accessory::find($idx);
            $data .= '<tr>';
            $data .= '<td>' . $accessory->sku . '</td>';
            $data .= '<td>' . $acc . '</td>';
            $data .= '<td><a class="get" href="' . route('quote_remove_accessories', ['id' => $quote->id, 'aid' => $idx]) . '"><i class="fa fa-trash"></i></a></td>';
            $data .= '</tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function accessoryRemove($id, $aid)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta);
        foreach ($meta['meta']['quote_accessories'] AS $idx => $acc)
        {
            if ($idx == $aid)
            {
                unset($meta['meta']['quote_accessories'][$idx]);
            }
        }

        $quote->meta = serialize($meta);
        $quote->save();

        return redirect()->back()->with('success', 'Quote Accessories removed.');
    }

    public function granite($id, Request $request)
    {
        $quote = Quote::find($id);
        if ($request->has('del'))
        {
            QuoteGranite::find($request->del)->delete();
            return redirect()->back()->with('success', 'Granite deleted.');
        }
        if ($request->has('moving')) // ajax processing to the page to allow movement
        {
            if ($quote->type->name == 'Full Kitchen')
            {
                $meta = unserialize($quote->meta);
                if (!isset($meta['meta']['cabinet_id']) || !isset($meta['meta']['cabinet_price']))
                {
                    return redirect()->back()->with('error', 'No cabinet information was found. Please make sure to click Update Primary/Secondary Cabinets before continuing.');
                }
                if (!$meta['meta']['cabinet_id'] || !$meta['meta']['cabinet_price'])
                {
                    return redirect()->back()->with('error', 'No cabinet information was found. Please make sure to click Update Primary/Secondary Cabinets before continuing.');
                }
                return redirect()->back();
            }
        }

        return view('quote.stages.granite', compact('quote', 'request'));
    }

    public function displayGranite(Request $request)
    {
        $quote = Quote::find($request->quote_id);

        $data = '';
        foreach ($quote->granites AS $granite)
        {
            $data .= '<tr>';
            $data .= '<td><a href="' . route('quote_granite', ['id' => $quote->id]) . '?granite_id=' . $granite->id . '">' . $granite->description . '</a> <span class="pull-right"><a href="' . route('quote_granite', ['id' => $quote->id]) . '?del=' . $granite->id . '"><i class="fa fa-trash-o"></i></a></span></td>';
            $data .= '<td>' . (($granite->granite && !$granite->granite_override) ? $granite->granite->name : $granite->granite_override) . '</td>';
            $data .= '<td>' . $granite->removal_type . '</td>';
            $data .= '<td>' . $granite->measurements . '</td>';
            $data .= '<td>' . $granite->backsplash_height . '</td>';
            $data .= '<td>' . sprintf("%d/%d", $granite->island_width, $granite->island_length) . '</td>';
            $data .= '<td>' . sprintf("%d/%d", $granite->raised_bar_length, $granite->raised_bar_depth) . '</td>';
            $data .= '</tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function graniteSave($id, Request $request)
    {
        $quote = Quote::find($id);
        if ($request->has('update'))
        {
            $quote->picking_slab = $request->picking_slab;
            $quote->save();
            $g = ($request->g_id) ? QuoteGranite::find($request->g_id) : new QuoteGranite();
            $request->merge([
                'quote_id' => $id
            ]);
            if (!$request->description)
            {
                $request->merge(['description' => 'Kitchen']);
            }
            if (!$request->g_id)
            {
                $g = QuoteGranite::create($request->except(['updateGranite', 'g_id', 'update', 'picking_slab']));
            }
            else
            {
                if ($this->editable($quote))
                {
                    $g->update($request->except(['updateGranite', 'g_id', 'update', 'picking_slab']));
                }
                else
                {
                    $g->update(['granite_jo' => $request->granite_jo]);
                }
            }
        }

        return redirect(route('quote_granite', ['id' => $quote->id]))->with('success', 'Granites Requirements Updated.');
    }

    public function customerAppliances($id)
    {
        $quote = Quote::find($id);
        $meta = unserialize($quote->meta)['meta'];
        $appids = (isset($meta['quote_appliances'])) ? $meta['quote_appliances'] : [];

        // First check and make sure we have the records written.
        foreach ($appids as $app)
        {
            if ($quote->appliances()->whereApplianceId($app)->count() == 0)
                (new QuoteAppliance)->create([
                    'quote_id' => $quote->id,
                    'appliance_id' => $app,
                    'brand' => '',
                    'model' => '',
                    'size' => ''
                ]);
        }

        $data = '';
        foreach ($quote->appliances AS $app)
        {
            $data .= '<tr>';
            $data .= '<td>' . $app->appliance->name . '</td>';
            $data .= '<td><input name="app_' . $app->id . '_brand" value="' . $app->brand . '"></td>';
            $data .= '<td><input name="app_' . $app->id . '_model" value="' . $app->model . '"></td>';
            $data .= '<td><input name="app_' . $app->id . '_size" value="' . $app->size . '"></td>';
            $data .= '</tr>';
        }

        return view('quote.custappliances', compact('quote', 'data'));
    }

    /**
     * Save appliances from customer.
     * @param $id
     * @return redirect
     */
    public function customerAppliancesSave($id, Request $request)
    {
        foreach ($request->all() as $key => $val)
        {
            if (preg_match("/app_/", $key))
            {
                $key = str_replace("app_", null, $key);
                $x = explode("_", $key);
                $aid = $x[0]; // _id
                $type = $x[1]; //_brand, model size
                QuoteAppliance::find($aid)->update([$type => ($val ?: '')]);
            }
        }

        return view('quote.custappliancesthanks');
    }
}
