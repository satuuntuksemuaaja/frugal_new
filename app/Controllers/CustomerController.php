<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 3/16/18
 * Time: 5:50 PM
 */

namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Customer;
use FK3\Models\Quote;
use FK3\Models\Job;
use FK3\Models\JobNote;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationList;
use FK3\vl\core\Formatter;
use FK3\Models\Traits\DataTrait;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Auth;
use Carbon\Carbon;
use Response;

class CustomerController extends Controller
{
    use DataTrait;

    /**
     * Show index for customers.
     * @param Request $request
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            // We're formatting a table.
            return $this->renderTable($request);
        }
        return view('customers.index');
    }

    /**
     * Show create form for new customer
     * @return mixed
     */
    public function create()
    {
        return view('customers.create')->withCustomer(new Customer);
    }

    /**
     * Show customer profile.
     * @param Customer $customer
     * @return mixed
     */
    public function show(Customer $customer)
    {
        return view('customers.show')->withCustomer($customer);
    }

    /**
     * Store a new Customer
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->name || !$request->email || !$request->home)
        {
            throw new FrugalException("You must enter a name, email and home phone.");
        }

        (new Customer)->create($request->all());
        return ['callback' => "redirect:/customers"];
    }

    /**
     * Update customer properties.
     * @param Customer $customer
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Customer $customer, Request $request)
    {
        if (!$request->name || !$request->email || !$request->home)
        {
            throw new FrugalException("You must enter a name, email and home phone.");
        }
        $customer->update($request->all());
        return ['callback' => "redirect:/customers"];
    }

    /**
     * Ajax Handler for Datatables.
     * @param Request $request
     * @return array
     */
    public function renderTable(Request $request)
    {
        $this->dtLinkPrefix = "/customers/%d";
        $this->dtMap = [
            'name'      => [
                'link'       => 'id',
                'searchable' => true,
            ],
            'address'   => [
                'searchable' => true,
                'output'     => 'fullAddress'
            ],
            'email'     => ['searchable' => true],
            'home'      => ['searchable' => true],
            'mobile'    => ['searchable' => true],
            'alternate' => ['searchable' => true]
        ];
        $this->dtModel = Customer::class;
        return $this->dtRender($request);
    }

    public function displayCustomer(Request $request)
    {
        $items = Customer::where('active', '1')
                        ->orderBy('name', 'asc')
                        ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            $total = 0;
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="' . route('view_profile', ['id' => $item->id]) . '">' . (($item->name != '') ? $item->name : '--No Name--') . '</a><br/><a href="#" data-toggle="tooltip" title="Job Notes" onclick="ShowModalNotes(' . $item->id . ',\'' . (($item->name != '') ? $item->name : '--No Name--') . '\');"><i class="fa fa-check-square-o"></i></a>';
              $objItems[] = $item->address;
              $objItems[] = $item->email;
              $objItems[] = $item->email2;
              $objItems[] = $item->email3;
              $objItems[] = Formatter::numberFormat($item->home);
              $objItems[] = Formatter::numberFormat($item->mobile);
              $objItems[] = Formatter::numberFormat($item->alternate);

              $newItems[] = $objItems;

              $total++;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function displayCustomerJobNotes(Request $request)
    {
        $customer = Customer::find($request->customer_id);
        $quotes = $customer->quotes;
        $quoteArr = array();
        foreach($quotes as $quote)
        {
            $quoteArr[] = $quote->id;
        }

        $items = JobNote::join('jobs', 'job_notes.job_id', '=', 'jobs.id')
                        ->join('quotes', 'jobs.quote_id', '=', 'quotes.id')
                        ->join('users', 'job_notes.user_id', '=', 'users.id')
                        ->whereIn('quotes.id', $quoteArr)
                        ->select(
                                  'quotes.id as quote_id',
                                  'quotes.title as quote_title',
                                  'job_notes.id as job_notes_id',
                                  'job_notes.created_at as job_notes_created_at',
                                  'job_notes.note as job_notes_note',
                                  'users.name as user_name'
                                )
                        ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            $total = 0;
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="' . route('quote_view', ['id' => $item->quote_id]) . '">' . (($item->quote_title != '') ? $item->quote_title : '--No Name--') . '</a>';
              $objItems[] = Carbon::parse($item->job_notes_created_at)->format('m/d/Y H:i:s');
              $objItems[] = $item->job_notes_note;
              $objItems[] = $item->user_name;

              $newItems[] = $objItems;

              $total++;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function getCustomerQuotes(Request $request)
    {
        $customer = Customer::find($request->customer_id);

        $quoteList = '';
        foreach($customer->quotes as $quote)
        {
            $quoteList .= '<option value="' . $quote->id . '">' . $quote->title . '</option>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $quoteList
          ]
        );
    }

    public function saveCustomerJobNotes(Request $request)
    {
        $quote = Quote::find($request->quote_id);
        if(!$quote->job)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'No Job Found'
            ]
          );
        }
        $jobNote = new JobNote();
        $jobNote->user_id = Auth::user()->id;
        $jobNote->job_id = $quote->job->id;
        $jobNote->note = $request->notes;
        $jobNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Job Note Added',
            'customer_id' => $quote->lead->customer->id
          ]
        );
    }

    public function jobMultipleAuth(Request $request)
    {
        $customer = Customer::find($request->id);
        $quotes = $customer->quotes;
        $arrQuotes = [];
        foreach($quotes as $quote)
        {
            $arrQuotes[] = $quote->id;
        }
        $jobs = Job::whereIn('quote_id', $arrQuotes)->get();
        $authLists = AuthorizationList::all();

        return view('customers.multiple_auth', compact('customer', 'jobs', 'authLists'));
    }

    public function getJobAuthSign(Request $request)
    {
        $job_ids = $request->job_ids;
        $jobs = Job::whereIn('id',  $job_ids)->get();

        $data = '';
        foreach($jobs as $job)
        {
            $data .= '<tr><td>' . $job->quote->type->name . ' - ' . $job->quote->id . '</td></tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function saveCustomerJobAuthSign(Request $request)
    {
        $job_ids = $request->job_ids;
        $signature = $request->signature;

        $jobs = Job::whereIn('id',  $job_ids)->get();
        foreach($jobs as $job)
        {
            $auth = Authorization::where('job_id', $job->id)->first();
            if(!$auth)
            {
                $auth = new Authorization();
                $auth->job_id = $job->id;
            }
            $auth->signature = $signature;
            $auth->signed_on = Carbon::now()->format('Y-m-d H:i:s');
            $auth->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Signature Set.'
          ]
        );
    }

    public function getJobAuthStatus(Request $request)
    {
        $job_id = $request->job_id;

        $auth = Authorization::where('job_id', $job_id)->first();
        if(!$auth)
        {
            return Response::json(
              [
                'response' => 'success',
                'message' => 'no sign found'
              ]
            );
        }
        else
        {
            if($auth->signed_on == null)
            {
              return Response::json(
                [
                  'response' => 'success',
                  'message' => 'no sign found'
                ]
              );
            }
            else
            {
                return Response::json(
                  [
                    'response' => 'success',
                    'message' => 'sign found',
                    'signature' => $auth->signature,
                    'signed_on' => Carbon::parse($auth->signed_on)->format('Y-m-d H:i:s')
                  ]
                );
            }
        }
    }

    public function removeCustomerJobAuthSign(Request $request)
    {
        $job_ids = $request->job_ids;
        $auths = Authorization::whereIn('job_id', $job_ids)->get();
        foreach($auths as $auth)
        {
            $auth->signature = '';
            $auth->signed_on = null;
            $auth->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Siganture Deleted'
          ]
        );
    }
}
