<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
use FK3\Models\LeadUpdate;
use FK3\Models\Quote;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Customer;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class IndexController extends Controller
{
    public $auditPage = "Dashboard";

    /*
     * Show Dashboard Index.
     */
    public function dashboard(Request $request)
    {
        return view('dashboard', compact('request'));
    }

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

    public $SHOWROOM_SCHEDULED = 4;
    public $QUOTE_PROVIDED = 10;
    public $SOLD = 14;
    public $WAITING_FOR_CUSTOMER = 12;
    public $NO_SHOW = 49;

    /**
     * Get Commission Pipeline
     *
     * @param string $for week or year
     * @param        $status
     * @return null
     */
    function getPipeline($for = 'week', $status, $user = null, $year = null)
    {
        if (!$user)
        {
            $user = Auth::user()->id;
        }
        switch ($for)
        {
            case 'week' :
                for ($i = 0; $i <= 6; $i++)
                {
                    $start = Carbon::now()->startOfWeek()->addDays($i);
                    $end = Carbon::now()->startOfWeek()->addDays($i)->endOfDay();
                    $total[$i] = 0;
                    $leads = LeadUpdate::whereStatus($status)
                        //->groupBy('lead_id')
                        ->whereUserId($user)
                        ->where('created_at', '>=', $start)
                        ->where('created_at', '<=', $end)->get();
                    foreach ($leads AS $lead)
                    {
                        $lead = Lead::find($lead->id);
                        $quotes = Quote::where($lead->quote_id)->get();
                        if ($quotes)
                        {
                            $quoteFirst = $quotes->first();
                            if ($quoteFirst)
                            {
                                if ($quoteFirst->for_designer == 0)
                                {
                                    $total[$i] += QuoteGenerator::getQuoteObject($quoteFirst)->forDesigner;
                                }
                                else
                                {
                                    $total[$i] += $quoteFirst->for_designer;
                                }
                            }
                        }
                    }
                }
                break;
            case 'year' :
                $grand = 0;
                for ($i = 0; $i <= 11; $i++)
                {
                    $total[$i] = 0;
                    $start = Carbon::now()->startOfYear()->addMonths($i);
                    $end = Carbon::now()->startOfYear()->addMonths($i)->endOfMonth();
                    $leads = LeadUpdate::whereStatus($status)
                        ->whereUserId($user)
                        //->groupBy('lead_id')
                        ->where('created_at', '>=', $start)
                        ->where('created_at', '<=', $end)
                        ->get();
                    foreach ($leads AS $lead)
                    {
                        $lead = $lead->lead;
                        $quotes = $lead->quotes;
                        if ($quotes)
                        {
                            $quoteFirst = $quotes->first();
                            if ($quoteFirst)
                            {
                                if ($quoteFirst->for_designer > 0)
                                {
                                    $amt = $quoteFirst->for_designer;
                                }
                                else
                                {
                                    $amt = QuoteGenerator::getQuoteObject($quoteFirst)->forDesigner;
                                }
                                $total[$i] += $amt;
                                $grand += $amt;
                            }
                        }
                    }
                }
                $total[$i] = $grand;
                break;

            case 'annual' :
                $grand = 0;
                $i = $year;
                $total[$i] = 0;
                $start = Carbon::parse("{$i}-01-01");
                $end = Carbon::parse("{$i}-12-31");
                $leads = LeadUpdate::whereStatus($status)
                    ->whereUserId($user)
                    //->groupBy('lead_id')
                    ->where('created_at', '>=', $start)
                    ->where('created_at', '<=', $end)
                    ->get();
                foreach ($leads AS $lead)
                {
                    $lead = $lead->lead;
                    $quotes = $lead->quotes;
                    if ($quotes)
                    {
                        $quoteFirst = $quotes->first();
                        if ($quoteFirst)
                        {
                            if ($quoteFirst->for_designer > 0)
                            {
                                $amt = $quoteFirst->for_designer;
                            }
                            else
                            {
                                $amt = QuoteGenerator::getQuoteObject($quoteFirst)->forDesigner;
                            }
                            $total[$i] += $amt;
                            $grand += $amt;
                        }
                    }
                }

                $total[99] = $grand;

                break;
        }

        return $total;
    }

    public function weekly(Request $request)
    {
        if (!Auth::user()->superuser)
        {
            $rows = [];
            $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', self::weeklyFor($this->SHOWROOM_SCHEDULED)];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', self::weeklyFor($this->QUOTE_PROVIDED)];
            $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', self::getPipeline('week', $this->QUOTE_PROVIDED)];
            $rows[] = ['<span class="pull-right"><b>Sold', self::weeklyFor($this->SOLD)];
            $rows[] = ['<span class="pull-right"><b>No Shows', self::weeklyFor($this->NO_SHOW)];
            $rows[] = ['<span class="pull-right"><b>Waiting for Customer', self::weeklyFor($this->WAITING_FOR_CUSTOMER)];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('week', $this->SOLD)];
        }
        else
        {
            $rows = [];
            foreach (User::whereGroupId(20)->get() AS $user)
            {
                if (!$user->active) continue;
                $rows[] = ['<b>' . $user->name . "</b>"];
                $rows[] = [];
                $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', self::weeklyFor($this->SHOWROOM_SCHEDULED, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Quote Provided', self::weeklyFor($this->QUOTE_PROVIDED, $user->id)];
                $rows[] = [
                    '<span class="pull-right"><b>Commissions in Pipeline',
                    self::getPipeline('week', $this->QUOTE_PROVIDED, $user->id)
                ];
                $rows[] = ['<span class="pull-right"><b>Sold', self::weeklyFor($this->SOLD, $user->id)];
                $rows[] = ['<span class="pull-right"><b>No Shows', self::weeklyFor($this->NO_SHOW, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Waiting for Customer', self::weeklyFor($this->WAITING_FOR_CUSTOMER, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('week', $this->SOLD, $user->id)];
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 6)
          ]
        );
    }

    /**
     * Get a weekly status array for the status provided.
     *
     * @param      $status
     * @param null $user Override for Admin
     * @return
     */
    function weeklyFor($status, $user = null)
    {
        if (!$user)
        {
            $user = Auth::user()->id;
        }
        for ($i = 0; $i <= 6; $i++)
        {
            $start = Carbon::now()->startOfWeek()->addDays($i);
            $end = Carbon::now()->startofWeek()->addDays($i)->endOfDay();

            $cCount = LeadUpdate::whereStatus($status)
                ->whereUserId($user)
                //->groupBy('lead_id')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count();

            $count[$i] = '<a href="#" onclick="ShowModalLeadUpdates(\'' . $status . '\',\'' . $user . '\',\'' . $start->toDateString() . '\',\'' . $end->toDateString() . '\')">' . $cCount . '</a>';
        }
        return $count;
    }

    /**
     * Get monthly status for status provided.
     *
     * @param $status
     * @return array
     */
    function monthlyFor($status, $user = null)
    {
        if (!$user)
        {
            $user = Auth::user()->id;
        }
        $total = 0;
        for ($i = 0; $i <= 11; $i++) // we start with 1 already for the month
        {
            $start = Carbon::now()->startOfYear()->addMonths($i);
            $end = Carbon::now()->startOfYear()->addMonths($i)->endOfMonth();
            //   Log::info("[$i] Checking $start to $end");
            $cCount = LeadUpdate::whereStatus($status)
                ->whereUserId($user)
                //->groupBy('lead_id')
                ->where('created_at', '>=', $start)
                ->where('created_at', '<=', $end)
                ->count();

            $count[$i] = '<a href="#" onclick="ShowModalLeadUpdates(\'' . $status . '\',\'' . $user . '\',\'' . $start->toDateString() . '\',\'' . $end->toDateString() . '\')">' . $cCount . '</a>';

            $total += $cCount;
        }
        $count[] = $total;
        return $count;
    }

    /**
     * Get monthly status for status provided.
     *
     * @param $status
     * @return array
     */
    function yearlyFor($status, $user = null, $year)
    {
        if (!$user)
        {
            $user = Auth::user()->id;
        }
        $total = 0;
        $start = Carbon::parse("{$year}-01-01");
        $end = Carbon::parse("{$year}-12-31");
        //   Log::info("[$i] Checking $start to $end");
        $cCount = LeadUpdate::whereStatus($status)
            ->whereUserId($user)
            //->groupBy('lead_id')
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->count();
        $count[$year] = '<a href="#" onclick="ShowModalLeadUpdates(\'' . $status . '\',\'' . $user . '\',\'' . $start->toDateString() . '\',\'' . $end->toDateString() . '\')">' . $cCount . '</a>';
        $total += $cCount;
        $count[] = $total;
        return $count[$year];
    }

    public function yearly(Request $request)
    {
        $year = $request->year;
        if (!Auth::user()->superuser)
        {
            $rows = [];
            $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', self::yearlyFor($this->SHOWROOM_SCHEDULED, null, $year)];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', self::yearlyFor($this->QUOTE_PROVIDED, null, $year)];
            $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', self::getPipeline('annual', $this->QUOTE_PROVIDED, $year)[99]];
            $rows[] = ['<span class="pull-right"><b>Sold', self::yearlyFor($this->SOLD, null, $year)];
            $rows[] = ['<span class="pull-right"><b>No Shows', self::yearlyFor($this->NO_SHOW, null, $year)];
            $rows[] = ['<span class="pull-right"><b>Waiting for Customer', self::yearlyFor($this->WAITING_FOR_CUSTOMER, null, $year)];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('annual', $this->SOLD, $year)[99]];
        }
        else
        {
            $rows = [];
            foreach (User::whereGroupId(20)->get() AS $user)
            {
                if (!$user->active) continue;
                $rows[] = ['<b>' . $user->name . "</b>"];
                $rows[] = [];
                $rows[] = [
                    '<span class="pull-right"><b>Showroom Scheduled',
                    self::yearlyFor($this->SHOWROOM_SCHEDULED, $user->id, $year)
                ];
                $rows[] = ['<span class="pull-right"><b>Quote Provided', self::yearlyFor($this->QUOTE_PROVIDED, $user->id, $year)];
                $rows[] = [
                    '<span class="pull-right"><b>Commissions in Pipeline',
                    self::getPipeline('annual', $this->QUOTE_PROVIDED, $user->id, $year)[99]
                ];
                $rows[] = ['<span class="pull-right"><b>Sold', self::yearlyFor($this->SOLD, $user->id, $year)];
                $rows[] = ['<span class="pull-right"><b>No Shows', self::yearlyFor($this->NO_SHOW, $user->id, $year)];
                $rows[] = [
                    '<span class="pull-right"><b>Waiting for Customer',
                    self::yearlyFor($this->WAITING_FOR_CUSTOMER, $user->id, $year)
                ];
                $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('annual', $this->SOLD, $user->id, $year)[99]];
            }
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 0)
          ]
        );

    }

    /**
     * Show Monthly Table
     *
     * @return mixed
     */
    function monthly()
    {
        if (!Auth::user()->superuser)
        {
            $rows = [];
            $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', self::monthlyFor($this->SHOWROOM_SCHEDULED)];
            $rows[] = ['<span class="pull-right"><b>Quote Provided', self::monthlyFor($this->QUOTE_PROVIDED)];
            $rows[] = ['<span class="pull-right"><b>Commissions in Pipeline', getPipeline('year', $this->QUOTE_PROVIDED)];
            $rows[] = ['<span class="pull-right"><b>Sold', self::monthlyFor($this->SOLD)];
            $rows[] = ['<span class="pull-right"><b>No Shows', self::monthlyFor($this->NO_SHOW)];
            $rows[] = ['<span class="pull-right"><b>Waiting for Customer', self::monthlyFor($this->WAITING_FOR_CUSTOMER)];
            $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('year', $this->SOLD)];
        }
        else
        {
            $rows = [];
            foreach (User::whereGroupId(20)->get() AS $user)
            {
                if (!$user->active) continue;
                $rows[] = ['<b>' . $user->name . "</b>"];
                $rows[] = [];
                $rows[] = ['<span class="pull-right"><b>Showroom Scheduled', self::monthlyFor($this->SHOWROOM_SCHEDULED, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Quote Provided', self::monthlyFor($this->QUOTE_PROVIDED, $user->id)];
                $rows[] = [
                    '<span class="pull-right"><b>Commissions in Pipeline',
                    self::getPipeline('year', $this->QUOTE_PROVIDED, $user->id)
                ];
                $rows[] = ['<span class="pull-right"><b>Sold', self::monthlyFor($this->SOLD, $user->id)];
                $rows[] = ['<span class="pull-right"><b>No Shows', self::monthlyFor($this->NO_SHOW, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Waiting for Customer', self::monthlyFor($this->WAITING_FOR_CUSTOMER, $user->id)];
                $rows[] = ['<span class="pull-right"><b>Commissions Earned', self::getPipeline('year', $this->SOLD, $user->id)];
            }
        }
        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 12)
          ]
        );
    }

    public function showleadUpdates(Request $request)
    {
        $start = $request->startDate;
        $end = $request->endDate;
        $status = $request->status;
        $user_id = $request->user_id;

        $updates = LeadUpdate::whereStatus($status)
                   ->where('created_at', '>=', $start)
                   ->where('created_at', '<=', $end)
                   ->whereUserId($user_id)
                 //   ->groupBy('lead_id')
                    ->get();

        $rows = [];
        $ttl = 0;
        foreach ($updates AS $update)
        {
            $lead = $update->lead;
            $followups = $update->lead->followups();
            $customer = $lead->customer;
            $follow = ($lead && $followups->count() > 0)
                ? '<a data-toggle="tooltip" title="Follow Ups" href="#" style="float:right;" onclick="ShowModalFollowUp(' . $update->lead_id . ',\'' . ($customer ? $customer->name : '') . '\')"><i class="fa fa-phone"></i></a>'
                : null;

            $quotes = $update->lead->quotes();
            $quoteFirst = $quotes->first();
            $updateNewStatus = $update->newstatus;
            $rows[] = [
                "$follow ({$lead->id}) " . ($customer ? $customer->name : ''),
                $update->created_at->format("m/d/y h:i a"),
                $updateNewStatus ? $updateNewStatus->name : "Removed Status",
                $quoteFirst ? "<a href='/quotes/".$quoteFirst->id."/view'>".$quoteFirst->id."</a>" : null,
                $quoteFirst ? "$" . number_format($quoteFirst->for_designer,2) : 'N/A',
            ];
            $ttl += $lead && $quotes && $quoteFirst ? $quoteFirst->for_designer : 0;
        }
        $rows[] = [
            "<span class='pull-right'><B>TOTAL: </B>",
            "$" . number_format($ttl,2),
            null,
            null,
            null
        ];

        return Response::json(
          [
            'response' => 'success',
            'data' => $this->GenerateTableRows($rows, 0)
          ]
        );
    }

}
