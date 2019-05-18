<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\vl\quotes\QuoteGeneratorNew;
use FK3\Models\Job;
use FK3\Models\Quote;
use FK3\Models\Payout;
use FK3\Models\PayoutItem;
use FK3\Models\Group;
use FK3\Models\User;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;
use Mail;

class PayoutController extends Controller
{
    public $auditPage = "Payout";

    /*
     * Show Payout Index.
     */
    public function index(Request $request)
    {
        return view('payouts.index', compact('request'));
    }

    /*
     * Create Payout.
     */
    public function create(Request $request)
    {
        $job_id = $request->job_id;

        $groups = Group::orderBy('name')->get();
        return view('payouts.create', compact('job_id', 'groups'));
    }

    /*
     * Store Payout.
     */
    public function store(Request $request)
    {
        $job = Job::find($request->job_id);
        if ($job->payout_additionals)
        {
            $adds = unserialize($job->payout_additionals);
        }
        else $adds = [];
        $payout = new Payout();
        $payout->job_id = $request->job_id;
        $payout->group_id = $request->group_id;
        $payout->user_id = 0;
        $payout->total = 0;
        $payout->save();
        $adds[] = [$payout->id];
        $adds = serialize($adds);
        $job->payout_additionals = $adds;
        $job->save();

        return redirect(route('payouts.edit', ['id' => $payout->id]));
    }

    /*
     * Edit Payout.
     */
    public function edit($id, Request $request)
    {
        $payout = Payout::find($id);
        $users = User::where(function ($t) use ($payout)
                            {
                                $t->where('group_id', $payout->group_id);
                                $t->orWhere('group_id', 24);
                            })
                      ->whereActive(true)->get();

		$job = Job::find($payout->job_id);

        return view('payouts.edit', compact('payout', 'users', 'job'));
    }

    /*
     * Update Payout.
     */
    public function updatePayout($id, Request $request)
    {
        $payout = Payout::find($id);
        $payout->user_id = $request->user_id;
        $payout->total = $request->total;
        $payout->invoice = $request->invoice;
        $payout->notes = $request->notes;
        $payout->approved = $request->approved;
        $payout->paid = $request->paid;
        $payout->paid_on = Carbon::parse($request->paid_on)->format('Y-m-d');
        $payout->check = $request->check;
        $payout->save();

        return redirect(route('payouts.edit', ['id' => $payout->id]))->with('success', 'Payout Updated');
    }

    public function deletePayout($id)
    {
        PayoutItem::where('payout_id', $id)->delete();
        Payout::where('id', $id)->delete();

        return redirect(route('payouts.index'))->with('success', 'Payout deleted.');
    }

    public function approvePayout($id)
    {
        $payout = Payout::find($id);
        if(!$payout->user) return redirect()->back()->with('error', 'Can\'t approve, cause no user set yet.');

        $customer = $payout->job->quote->lead->customer->contacts()->first()->email;
        $custname = $payout->job->quote->lead->customer->contacts()->first()->name;
        $data['content'] = "The payout for {$payout->user->name} for $custname has been approved. Please cut a check and record the check number.";

        Mail::send('emails.notification', $data, function ($message) use ($payout, $customer, $custname)
        {
            $message->to(['kimw@frugalkitchens.com']);
            $message->subject("[$custname] Payment to {$payout->user->name} has been approved.");
        });

        $payout->approved = '1';
        $payout->save();

        return redirect(route('payouts.index'))->with('success', 'Payout approved.');
    }

    public function reportPayout($user_id)
    {
        $user = User::find($user_id);

        return view('payouts.report', compact('user'));
    }

    public function getReportPayouts(Request $request)
    {
        $user_id = $request->user_id;
        //$start = Carbon::now()->subDays(30);
        $payouts = Payout::whereUserId($user_id)
                          //->where('paid_on', '>=', $start)
                          ->get();

        $data = '';
        foreach($payouts as $payout)
        {
            $data .= "<tr>";
            $data .= "<td><input type='checkbox' name='p_$payout->id'></td>";
            $data .= "<td>" . @$payout->job->quote->lead->customer->name . "</td>";
            $data .= "<td>" . @$payout->job->start_date . "</td>";
            $data .= "<td>" . $payout->paid_on . "</td>";
            $data .= "<td>" . $payout->check . "</td>";
            $data .= "<td>" . $payout->total . "</td>";

            $items = null;
            foreach ($payout->items as $item)
            {
                $items .= $item->item . " ($item->amount), ";
            }

            $data .= "<td>" . $items . "</td>";
            $data .= "</tr>";
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    /**
     * Generate a CSV report of the user.
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createReportPayout($id)
    {
        $start = Carbon::now();
        $user = User::find($id);
        $start = $start->subDays(7);
        $rows[] = ['Customer', 'Job Date', 'Paid On', 'Check Number', 'Amount', 'Items'];
        $req = app('request');
        $inc = [];
        foreach ($req->all() as $req => $id)
        {
            if (preg_match('/p_/', $req))
            {
                $r = str_replace("p_", null, $req);
                $inc[] = $r;
            }
        }
        foreach (Payout::whereIn('id', $inc)->get() as $payout)
        {
            $rows[] = [
              $payout->job->quote->lead->customer->name,
              $payout->job->start_date,
              $payout->paid_on,
              $payout->check,
              $payout->total,
              null
            ];
            foreach ($payout->items as $item)
            {
                $rows[] = [
                    null,null,null,null,null,
                    strip_tags(str_replace(",", null, $item->item)) . " - " . "$" . $item->amount
                ];
            }
        }
        $data = null;
        foreach ($rows as $row)
        {
            $data .= implode(",", $row) . "\n";
        }
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export-'.$user->name.'.csv"',
        ];

        return Response::make($data, 200, $headers);

    }

    /*
     * Load Payouts Data.
     */
    public function loadPayouts(Request $request)
    {
        $rows = $this->LoadPayout($request);
        $data = $this->GenerateTableRows($rows);

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function GenerateTableRows($rows, $countHeader = 0)
    {
        $finalRows = '';
        for($x = 0; $x < count($rows); $x++)
        {
            $color = '';
            $substract = 0;
            if($countHeader > 0 &&  ($countHeader < count($rows[$x])))
            {
                $color = 'style="background-color:' . $rows[$x][$countHeader] . '"';
                $substract = 1;
            }

            $finalRows .= '<tr ' . $color . '>';
            for($z = 0; $z < (count($rows[$x]) - $substract); $z++)
            {
                $finalRows .= '<td>';
                $finalRows .= $rows[$x][$z];
                $finalRows .= '</td>';
            }
            $finalRows .= '</tr>';
        }

        return $finalRows;
    }

    public function generatePayout($job, $group)
    {
        $quote = $job->quote()->whereAccepted(true)->first();
        if (!$quote) return "Quote Not Found. Really old job?";
        if ($group == 8) // Groups and delivery share.. ugh.
        {
            $uid = $job->quote->lead->user_id;
        }
        else
        {
            $schedule = $job->schedules()->whereGroupId($group)->first();
            if (!$schedule) return null; // No Assignment
            $uid = $schedule->user_id;
        }
        $details = QuoteGeneratorNew::getQuoteObject($quote);
        switch ($group)
        {
            case 4 : // Installer
                $amount = $details->forInstaller;
                break;
            case 1 : // Plumber
                $amount = $details->forPlumber;
                break;
            case 2 : // Electrician
                $amount = $details->forElectrician;
                break;
            case 8 : // Designer
                $amount = $details->forDesigner;
                break;
            case 11 : // Flooring Contractor
                $amount = $details->tile;
                break;
            default :
                $amount = 0;
                break;

        }

        $payout = Payout::create([
            'job_id'         => $job->id,
            'paid'           => 0,
            'archived'       => 0,
            'approved'       => 0,
            'user_id'        => $uid,
            'group_id'       => $group,
            'total'          => $amount ?: 0
        ]);
        $debugs = $details->debug;
        foreach ($debugs AS $debug)
        {
            switch ($group)
            {

                case 4 : // Cabinet Installer
                    if (preg_match('/installer/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                    {
                        (new PayoutItem)->create([
                            'payout_id' => $payout->id,
                            'item'      => $debug[0],
                            'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                        ]);
                    }
                    break;
                case 8 : // Designer
                    if (preg_match('/designer/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                    {
                        (new PayoutItem)->create([
                            'payout_id' => $payout->id,
                            'item'      => $debug[0],
                            'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                        ]);
                    }
                    break;
                case 1 : // Plumber
                    if (preg_match('/plumber/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                    {
                        (new PayoutItem)->create([
                            'payout_id' => $payout->id,
                            'item'      => $debug[0],
                            'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                        ]);
                    }
                    break;
                case 2 :  // Electrician
                    if (preg_match('/electrician|electrican/i', $debug[0]) && !preg_match('/applying/i', $debug[0]))
                    {
                        (new PayoutItem)->create([
                            'payout_id' => $payout->id,
                            'item'      => $debug[0],
                            'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                        ]);
                    }
                    break;
                case 11 : // Flooring
                    if (preg_match('/tile/i', $debug[0]))
                    {
                        (new PayoutItem)->create([
                            'payout_id' => $payout->id,
                            'item'      => $debug[0],
                            'amount'    => trim(str_replace(",", null, $debug[1])) ?: 0
                        ]);
                    }
                    break;
            }
        }
        // Create line items.

    }

    public function getPayouts($job, $group)
    {
        $dtext = [
            4  => 'Cabinet Installer',
            1  => 'Plumber',
            2  => 'Electrician',
            8  => 'Designer',
            11 => 'Flooring Contractor',
            3  => 'Granite Company'
        ];

        $quote = $job->quote()->whereAccepted(true)->first();
        if (!$quote) return [null, null, null, "No Quote found - Must be really old job!", null, null, null, null, null];
        $payout = Payout::whereJobId($job->id)->whereGroupId($group)->first();
        if (!$payout)
        {
            $payout = $this->generatePayout($job, $group);
            if (!$payout)
            {
                return [null, null, null, null, "No $dtext[$group] found to pay.", null, null, null, null, null];
            }
        }
        $approval = (Auth::user()->id == 5 || Auth::user()->id == 1) ? "<a href='" . route('approve_payout', ['id' => $payout->id]) . "'><span style='color: red'>No</span></a>" : "<span style='color: red'>No</span>";

        return [
            null,
            null,
            null,
            null,
            $payout->user && $payout->user->group ? $payout->user->group->name . " - " . "<a href='" . route('report_payout', ['user_id' => $payout->user_id]) . "'>{$payout->user->name}</a>" : "No User Found",
            "<a href='" . route('payouts.edit', ['id' => $payout->id]) . "'>$" . number_format($payout->total, 2) . "</a>",
            $payout->invoice ?: "None",
            $payout->check ?: "None",
            $payout->approved ? "<span style='color: green'>Yes</span>" : $approval,
            $payout->paid ? "<span style='color: green'>" . Carbon::parse($payout->paid_on)
                    ->format("m/d/y") . "</span>" : "<span style='color: red'>No</span>"
        ];
    }

    public function getPayout($id)
    {
        $id = $id[0];
        $payout = Payout::find($id);
        if (!$payout) return [];
        $approval = (Auth::user()->id == 5 || Auth::user()->id == 1) ? "<a href='" . route('approve_payout', ['id' => $payout->id]) . "'><span style='color: red'>No</span></a>" : "<span style='color: red'>No</span>";
        $des_name = $payout->user ? $payout->user->group->name : "No Group";
        $user_name = $payout->user ? $payout->user->name : "Unassigned";
        return [
            null,
            null,
            null,
            null,
            $des_name . " - " . ($user_name == 'Unassigned' ? $user_name : "<a href='" . route('report_payout', ['user_id' => $payout->user_id]) . "'>{$user_name}</a>"),
            "<a href='" . route('payouts.edit', ['id' => $payout->id]) . "'>$" . number_format($payout->total, 2) . "</a>",
            $payout->invoice ?: "None",
            $payout->check ?: "None",
            $payout->approved ? "<span style='color: green'>Yes</span>" : $approval,
            $payout->paid ? "<span style='color: green'>" . Carbon::parse($payout->paid_on)
                    ->format("m/d/y") . "</span>" : "<span style='color: red'>No</span>"
        ];
    }

    public function LoadPayout($request)
    {
        $rows = [];
        if ($request->all == '1')
        {
            $jobs = Job::with('fft')->wherePaid(true)->where('start_date', '>', '2016-01-01')->orderBy('created_at', 'DESC')
                ->get();
        }
        else
        {
            $jobs = Job::with('fft')->wherePaid(false)->where('start_date', '>', '2016-01-01')->orderBy('created_at', 'DESC')
                ->get();
        }
        $jobs = $jobs->sortBy('start_date');
        foreach ($jobs as $job)
        {
            if (!$job->quote) continue;
            $customer = @$job->quote->lead->customer->name ?: "Unknown Customer Association";
			$idCustomer = @$job->quote->lead->customer->id ? " | <small>ID: " .  @$job->quote->lead->customer->id . "</small>" : "";
            $archive = true;
            foreach (Payout::whereJobId($job->id)->get() as $p)
            {
                if (!$p->paid)
                    $archive = false;
            }
            if (Auth::user()->id == 1 || Auth::user()->id == 5 || Auth::user()->id == 7) $archive = true;
            if ($archive)
                $archive = "<span class='pull-right'><a class='get' href='" . route('job_paid', ['id' => $job->id]) . "'><i class='fa fa-archive'></a></span>";
            $rows[] = [
                "<a name='$job->id'>$customer $idCustomer</a> $archive",
                @$job->quote->type->name,
                Carbon::parse($job->start_date)->format("m/d/y"),
                Carbon::parse($job->fft->signoff_stamp)->timestamp > 0 ? Carbon::parse($job->fft->signoff_stamp)
                    ->format("m/d/y") : "Not Signed",
                "<span class='pull-right'><a href='" . route('payouts.create') . "?job_id=$job->id'>+ new</a></span>",
                null,
                null,
                null,
                null,
                null
            ];
            $rows[] = $this->getPayouts($job, 8); // Designer
            $rows[] = $this->getPayouts($job, 4); // Installer
            $rows[] = $this->getPayouts($job, 1); // Plumber
            $rows[] = $this->getPayouts($job, 2); // Electrician
            $rows[] = $this->getPayouts($job, 11); // Flooring Contractor
            $rows[] = $this->getPayouts($job, 3); // Granite
            if ($job->payout_additionals)
            {
                $adds = unserialize($job->payout_additionals);
                foreach ($adds as $add)
                {
                    $rows[] = $this->getPayout($add);
                }
            }
        }

        return $rows;
    }
}
