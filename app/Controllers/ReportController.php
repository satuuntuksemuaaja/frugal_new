<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Contact;
use FK3\Models\Fft;
use FK3\Models\Lead;
use FK3\Models\LeadUpdate;
use FK3\Models\Quote;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteType;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\Cabinet;
use FK3\Models\Job;
use FK3\Models\JobSchedule;
use FK3\Models\Status;
use FK3\Models\TaskNote;
use FK3\Models\Customer;
use FK3\Models\LeadSource;
use FK3\Models\Location;
use FK3\Models\Promotion;
use FK3\Models\Vendor;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use FK3\vl\quotes\QuoteGeneratorNew;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class ReportController extends Controller
{
    public $auditPage = "Report";

    public $masterCount = 0;
    public $masterSold = 0;
    public $masterProvided = 0;
    public $limitData = 3;

    public function GenerateTableRows($rows, $array_long)
    {
        $finalRows = '';
        for($x = 0; $x < count($rows); $x++)
        {
            $finalRows .= '<tr>';
            for($z = 0; $z < count($rows[$x]); $z++)
            {
                if(!is_array($rows[$x][$z]))
                {
                  $finalRows .= '<td>';
                  $finalRows .= $rows[$x][$z];
                  $finalRows .= '</td>';
                }
                else
                {
                  for($q = 0; $q <= $array_long; $q++)
                  {
                    $finalRows .= '<td>';
                    $finalRows .= array_key_exists($q, $rows[$x][$z]) ? $rows[$x][$z][$q] : 0;
                    $finalRows .= '</td>';
                  }
                }
            }
            $finalRows .= '</tr>';
        }

        return $finalRows;
    }

    /*
     * Show Report Index
     */
    public function index(Request $request)
    {
        $quoteTypes = QuoteType::all();
        return view('reports.index', compact('quoteTypes', 'request'));
    }

    public function getLeadsReport(Request $request)
    {
        $type = $request->type ?: "All Job Types";

        $sources = LeadSource::get(); //all();
        $start = '';
        if($request->has('start')) $start = $request->start;
        $end = '';
        if($request->has('end')) $end = $request->end;

        foreach ($sources AS $source)
        {
            $provided = 0;
            $sold = 0;
            $count = 0;
            $leads = Lead::where('source_id', $source->id)->get();
            foreach ($leads AS $lead)
            {
                if ($start != '')
                {
                    if ($lead->created_at < Carbon::parse($start))
                    {
                        continue;
                    }
                }
                if ($end != '')
                {
                    if ($lead->created_at > Carbon::parse($end))
                    {
                        continue;
                    }
                }
                $count++;
                $this->masterCount++;
                if ($lead->status_id == 10 || $lead->provided)
                {
                    $provided++;
                    $this->masterProvided++;
                }
                $quotes = Quote::where('lead_id', $lead->id)->get();
                foreach ($quotes as $quote)
                {
                    if ($type != 'All Job Types')
                    {
                        $quoteType = QuoteType::find($quote->quote_type_id);
                        if($quoteType)
                        {
                          if ($quoteType->name == $type && $quote->accepted)
                          {
                              $sold++;
                              $this->masterSold++;
                          }
                        }
                    }
                    else
                    {
                        if ($quote->accepted)
                        {
                            $sold++;
                            $this->masterSold++;
                        }
                    }

                }


            } // fe lead
            if ($count > 0)
            {
                $providedPerc = '<a href="#" onclick="ShowModalSource(' .  $source->id . ', \'provided\', \'' . $start . '\', \'' . $end . '\')">' . $provided . '</a> <span class="pull-right">(' . round($provided / $count * 100) . '%)</span>';
                $soldPerc = '<a href="#" onclick="ShowModalSource(' .  $source->id . ', \'sold\', \'' . $start . '\', \'' . $end . '\')">' . $sold . '</a><span class="pull-right"> (' . round($sold / $count * 100) . '%)</span>';
            }
            else
            {
                $providedPerc = 0;
                $soldPerc = 0;
            }
            $rows[] = [
                $source->name,
                '<a href="#" onclick="ShowModalSource(' .  $source->id . ', \'count\', \'' . $start . '\', \'' . $end . '\')">' . $count . '</a>',
                $soldPerc,
                $providedPerc,
                @number_format($sold / $provided * 100) . "%"
            ];
        } // fe source
        $rows[] = [
                null,
                number_format($this->masterCount),
                number_Format($this->masterSold),
                number_format($this->masterProvided),
            @number_format($this->masterSold / $this->masterProvided * 100) . "%"
        ];

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function getUsersReport(Request $request)
    {
        $type = $request->type ?: "All Job Types";

        $start = '';
        if($request->has('start')) $start = $request->start;
        $end = '';
        if($request->has('end')) $end = $request->end;

        foreach (User::whereActive(true)->get() AS $user)
        {
            $provided = 0;
            $sold = 0;
            $count = 0;
            foreach ($user->quotes AS $quote)
            {
                if ($start != '')
                {
                    if ($quote->created_at < Carbon::parse($start))
                    {
                        continue;
                    }
                }
                if ($end != '')
                {
                    if ($quote->created_at > Carbon::parse($end))
                    {
                        continue;
                    }

                }
                $count++;
                $this->masterCount++;
                $lead = Lead::find($quote->lead_id);
                if ($lead->status_id == 10 || $lead->provided)
                {
                    $provided++;
                    $this->masterProvided++;
                }

                if ($type != 'All Job Types')
                {
                    $quoteType = QuoteType::find($quote->quote_type_id);
                    if($quoteType)
                    {
                        if ($quoteType->name == $type && $quote->accepted)
                        {
                            $sold++;
                            $this->masterSold++;
                        }
                    }
                }
                else
                {
                    if ($quote->accepted)
                    {
                        $sold++;
                        $this->masterSold++;
                    }
                }

            } // fe lead
            if ($count > 0)
            {
                $providedPerc = $provided . " <span class='pull-right'>(" . round($provided / $count * 100) . "%)</span>";
                $soldPerc = $sold . "<span class='pull-right'> (" . round($sold / $count * 100) . "%)</span>";
            }
            else
            {
                $providedPerc = 0;
                $soldPerc = 0;
            }
            $rows[] = [
                $user->name,
                '<a href="#" onclick="ShowModalUser(' .  $user->id . ', \'count\', \'' . $start . '\', \'' . $end . '\')">' . $count . '</a></span>',
                '<a href="#" onclick="ShowModalUser(' .  $user->id . ', \'sold\', \'' . $start . '\', \'' . $end . '\')">' . $sold . '</a>  <span class="pull-right">(' . @round($sold / $count * 100) . '%)</span>',
                '<a href="#" onclick="ShowModalUser(' .  $user->id . ', \'provided\', \'' . $start . '\', \'' . $end . '\')">' . $provided . '</a>  <span class="pull-right">(' . @round($provided / $count * 100) . '%)</span>',

            ];
        } // fe source
        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 4)
          ]
        );
    }

    public function getSourceType(Request $request)
    {
        $source_id = $request->source_id;
        $type = $request->type;
        $start = $request->start;
        $end = $request->end;

        $rows = [];
        $source = LeadSource::find($source_id);
        $leads = Lead::where('source_id', $source->id);
        if($start != '' && $end != '')
        {
            $leads = $leads->where('created_at', '>=', Carbon::parse($start))->where('created_at', '<=', Carbon::parse($end));
        }
        $leads = $leads->get();
        foreach ($leads AS $lead)
        {
            $customer = Customer::find($lead->customer_id);
            switch ($type)
            {
                case 'count' :
                    $rows[] = [
                      "<a href='" . route('view_profile', ['id' => $lead->customer_id]) . "'>" . @$customer->name . "</a>",
                      $lead->created_at->format("m/d/y")
                    ];
                    break;
                case 'sold' :
                    $quotes = Quote::where('lead_id', $lead->id)->get();
                    foreach ($quotes as $quote)
                    {
                        if ($quote->accepted)
                            $rows[] = [
                                "<a href='" . route('view_profile', ['id' => $lead->customer_id]) . "'>" . @$customer->name . "</a>",
                                $lead->created_at->format("m/d/y")
                            ];
                    }

                    break;
                case 'provided' :
                    if ($lead->provided)
                        $rows[] = [
                            "<a href='" . route('view_profile', ['id' => $lead->customer_id]) . "'>" . @$customer->name . "</a>",
                            $lead->created_at->format("m/d/y")
                        ];
                    break;
            }
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 2),
            'source_name' => $source->name
          ]
        );

    }

    public function getUserType(Request $request)
    {
        $user_id = $request->user_id;
        $type = $request->type;
        $start = $request->start;
        $end = $request->end;

        $rows = [];
        $sold = 0;
        $provided = 0;
        $providedBlock = [];
        $soldBlock = [];
        $rtype = $request->type ?: "All Job Types";

        $provided = 0;
        $sold = 0;
        $count = 0;

        $user = User::find($user_id);
        foreach ($user->quotes AS $quote)
        {
            if ($request->start != '')
            {
                if ($quote->created_at < Carbon::parse($request->start))
                {
                    continue;
                }
            }
            if ($request->end != '')
            {
                if ($quote->created_at > Carbon::parse($request->end))
                {
                    continue;
                }

            }
            $lead = Lead::find($quote->lead_id);
            $customer = Customer::find($lead->customer_id);
            $quoteType = QuoteType::find($quote->quote_type_id);
            if ($lead->status_id == 10 || $lead->provided)
            {
                $provided++;
                $providedBlock[] = [
                    (($customer) ? "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>" : null),
                    "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>#{$quote->id}</a>",
                    @$quoteType->name
                ];
            }
            $count++;
            if ($quote->accepted)
            {
                if ($rtype != 'All Job Types')
                {
                    if ($quote->type == $rtype && $quote->accepted)
                    {
                        $sold++;
                        $soldBlock[] = [
                            (($customer) ? "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>" : null),
                            "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>#{$quote->id}</a>",
                            @$quoteType->name
                        ];
                    }
                }
                else
                {
                    if ($quote->accepted)
                    {
                        $sold++;
                        $soldBlock[] = [
                            (($customer) ? "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>" : null),
                            "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>#{$quote->id}</a>",
                            @$quoteType->name
                        ];
                    }
                }


            }
        } // fe lead


        if ($type == 'sold')
            $rows = $soldBlock;
        else $rows = $providedBlock;
        $total = ($type == 'sold') ? $sold : $provided;

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 3),
            'total' => $total,
            'user_name' => $user->name
          ]
        );
    }

    public function exportLeads(Request $request)
    {
        $leads = Lead::get();

        $data = "Name,Address,City,State,Zip,Home Phone,Mobile Phone,E-mail,Created On,Status,Sold On,Lead Source,Price\n";
        foreach ($leads AS $lead)
        {
            $customer = Customer::find($lead->customer_id);
            if (!$customer) continue;

            $contact = Contact::where('customer_id', $customer->id)->first();
            $created = $lead->created_at->format("m/d/y");

            $status = Status::find($lead->status_id);
            $status = $status ? $status->name : "Not Set";
            $sold = "N/A";

            $quotesAccepted = Quote::where('lead_id', $lead->id)->where('accepted', true)->first();
            if ($quotesAccepted)
            {
                $job = Job::where('quote_id', $quotesAccepted->id)->first();
                if ($job)
                {
                    $sold = Carbon::parse($job->contract_date)->format("m/d/y");
                }
            }
            $leadSource = LeadSource::find($lead->source_id);
            if ($leadSource)
                $source = $leadSource->name;
            else
                $source = "Unknown Source";

            $countQuoteFinal = Quote::where('lead_id', $lead->id)->where('final', true)->count();
            $quoteFinalFirst = Quote::where('lead_id', $lead->id)->where('final', true)->first();
            $price = $countQuoteFinal > 0 ? $quoteFinalFirst->finance_total : 0;
            $contact->name = str_replace(",", null, $contact->name);
            $customer->address = str_replace(",", null, $customer->address);
            $customer->state= str_replace(",", null, $customer->state);

            $data .= "{$contact->name},{$customer->address},{$customer->city},{$customer->state},{$customer->zip},{$contact->home},{$contact->mobile},{$contact->email},{$created},{$status},{$sold},{$source},{$price}\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    /**
     * Create Zip Code Exports based on #173
     */
    public function exportZips(Request $request)
    {
        /*
         * Report 1: Zip codes -list zip codes with total leads and then total sales for each one (I have to show see Leads and Sold total separate for each zip)
         * Report 2: Zip codes- need to be able to determine the top 5 or 6 over all for leads and sold
         * Report 3: Zip Codes-Sold need the total $ spent in each zip code.
         */
        // Zip - Leads - Sold - Sold Amount
        $data = "Zip,Leads,Number Sold,Sold Amount Total,Quote IDs\n";
        $zips = Customer::groupBy('zip')->get()->lists('zip');
        foreach ($zips AS $zip)
        {
            $data .= "$zip,";
            $zipCount = 0;
            $soldCount = 0;
            $soldAmount = 0;
            $quoteID = null;
            foreach (Customer::whereZip($zip)->get() as $customer)
            {
                $leads = Lead::where('customer_id', $customer->id)->get();
                $zipCount += count($leads);
                foreach ($leads AS $lead)
                {
                    $quotesAcceptedFirst = Quote::where('lead_id', $lead->id)->where('accepted', true)->first();
                    if ($quotesAcceptedFirst)
                    {
                        $job = Job::where('quote_id', $quotesAcceptedFirst->id)->first();
                        if ($job)
                        {
                            $quoteID .= $quotesAcceptedFirst->id. "/";
                            $soldCount++;
                            $soldAmount += $quotesAcceptedFirst->finance_total;
                        }
                    }

                }
            }
            $data .= "$zipCount,$soldCount,$soldAmount,$quoteID\n";

        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="zips.csv"',
        ];

        return Response::make($data, 200, $headers);
    }

    public function frugal(Request $request)
    {
        $type = $request->type ?: "All Job Types";
        $start = $request->start;
        $end = $request->end;

        $grand = 0;
        $allttl = 0;
        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;

        }
        foreach (User::whereGroupId(20)->orWhere('id', '=', 5)->get() AS $user)
        {
            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;
            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }
            foreach ($user->quotes AS $quote)
            {

                if ($quote->accepted)
                {
                    $job = Job::where('quote_id', $quote->id)->first();
                    if (!$quote || !$job) continue;
                    if ($start != '' && $job->created_at < Carbon::parse($start)) continue;
                    if ($start != '' && $job->created_at > Carbon::parse($end)) continue;
                    $details = QuoteGeneratorNew::getQuoteObject($quote);
                    $financeTotal = $details->forFrugal;
                    $financeTotal += 150.00;
                    $financeTotal += $details->cabinetBuildup;
                    $financeTotal -= $details->discounts;
                    if ($type != 'All Job Types')
                    {
                        if ($quote->type != $type) continue;
                    }

                    $month = $job->created_at->format('n');

                    if (!isset($months[$month])) $months[$month] = 0;
                    if (!isset($count[$month])) $count[$month] = 0;
                    if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                    if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                    if (!isset($quotesList[$month])) $quotesList[$month] = null;
                    $months[$month] = $months[$month] + $financeTotal;
                    $count[$month] = $count[$month] + 1;
                    $grandTTL[$month] = $grandTTL[$month] + 1;
                    $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
                    $quotesList[$month] .= $quote->id . ", ";
                    $userttl++;
                    $allttl++;
                    $ttl += $financeTotal;
                    $grand += $financeTotal;
                } // if accepted
            } // fe quote
            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                "$" . $months[$i] = number_format($months[$i], 2);
            }
            $rows[] = [
                $user->name,
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $months[1] . '</a>' . ' (' . @$count[1] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $months[2] . '</a>' . ' (' . @$count[2] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $months[3] . '</a>' . ' (' . @$count[3] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $months[4] . '</a>' . ' (' . @$count[4] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $months[5] . '</a>' . ' (' . @$count[5] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $months[6] . '</a>' . ' (' . @$count[6] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $months[7] . '</a>' . ' (' . @$count[7] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $months[8] . '</a>' . ' (' . @$count[8] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $months[9] . '</a>' . ' (' . @$count[9] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $months[10] . '</a>' . ' (' . @$count[10] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $months[11] . '</a>' . ' (' . @$count[11] . ')',
                '<a href="#" onclick="ShowModalDesignerFrugalReport(\'' . $user->name . '\',' . $user->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $months[12] . '</a>' . ' (' . @$count[12] . ')',
                number_format($ttl, 2) . ' (' . $userttl . ')'
            ];
        }
        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 14)
          ]
        );
    }

    public function cabinets(Request $request)
    {
        $quoteTypes = QuoteType::all();
        $vendors = Vendor::orderBy('name', 'asc')->get();
        return view('reports.cabinet', compact('quoteTypes', 'vendors'));
    }

    public function cabinetsReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $vendor_id = $request->vendor_id;
        $rows = [];

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }
        if($vendor_id) $cabinets = Cabinet::where('vendor_id', $vendor_id)->get();
        else $cabinets = Cabinet::all();
        foreach ($cabinets as $cabinet)
        {
            $quotes = Quote::join('quote_cabinets', 'quote_cabinets.quote_id', '=', 'quotes.id')
                              ->where('quote_cabinets.cabinet_id', $cabinet->id);
            if($start != '') $quotes = $quotes->where('quotes.created_at', '>=', Carbon::parse($start));
            if($end != '') $quotes = $quotes->where('quotes.created_at', '<=', Carbon::parse($end));
            $quotes = $quotes->select(
                                      'quote_cabinets.*',
                                      'quotes.created_at as quote_created_at',
                                      'quotes.updated_at as quote_updated_at',
                                      'quotes.accepted',
                                      'quotes.id as quotes_id'
                                    );
            $quotes = $quotes->groupBy('quotes_id')->get();

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;
            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }

            foreach($quotes as $quote)
            {
                $month = $quote->created_at->format('n');

                if (!isset($months[$month])) $months[$month] = 0;
                if (!isset($count[$month])) $count[$month] = 0;
                if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                if (!isset($quotesList[$month])) $quotesList[$month] = null;

                $months[$month] = $months[$month] + 1;
                if($quote->accepted == '1')
                {
                   $count[$month] = $count[$month] + 1;
                   $grandTTL[$month] = $grandTTL[$month] + 1;
                   $userttl++;
                   $allttl++;
                }
                $grandMonth[$month] = $grandMonth[$month] + 1;
                $quotesList[$month] .= $cabinet->id . ", ";
                $ttl += 1;
                $grand += 1;
            }

            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                "$" . $months[$i] = $months[$i];
            }
            $rows[] = [
                $cabinet->name,
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',1,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[1] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',1,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[1]) ? $count[1] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',2,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[2] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',2,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[2]) ? $count[2] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',3,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[3] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',3,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[3]) ? $count[3] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',4,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[4] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',4,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[4]) ? $count[4] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',5,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[5] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',5,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[5]) ? $count[5] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',6,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[6] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',6,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[6]) ? $count[6] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',7,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[7] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',7,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[7]) ? $count[7] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',8,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[8] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',8,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[8]) ? $count[8] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',9,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[9] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',9,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[9]) ? $count[9] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',10,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[10] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',10,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[10]) ? $count[10] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',11,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[11] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',11,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[11]) ? $count[11] : 0) . '</a>',
                '<a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',12,\'' . $start . '\',\'' . $end . '\', \'Quotes\')">' . $months[12] . '</a> - <a href="#" onclick="ShowModalCabinetReport(\'' . $cabinet->name . '\',' . $cabinet->id . ',12,\'' . $start . '\',\'' . $end . '\', \'Sold\')">' . (isset($count[12]) ? $count[12] : 0) . '</a>',
                $ttl . '-' . $userttl
            ];
        }
        $rows[] = [
            null,
            $grandMonth[1] . " - " . @$grandTTL[1],
            $grandMonth[2] . " - " . @$grandTTL[2],
            $grandMonth[3] . " - " . @$grandTTL[3],
            $grandMonth[4] . " - " . @$grandTTL[4],
            $grandMonth[5] . " - " . @$grandTTL[5],
            $grandMonth[6] . " - " . @$grandTTL[6],
            $grandMonth[7] . " - " . @$grandTTL[7],
            $grandMonth[8] . " - " . @$grandTTL[8],
            $grandMonth[9] . " - " . @$grandTTL[9],
            $grandMonth[10] . " - " . @$grandTTL[10],
            $grandMonth[11] . " - " . @$grandTTL[11],
            $grandMonth[12] . " - " . @$grandTTL[12],
            $grand . " - " . $allttl

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function cabinetsDetailReport(Request $request)
    {
        $cabinet_id = $request->cabinet_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        $quotes = Quote::join('quote_cabinets', 'quote_cabinets.quote_id', '=', 'quotes.id')
                          ->where('quote_cabinets.cabinet_id', $cabinet_id);
        if($start != '') $quotes = $quotes->where('quotes.created_at', '>=', Carbon::parse($start));
        if($end != '') $quotes = $quotes->where('quotes.created_at', '<=', Carbon::parse($end));
        if($type == 'Sold') $quotes = $quotes->where('quotes.accepted', '1');
        $quotes = $quotes->select(
                                  'quote_cabinets.*',
                                  'quotes.created_at as quote_created_at',
                                  'quotes.updated_at as quote_updated_at',
                                  'quotes.accepted',
                                  'quotes.id as quotes_id'
                                );
        $quotes = $quotes->groupBy('quotes_id')->get();

        foreach($quotes as $quote)
        {
            $quoteObj = Quote::find($quote->quote_id);
            $customer = Customer::find($quoteObj->lead->customer_id);

            $monthQuote = $quote->created_at->format('n');
            if($monthQuote == $month)
            {
                $rows[] = [
                    "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                    "<a href='" . route('quote_view', ['id' => $quoteObj->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                    $quote->created_at->format("m/d/y"),
                    "$". number_format($quoteObj->finance_total,2)
                ];
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function designers(Request $request)
    {
        $quoteTypes = QuoteType::all();
        return view('reports.designers', compact('quoteTypes', 'request'));
    }

    public function designersReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;

        }
        foreach (User::whereGroupId(20)->orWhere('id', '=', 5)->get() AS $user)
        {
            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;
            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }
            if ($user->id == 34) continue;
            if ($user->id == 12 && Carbon::parse($start) >= Carbon::parse('2017-01-01') )
                continue;
            foreach ($user->quotes AS $quote)
            {
                if ($quote->accepted)
                {
                    $job = Job::where('quote_id', $quote->id);
                    if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($start));
                    if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($end));
                    $job = $job->first();

                    if (!$quote || !$job) continue;
                    if ($type != 'All Job Types')
                    {
                        if ($quote->type->name != $type) continue;
                    }
                    if ($quote->finance_total == 0)
                    {
                        $details = QuoteGeneratorNew::getQuoteObject($quote);
                        $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                        $quote->finance_total = $financeTotal;
                        $quote->save();
                    }
                    else
                    {
                        $financeTotal = $quote->finance_total;
                    }
                    $month = $job->created_at->format('n');

                    if (!isset($months[$month])) $months[$month] = 0;
                    if (!isset($count[$month])) $count[$month] = 0;
                    if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                    if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                    if (!isset($quotesList[$month])) $quotesList[$month] = null;
                    $months[$month] = $months[$month] + $financeTotal;
                    $count[$month] = $count[$month] + 1;
                    $grandTTL[$month] = $grandTTL[$month] + 1;
                    $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
                    $quotesList[$month] .= $quote->id . ", ";
                    $userttl++;
                    $allttl++;
                    $ttl += $financeTotal;
                    $grand += $financeTotal;
                } // if accepted
            } // fe quote
            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                "$" . $months[$i] = number_format($months[$i], 2);
            }
            $rows[] = [
                $user->name,
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $months[1] . '</a>' . ' (' . @$count[1] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $months[2] . '</a>' . ' (' . @$count[2] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $months[3] . '</a>' . ' (' . @$count[3] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $months[4] . '</a>' . ' (' . @$count[4] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $months[5] . '</a>' . ' (' . @$count[5] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $months[6] . '</a>' . ' (' . @$count[6] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $months[7] . '</a>' . ' (' . @$count[7] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $months[8] . '</a>' . ' (' . @$count[8] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $months[9] . '</a>' . ' (' . @$count[9] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $months[10] . '</a>' . ' (' . @$count[10] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $months[11] . '</a>' . ' (' . @$count[11] . ')',
                '<a href="#" onclick="ShowModalDesignerReport(\'' . $user->name . '\',' . $user->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $months[12] . '</a>' . ' (' . @$count[12] . ')',
                number_format($ttl, 2) . ' (' . $userttl . ')'
            ];
        }
        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function designersDetailReport(Request $request)
    {
        $user_id = $request->user_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $user = User::find($user_id);
        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];
        foreach ($user->quotes AS $quote)
        {
            $job = Job::where('quote_id', $quote->id);
            if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($startDate));
            if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($endDate));
            $job = $job->first();

            if (!$job) continue;

            $obj = QuoteGeneratorNew::getQuoteObject($quote);

            $lead = Lead::find($quote->lead_id);
            $customer = Customer::find($lead->customer_id);

            $profitAmount = null;
            if($profit) $profitAmount = number_format(($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2);

            $profitDetails = '<table>
                                <thead>
                                  <th><b>Items</b></th>
                                  <th><b>Amount</b></th>
                                </thead>
                                <tr>
                                  <td>For Frugal:</td>
                                  <td>' . $obj->forFrugal . '</td>
                                </tr>
                                <tr>
                                  <td>Additional:</td>
                                  <td>150.00</td>
                                </tr>
                                <tr>
                                  <td>Cabinet Buildup:</td>
                                  <td>' . $obj->cabinetBuildup . '</td>
                                </tr>
                                <tr>
                                  <td>Discount:</td>
                                  <td>-' . $obj->discounts . '</td>
                                </tr>
                                <tr>
                                  <td><b>Total:</b></td>
                                  <td><b>' . $profitAmount . '<b></td>
                                </tr>
                              </table>';

            $profitLink = '<a href="#" data-toggle="popover" title="Profit Details" data-placement="left" data-html="true" data-trigger="hover" data-content="' . $profitDetails . '" class="popovered">' . $profitAmount . '</a>';

            $rows[] = [
                "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                $job->created_at->format("m/d/y"),
                "$". number_format($quote->finance_total,2),
                $profitLink
            ];
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function locations(Request $request)
    {
        $quoteTypes = QuoteType::all();
        return view('reports.locations', compact('quoteTypes', 'request'));
    }

    public function locationsReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $locations = Location::where('deleted_at', null)->get();
        foreach($locations as $location)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;
            foreach (Lead::where('showroom_location_id', '=', $location->id)->get() AS $lead)
            {
                $months = [];
                $count = [];
                $ttl = 0;
                $userttl = 0;
                $quotesList = [];
                for ($i = 1; $i <= 12; $i++)
                {
                    $quotesList[$i] = null;
                }

                foreach ($lead->quotes AS $quote)
                {
                    if ($quote->accepted)
                    {
                        $job = Job::where('quote_id', $quote->id);
                        if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($start));
                        if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($end));
                        $job = $job->first();

                        if (!$quote || !$job) continue;
                        if ($type != 'All Job Types')
                        {
                            if ($quote->type->name != $type) continue;
                        }
                        if ($quote->finance_total == 0)
                        {
                            $details = QuoteGeneratorNew::getQuoteObject($quote);
                            $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                            $quote->finance_total = $financeTotal;
                            $quote->save();
                        }
                        else
                        {
                            $financeTotal = $quote->finance_total;
                        }
                        $month = $job->created_at->format('n');

                        if (!isset($months[$month])) $months[$month] = 0;
                        if (!isset($count[$month])) $count[$month] = 0;
                        if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                        if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                        if (!isset($quotesList[$month])) $quotesList[$month] = null;
                        $months[$month] = $months[$month] + $financeTotal;
                        $count[$month] = $count[$month] + 1;
                        $grandTTL[$month] = $grandTTL[$month] + 1;
                        $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
                        $quotesList[$month] .= $quote->id . ", ";
                        $userttl++;
                        $allttl++;
                        $ttl += $financeTotal;
                        $grand += $financeTotal;
                    } // if accepted
                } // fe quote
                for ($i = 1; $i <= 12; $i++)
                {
                    if (!isset($months[$i])) $months[$i] = 0;
                    //else $months[$i] = number_format($months[$i], 2);

                    if (!isset($count[$i])) $count[$i] = 0;
                    //else $count[$i] = number_format($count[$i], 2);
                }

                $totalMonth1 += $months[1];
                $totalMonth2 += $months[2];
                $totalMonth3 += $months[3];
                $totalMonth4 += $months[4];
                $totalMonth5 += $months[5];
                $totalMonth6 += $months[6];
                $totalMonth7 += $months[7];
                $totalMonth8 += $months[8];
                $totalMonth9 += $months[9];
                $totalMonth10 += $months[10];
                $totalMonth11 += $months[11];
                $totalMonth12 += $months[12];
                $totalCountMonth1 += $count[1];
                $totalCountMonth2 += $count[2];
                $totalCountMonth3 += $count[3];
                $totalCountMonth4 += $count[4];
                $totalCountMonth5 += $count[5];
                $totalCountMonth6 += $count[6];
                $totalCountMonth7 += $count[7];
                $totalCountMonth8 += $count[8];
                $totalCountMonth9 += $count[9];
                $totalCountMonth10 += $count[10];
                $totalCountMonth11 += $count[11];
                $totalCountMonth12 += $count[12];
                $totalCountAllMonth += $ttl; //number_format($ttl, 2);
                $totalCountAllUser += $userttl;
            }

            $rows[] = [
                $location->name,
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalLocationReport(\'' . $location->name . '\',' . $location->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
        }
        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function locationsDetailReport(Request $request)
    {
        $location_id = $request->location_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $location = Location::find($location_id);
        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        foreach (Lead::where('showroom_location_id', '=', $location->id)->get() AS $lead)
        {
            foreach ($lead->quotes AS $quote)
            {
                if($lead->showroom_location_id == $location_id)
                {
                    $job = Job::where('quote_id', $quote->id);
                    if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($startDate));
                    if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($endDate));
                    $job = $job->first();

                    if (!$job) continue;

                    $obj = QuoteGeneratorNew::getQuoteObject($quote);

                    $lead = Lead::find($quote->lead_id);
                    $customer = Customer::find($lead->customer_id);

                    $rows[] = [
                        "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                        "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                        $job->created_at->format("m/d/y"),
                        "$". number_format($quote->finance_total,2),
                        $profit ? number_format(
                            ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
                    ];
                }
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function promotions(Request $request)
    {
        $quoteTypes = QuoteType::all();
        return view('reports.promotions', compact('quoteTypes', 'request'));
    }

    public function promotionsReport(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $promotions = Promotion::where('deleted_at', null)->get();
        foreach($promotions as $promotion)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;

            $quotes = Quote::where('promotion_id', '=', $promotion->id)
                              ->where('accepted', '1')
                              ->where('deleted_at', null)
                              ->get();

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;
            foreach ($quotes AS $quote)
            {
                $quotesList = [];
                for ($i = 1; $i <= 12; $i++)
                {
                    $quotesList[$i] = null;
                }

                $job = Job::where('quote_id', $quote->id);
                if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($start));
                if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($end));
                $job = $job->first();

                if (!$quote || !$job) continue;
                if ($type != 'All Job Types')
                {
                    if ($quote->type->name != $type) continue;
                }
                if ($quote->finance_total == 0)
                {
                    $details = QuoteGeneratorNew::getQuoteObject($quote);
                    $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                    $quote->finance_total = $financeTotal;
                    $quote->save();
                }
                else
                {
                    $financeTotal = $quote->finance_total;
                }
                $month = $job->created_at->format('n');

                if (!isset($months[$month])) $months[$month] = 0;
                if (!isset($count[$month])) $count[$month] = 0;
                if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                if (!isset($quotesList[$month])) $quotesList[$month] = null;
                $months[$month] = $months[$month] + $financeTotal;
                $count[$month] = $count[$month] + 1;
                $grandTTL[$month] = $grandTTL[$month] + 1;
                $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
                $quotesList[$month] .= $quote->id . ", ";
                $userttl++;
                $allttl++;
                $ttl += $financeTotal;
                $grand += $financeTotal;
            } // fe quote
            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                //else $months[$i] = number_format($months[$i], 2);

                if (!isset($count[$i])) $count[$i] = 0;
                //else $count[$i] = number_format($count[$i], 2);
            }

            $totalMonth1 += $months[1];
            $totalMonth2 += $months[2];
            $totalMonth3 += $months[3];
            $totalMonth4 += $months[4];
            $totalMonth5 += $months[5];
            $totalMonth6 += $months[6];
            $totalMonth7 += $months[7];
            $totalMonth8 += $months[8];
            $totalMonth9 += $months[9];
            $totalMonth10 += $months[10];
            $totalMonth11 += $months[11];
            $totalMonth12 += $months[12];
            $totalCountMonth1 += $count[1];
            $totalCountMonth2 += $count[2];
            $totalCountMonth3 += $count[3];
            $totalCountMonth4 += $count[4];
            $totalCountMonth5 += $count[5];
            $totalCountMonth6 += $count[6];
            $totalCountMonth7 += $count[7];
            $totalCountMonth8 += $count[8];
            $totalCountMonth9 += $count[9];
            $totalCountMonth10 += $count[10];
            $totalCountMonth11 += $count[11];
            $totalCountMonth12 += $count[12];
            $totalCountAllMonth += $ttl; //number_format($ttl, 2);
            $totalCountAllUser += $userttl;

            $rows[] = [
                $promotion->name,
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalPromotionReport(\'' . $promotion->name . '\',' . $promotion->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
        }

        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function promotionsDetailReport(Request $request)
    {
        $promotion_id = $request->promotion_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $promotion = Promotion::find($promotion_id);
        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        $quotes = Quote::where('promotion_id', '=', $promotion->id)
                          ->where('accepted', '1')
                          ->where('deleted_at', null)
                          ->get();

        foreach ($quotes AS $quote)
        {
            if($quote->promotion_id == $promotion_id)
            {
                $job = Job::where('quote_id', $quote->id);
                if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($startDate));
                if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($endDate));
                $job = $job->first();

                if (!$job) continue;

                $obj = QuoteGeneratorNew::getQuoteObject($quote);

                $job = Job::where('quote_id', $quote->id)->first();
                $customer = $quote->lead->customer;

                $rows[] = [
                    ($customer) ? "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>" . $customer->name . "</a>" : '--no customer--',
                    "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                    $job->created_at->format("m/d/y"),
                    "$". number_format($quote->finance_total,2),
                    $profit ? number_format(
                        ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
                ];
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function finishedJob(Request $request)
    {
        return view('reports.finished_job', compact('request'));
    }


    public $SOLD = 14;
    public function finishedJobReport(Request $request)
    {
        $type = $request->type;

        if($type == 'Lead to Close Time') return $this->LeadToCloseTime($request);
        if($type == 'Cabinet Install Date') return $this->CabinetInstallDate($request);
        if($type == 'Final Payment Date') return $this->FinalPaymentDate($request);
        if($type == 'Closeout Date') return $this->CloseoutDate($request);
    }

    public function LeadToCloseTime(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $leads = LeadUpdate::whereStatus($this->SOLD)
                  ->where('created_at', '>=', Carbon::parse($start))
                  ->where('created_at', '<=', Carbon::parse($end))
                  ->get();

        foreach ($leads AS $lead)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;

            $lead = $lead->lead;
            $quotes = $lead->quotes;
            foreach ($quotes AS $quote)
            {
                $quotesList = [];
                for ($i = 1; $i <= 12; $i++)
                {
                    $quotesList[$i] = null;
                }

                $job = Job::where('quote_id', $quote->id)->first();

                if (!$job) continue;

                if ($quote->finance_total == 0)
                {
                    $details = QuoteGeneratorNew::getQuoteObject($quote);
                    $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                    $quote->finance_total = $financeTotal;
                    $quote->save();
                }
                else
                {
                    $financeTotal = $quote->finance_total;
                }
                $month = $job->created_at->format('n');

                if (!isset($months[$month])) $months[$month] = 0;
                if (!isset($count[$month])) $count[$month] = 0;
                if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
                if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
                if (!isset($quotesList[$month])) $quotesList[$month] = null;
                $months[$month] = $months[$month] + $financeTotal;
                $count[$month] = $count[$month] + 1;
                $grandTTL[$month] = $grandTTL[$month] + 1;
                $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
                $quotesList[$month] .= $quote->id . ", ";
                $userttl++;
                $allttl++;
                $ttl += $financeTotal;
                $grand += $financeTotal;
            } // fe quote

            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                //else $months[$i] = number_format($months[$i], 2);

                if (!isset($count[$i])) $count[$i] = 0;
                //else $count[$i] = number_format($count[$i], 2);
            }

            $totalMonth1 += $months[1];
            $totalMonth2 += $months[2];
            $totalMonth3 += $months[3];
            $totalMonth4 += $months[4];
            $totalMonth5 += $months[5];
            $totalMonth6 += $months[6];
            $totalMonth7 += $months[7];
            $totalMonth8 += $months[8];
            $totalMonth9 += $months[9];
            $totalMonth10 += $months[10];
            $totalMonth11 += $months[11];
            $totalMonth12 += $months[12];
            $totalCountMonth1 += $count[1];
            $totalCountMonth2 += $count[2];
            $totalCountMonth3 += $count[3];
            $totalCountMonth4 += $count[4];
            $totalCountMonth5 += $count[5];
            $totalCountMonth6 += $count[6];
            $totalCountMonth7 += $count[7];
            $totalCountMonth8 += $count[8];
            $totalCountMonth9 += $count[9];
            $totalCountMonth10 += $count[10];
            $totalCountMonth11 += $count[11];
            $totalCountMonth12 += $count[12];
            $totalCountAllMonth += $ttl; //number_format($ttl, 2);
            $totalCountAllUser += $userttl;

            $rows[] = [
                $lead->customer->name,
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalLeadToCloseReport(\'' . $lead->customer->name . '\',' . $lead->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
          }

        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function leadToCloseDetailReport(Request $request)
    {
        $lead_id = $request->lead_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $lead = Lead::find($lead_id);
        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];
        foreach ($lead->quotes AS $quote)
        {
            $job = Job::where('quote_id', $quote->id);
            if($start != '') $job = $job->where('created_at', '>=', Carbon::parse($startDate));
            if($end != '') $job = $job->where('created_at', '<=', Carbon::parse($endDate));
            $job = $job->first();

            if (!$job) continue;

            $obj = QuoteGeneratorNew::getQuoteObject($quote);

            $lead = Lead::find($quote->lead_id);
            $customer = Customer::find($lead->customer_id);

            $rows[] = [
                "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                $job->created_at->format("m/d/y"),
                "$". number_format($quote->finance_total,2),
                $profit ? number_format(
                    ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
            ];
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function CabinetInstallDate(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $jobSchedules = JobSchedule::whereBetween('created_at', [Carbon::parse($start), Carbon::parse($end)])
                  ->where(function ($query) {
                      $query->where('group_id', '=', '9')
                            ->orWhere('group_id', '=', '4');
                  })
                  ->groupBy('job_id')
                  ->get();

        foreach ($jobSchedules AS $jobSchedule)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;

            $quote = $jobSchedule->job->quote;

            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }

            $job = Job::where('quote_id', $quote->id)->first();

            if (!$job) continue;

            if ($quote->finance_total == 0)
            {
                $details = QuoteGeneratorNew::getQuoteObject($quote);
                $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                $quote->finance_total = $financeTotal;
                $quote->save();
            }
            else
            {
                $financeTotal = $quote->finance_total;
            }
            $month = $job->created_at->format('n');

            if (!isset($months[$month])) $months[$month] = 0;
            if (!isset($count[$month])) $count[$month] = 0;
            if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
            if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
            if (!isset($quotesList[$month])) $quotesList[$month] = null;
            $months[$month] = $months[$month] + $financeTotal;
            $count[$month] = $count[$month] + 1;
            $grandTTL[$month] = $grandTTL[$month] + 1;
            $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
            $quotesList[$month] .= $quote->id . ", ";
            $userttl++;
            $allttl++;
            $ttl += $financeTotal;
            $grand += $financeTotal;

            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                //else $months[$i] = number_format($months[$i], 2);

                if (!isset($count[$i])) $count[$i] = 0;
                //else $count[$i] = number_format($count[$i], 2);
            }

            $totalMonth1 += $months[1];
            $totalMonth2 += $months[2];
            $totalMonth3 += $months[3];
            $totalMonth4 += $months[4];
            $totalMonth5 += $months[5];
            $totalMonth6 += $months[6];
            $totalMonth7 += $months[7];
            $totalMonth8 += $months[8];
            $totalMonth9 += $months[9];
            $totalMonth10 += $months[10];
            $totalMonth11 += $months[11];
            $totalMonth12 += $months[12];
            $totalCountMonth1 += $count[1];
            $totalCountMonth2 += $count[2];
            $totalCountMonth3 += $count[3];
            $totalCountMonth4 += $count[4];
            $totalCountMonth5 += $count[5];
            $totalCountMonth6 += $count[6];
            $totalCountMonth7 += $count[7];
            $totalCountMonth8 += $count[8];
            $totalCountMonth9 += $count[9];
            $totalCountMonth10 += $count[10];
            $totalCountMonth11 += $count[11];
            $totalCountMonth12 += $count[12];
            $totalCountAllMonth += $ttl; //number_format($ttl, 2);
            $totalCountAllUser += $userttl;

            $customerName = $jobSchedule->job->quote->lead->customer->name;

            $rows[] = [
                $customerName,
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalCabinetInstallDateReport(\'' . $customerName . '\',' . $job->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
          }

        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function cabinetInstallDateDetailReport(Request $request)
    {
        $job_id = $request->job_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        $job = Job::find($job_id);
        $quote = $job->quote;
        $obj = QuoteGeneratorNew::getQuoteObject($quote);
        $customer = Customer::find($quote->lead->customer_id);

        $monthJob = $job->created_at->format('n');
        if($monthJob == $month)
        {

            $rows[] = [
                "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                $job->created_at->format("m/d/y"),
                "$". number_format($quote->finance_total,2),
                $profit ? number_format(
                    ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
            ];
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function FinalPaymentDate(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $ffts = Fft::where('paid', '1')
                  ->where('paid_on', '>=', Carbon::parse($start))
                  ->where('paid_on', '<=', Carbon::parse($end))
                  ->get();

        foreach ($ffts AS $fft)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;

            $quote = $fft->job->quote;

            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }

            $job = Job::where('quote_id', $quote->id)->first();

            if (!$job) continue;

            if ($quote->finance_total == 0)
            {
                $details = QuoteGeneratorNew::getQuoteObject($quote);
                $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                $quote->finance_total = $financeTotal;
                $quote->save();
            }
            else
            {
                $financeTotal = $quote->finance_total;
            }
            $month = $job->created_at->format('n');

            if (!isset($months[$month])) $months[$month] = 0;
            if (!isset($count[$month])) $count[$month] = 0;
            if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
            if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
            if (!isset($quotesList[$month])) $quotesList[$month] = null;
            $months[$month] = $months[$month] + $financeTotal;
            $count[$month] = $count[$month] + 1;
            $grandTTL[$month] = $grandTTL[$month] + 1;
            $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
            $quotesList[$month] .= $quote->id . ", ";
            $userttl++;
            $allttl++;
            $ttl += $financeTotal;
            $grand += $financeTotal;

            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                //else $months[$i] = number_format($months[$i], 2);

                if (!isset($count[$i])) $count[$i] = 0;
                //else $count[$i] = number_format($count[$i], 2);
            }

            $totalMonth1 += $months[1];
            $totalMonth2 += $months[2];
            $totalMonth3 += $months[3];
            $totalMonth4 += $months[4];
            $totalMonth5 += $months[5];
            $totalMonth6 += $months[6];
            $totalMonth7 += $months[7];
            $totalMonth8 += $months[8];
            $totalMonth9 += $months[9];
            $totalMonth10 += $months[10];
            $totalMonth11 += $months[11];
            $totalMonth12 += $months[12];
            $totalCountMonth1 += $count[1];
            $totalCountMonth2 += $count[2];
            $totalCountMonth3 += $count[3];
            $totalCountMonth4 += $count[4];
            $totalCountMonth5 += $count[5];
            $totalCountMonth6 += $count[6];
            $totalCountMonth7 += $count[7];
            $totalCountMonth8 += $count[8];
            $totalCountMonth9 += $count[9];
            $totalCountMonth10 += $count[10];
            $totalCountMonth11 += $count[11];
            $totalCountMonth12 += $count[12];
            $totalCountAllMonth += $ttl; //number_format($ttl, 2);
            $totalCountAllUser += $userttl;

            $customerName = '';
            $customer = $quote->lead->customer;
            if($customer) $customerName = $customer->name;
            $rows[] = [
                $customerName,
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalFinalPaymentDateReport(\'' . $customerName . '\',' . $fft->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
          }

        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function finalPaymentDateDetailReport(Request $request)
    {
        $fft_id = $request->fft_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        $fft = Fft::find($fft_id);
        $job = Job::find($fft->job_id);
        $quote = $job->quote;
        $obj = QuoteGeneratorNew::getQuoteObject($quote);
        $customer = Customer::find($quote->lead->customer_id);

        $monthJob = $job->created_at->format('n');
        if($monthJob == $month)
        {

            $rows[] = [
                "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                $job->created_at->format("m/d/y"),
                "$". number_format($quote->finance_total,2),
                $profit ? number_format(
                    ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
            ];
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

    public function CloseoutDate(Request $request)
    {
        $start = $request->start;
        $end = $request->end;
        $type = $request->type;

        $grand = 0;
        $allttl = 0;

        for ($i = 1; $i <= 12; $i++)
        {
            $grandTTL[$i] = 0;
            $grandMonth[$i] = 0;
        }

        $ffts = Fft::where('closed_on', '>=', Carbon::parse($start))
                  ->where('closed_on', '<=', Carbon::parse($end))
                  ->get();

        foreach ($ffts AS $fft)
        {
            $totalMonth1 = 0;
            $totalMonth2 = 0;
            $totalMonth3 = 0;
            $totalMonth4 = 0;
            $totalMonth5 = 0;
            $totalMonth6 = 0;
            $totalMonth7 = 0;
            $totalMonth8 = 0;
            $totalMonth9 = 0;
            $totalMonth10 = 0;
            $totalMonth11 = 0;
            $totalMonth12 = 0;
            $totalCountMonth1 = 0;
            $totalCountMonth2 = 0;
            $totalCountMonth3 = 0;
            $totalCountMonth4 = 0;
            $totalCountMonth5 = 0;
            $totalCountMonth6 = 0;
            $totalCountMonth7 = 0;
            $totalCountMonth8 = 0;
            $totalCountMonth9 = 0;
            $totalCountMonth10 = 0;
            $totalCountMonth11 = 0;
            $totalCountMonth12 = 0;
            $totalCountAllMonth = 0;
            $totalCountAllUser = 0;

            $months = [];
            $count = [];
            $ttl = 0;
            $userttl = 0;

            $quote = $fft->job->quote;

            $quotesList = [];
            for ($i = 1; $i <= 12; $i++)
            {
                $quotesList[$i] = null;
            }

            $job = Job::where('quote_id', $quote->id)->first();

            if (!$job) continue;

            if ($quote->finance_total == 0)
            {
                $details = QuoteGeneratorNew::getQuoteObject($quote);
                $financeTotal = QuoteGeneratorNew::financing($quote, $details, true);
                $quote->finance_total = $financeTotal;
                $quote->save();
            }
            else
            {
                $financeTotal = $quote->finance_total;
            }
            $month = $job->created_at->format('n');

            if (!isset($months[$month])) $months[$month] = 0;
            if (!isset($count[$month])) $count[$month] = 0;
            if (!isset($grandTTL[$month])) $grandTTL[$month] = 0;
            if (!isset($grandMonth[$month])) $grandMonth[$month] = 0;
            if (!isset($quotesList[$month])) $quotesList[$month] = null;
            $months[$month] = $months[$month] + $financeTotal;
            $count[$month] = $count[$month] + 1;
            $grandTTL[$month] = $grandTTL[$month] + 1;
            $grandMonth[$month] = $grandMonth[$month] + $financeTotal;
            $quotesList[$month] .= $quote->id . ", ";
            $userttl++;
            $allttl++;
            $ttl += $financeTotal;
            $grand += $financeTotal;

            for ($i = 1; $i <= 12; $i++)
            {
                if (!isset($months[$i])) $months[$i] = 0;
                //else $months[$i] = number_format($months[$i], 2);

                if (!isset($count[$i])) $count[$i] = 0;
                //else $count[$i] = number_format($count[$i], 2);
            }

            $totalMonth1 += $months[1];
            $totalMonth2 += $months[2];
            $totalMonth3 += $months[3];
            $totalMonth4 += $months[4];
            $totalMonth5 += $months[5];
            $totalMonth6 += $months[6];
            $totalMonth7 += $months[7];
            $totalMonth8 += $months[8];
            $totalMonth9 += $months[9];
            $totalMonth10 += $months[10];
            $totalMonth11 += $months[11];
            $totalMonth12 += $months[12];
            $totalCountMonth1 += $count[1];
            $totalCountMonth2 += $count[2];
            $totalCountMonth3 += $count[3];
            $totalCountMonth4 += $count[4];
            $totalCountMonth5 += $count[5];
            $totalCountMonth6 += $count[6];
            $totalCountMonth7 += $count[7];
            $totalCountMonth8 += $count[8];
            $totalCountMonth9 += $count[9];
            $totalCountMonth10 += $count[10];
            $totalCountMonth11 += $count[11];
            $totalCountMonth12 += $count[12];
            $totalCountAllMonth += $ttl; //number_format($ttl, 2);
            $totalCountAllUser += $userttl;

            $customerName = '';
            $customer = $quote->lead->customer;
            if($customer) $customerName = $customer->name;
            $rows[] = [
                $customerName,
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',1,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth1 . '</a>' . ' (' . $totalCountMonth1 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',2,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth2 . '</a>' . ' (' . $totalCountMonth2 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',3,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth3 . '</a>' . ' (' . $totalCountMonth3 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',4,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth4 . '</a>' . ' (' . $totalCountMonth4 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',5,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth5 . '</a>' . ' (' . $totalCountMonth5 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',6,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth6 . '</a>' . ' (' . $totalCountMonth6 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',7,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth7 . '</a>' . ' (' . $totalCountMonth7 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',8,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth8 . '</a>' . ' (' . $totalCountMonth8 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',9,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth9 . '</a>' . ' (' . $totalCountMonth9 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',10,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth10 . '</a>' . ' (' . $totalCountMonth10 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',11,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth11 . '</a>' . ' (' . $totalCountMonth11 . ')',
                '<a href="#" onclick="ShowModalCloseoutDateReport(\'' . $customerName . '\',' . $fft->id . ',12,\'' . $start . '\',\'' . $end . '\')">' . $totalMonth12 . '</a>' . ' (' . $totalCountMonth12 . ')',
                number_format($totalCountAllMonth, 2) . ' (' . $totalCountAllUser . ')'
            ];
          }

        $rows[] = [
            null,
            number_format($grandMonth[1], 2) . " (" . @$grandTTL[1] . ")",
            number_format($grandMonth[2], 2) . " (" . @$grandTTL[2] . ")",
            number_format($grandMonth[3], 2) . " (" . @$grandTTL[3] . ")",
            number_format($grandMonth[4], 2) . " (" . @$grandTTL[4] . ")",
            number_format($grandMonth[5], 2) . " (" . @$grandTTL[5] . ")",
            number_format($grandMonth[6], 2) . " (" . @$grandTTL[6] . ")",
            number_format($grandMonth[7], 2) . " (" . @$grandTTL[7] . ")",
            number_format($grandMonth[8], 2) . " (" . @$grandTTL[8] . ")",
            number_format($grandMonth[9], 2) . " (" . @$grandTTL[9] . ")",
            number_format($grandMonth[10], 2) . " (" . @$grandTTL[10] . ")",
            number_format($grandMonth[11], 2) . " (" . @$grandTTL[11] . ")",
            number_format($grandMonth[12], 2) . " (" . @$grandTTL[12] . ")",
            number_format($grand, 2) . " ($allttl)"

        ];

        return array( 	'iTotalRecords' => count($rows),
              'iTotalDisplayRecords' => count($rows),
              'data' => $rows
            );
    }

    public function closeoutDateDetailReport(Request $request)
    {
        $fft_id = $request->fft_id;
        $month = $request->month;
        $start = $request->start;
        $end = $request->end;
        $profit = $request->profit;

        $year = Carbon::parse($end)->year;

        $date = "{$month}/1/{$year}";
        $startDate = Carbon::parse($date);
        $endDate = Carbon::parse($date)->addMonth();

        $rows = [];

        $fft = Fft::find($fft_id);
        $job = Job::find($fft->job_id);
        $quote = $job->quote;
        $obj = QuoteGeneratorNew::getQuoteObject($quote);
        $customer = Customer::find($quote->lead->customer_id);

        $monthJob = $job->created_at->format('n');
        if($monthJob == $month)
        {

            $rows[] = [
                "<a href='" . route('view_profile', ['id' => $customer->id]) . "'>{$customer->name}</a>",
                "<a href='" . route('quote_view', ['id' => $quote->id]) . "'>{$quote->created_at->format("m/d/y")}</a>",
                $job->created_at->format("m/d/y"),
                "$". number_format($quote->finance_total,2),
                $profit ? number_format(
                    ($obj->forFrugal + 150.00 + $obj->cabinetBuildup) - ($obj->discounts),2) : null
            ];
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 5)
          ]
        );
    }

}
