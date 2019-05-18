<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Accessory;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationItem;
use FK3\Models\AuthorizationList;
use FK3\Models\BuildupNote;
use FK3\Models\Cabinet;
use FK3\Models\Checklist;
use FK3\Models\Lead;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Fft;
use FK3\Models\FftNote;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteAppliance;
use FK3\Models\Question;
use FK3\Models\QuestionAnswer;
use FK3\Models\Shop;
use FK3\Models\ShopCabinet;
use FK3\Models\Hardware;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\JobNote;
use FK3\Models\JobSchedule;
use FK3\Models\Po;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\Group;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;
use Mail;

class ShopController extends Controller
{
    public $auditPage = "Shop";

    /*
     * Show Shop Index.
     */
    public function index(Request $request)
    {
      $jobs = Job::join('quotes', 'jobs.quote_id', '=', 'quotes.id')
                  ->join('quote_types', 'quotes.quote_type_id', '=', 'quote_types.id')
                  ->join('leads', 'quotes.lead_id', '=', 'leads.id')
                  ->join('customers', 'leads.customer_id', '=', 'customers.id')
                  ->select(
                            'jobs.*',
                            'quotes.lead_id',
                            'quote_types.name as quoteTypeName',
                            'customers.name as customerName'
                          )
                  ->get();

      $jobOpt = '';
      foreach($jobs as $job)
      {
          $jobOpt .= '<option value="' . $job->id . '">' . htmlspecialchars($job->customerName, ENT_QUOTES) . ' (' . $job->quoteTypeName . ' - ' . $job->id . ')</option>';
      }

      return view('shop.index', compact('jobOpt'));
    }

    public function saveShop(Request $request)
    {
        $job_id = $request->job_id;

        if($job_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select a job.'
            ]
          );
        }

        $shop = new Shop();
        $shop->job_id = $job_id;
        $shop->user_id = Auth::user()->id;
        $shop->active = 1;
        $shop->save();

        $job = Job::find($job_id);
        $cabinets = Quote::join('quote_cabinets', 'quotes.id', '=', 'quote_cabinets.quote_id')
                          ->select(
                                    'quote_cabinets.*',
                                    'quotes.id'
                                  )
                          ->where('quotes.id', $job->quote_id)
                          ->get();

        foreach ($cabinets AS $cabinet)
        {
            $cab = new ShopCabinet();
            $cab->quote_cabinet_id = $cabinet->id;
            $cab->shop_id = $shop->id;
            $cab->save();
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Shop Added.',
            'url' => route('shop')
          ]
        );
    }

    /**
     * Toggle completions.
     * @param $id
     * @param $type
     * @return array
     */
    public function setType($id, $type)
    {
        $item = ShopCabinet::find($id);
        $item->{$type} = \Carbon\Carbon::now();
        $item->save();
        $complete = true;

        $shop = Shop::find($item->shop_id);
        $cabinets = ShopCabinet::where('shop_id', $shop->id)->get();

        foreach ($cabinets as $cab)
        {
            if (!$cab->completed)
            {
                $complete = false;
            }
        }
        if ($complete)
        {
            $shop->active = '0';
            $shop->save();
        }

        return redirect()->back()->with('success', 'Shop Cabinet ' . $type . ' set.');
    }
}
