<?php

namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Accessory;
use FK3\Models\Authorization;
use FK3\Models\AuthorizationItem;
use FK3\Models\AuthorizationList;
use FK3\Models\Checklist;
use FK3\Models\Lead;
use FK3\Models\User;
use FK3\Models\Fft;
use FK3\Models\FftNote;
use FK3\Models\Appliance;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\QuoteCabinet;
use FK3\Models\QuoteAppliance;
use FK3\Models\Question;
use FK3\Models\QuestionAnswer;
use FK3\Models\Cabinet;
use FK3\Models\Hardware;
use FK3\Models\Job;
use FK3\Models\JobItem;
use FK3\Models\JobNote;
use FK3\Models\JobSchedule;
use FK3\Models\Po;
use FK3\Models\PoItem;
use FK3\Models\Customer;
use FK3\Models\Contact;
use FK3\Models\Group;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Shop;
use FK3\Models\ShopCabinet;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use PDF;
use Response;
use Storage;
use Auth;
use App;

class PunchController extends Controller
{
    public $auditPage = "Punch";

    /*
     * Show punches Index.
     */
    public function index($fft_id, Request $request)
    {
        $fft = Fft::find($fft_id);
        $job = Job::find($fft->job_id);
        $quote = Quote::find($job->quote_id);
        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $groups = Group::orderBy('name', 'asc')->get();
        $title = "Warranty";
        if($request->has('warranty')) $title = 'Warranty';
        else if($request->has('service')) $title = 'Service';

        return view('punch.index', compact('fft', 'customer', 'groups', 'title', 'request'));
    }

    public function displayPunchItems(Request $request)
    {
        $fft_id = $request->fft_id;
        $fft = Fft::find($fft_id);

        $dbType = ($fft->warranty) ? "Warranty" : "FFT";
        if ($fft->warranty)
        {
            $dbType = "Warranty";
        }
        elseif ($fft->service)
        {
            $dbType = "Service";
        }
        else $dbType = "FFT";

        $jobItems = JobItem::where('job_id', $fft->job_id)
                            ->where('instanceof', $dbType);

        $total = $jobItems->count();
        $jobItems = $jobItems->get();

        $newItems = array();
        foreach($jobItems as $item)
        {
            $objItems = array();

            $data = null;
            $word = "Item(s)";
            if ($item->ordered != '0000-00-00')
            {
                $title = "{$word} ordered on " . Carbon::parse($item->ordered)->format('m/d/y');
                $color = 'success';
                $type = 'fa-cloud-upload';

            }
            else
            {
                $title = "{$word} not ordered.";
                $color = 'danger';
                $type = 'fa-cloud-upload';
            }
            if ($item->orderable)
            {
                $data .= "<a class='btn btn-{$color}' data-toggle='tooltip'
                          title='{$title}' href='/fft/$fft->id/item/$item->id/update'>
                          <i class='text-white fa {$type}'></i></a>";
            }

            if ($item->confirmed != '0000-00-00')
            {
                $title = "{$word} confirmed on " . Carbon::parse($item->ordered)->format('m/d/y');
                $color = 'success';
                $type = 'fa-check-square-o';
            }
            else
            {
                $title = "{$word} not confirmed";
                $color = 'danger';
                $type = 'fa-check-square-o';
            }
            if ($item->orderable)
            {
                $data .= "<a class='btn btn-{$color}' data-toggle='tooltip'
                        title='{$title}' href='/fft/$fft->id/item/$item->id/update'>
                        <i class='text-white fa {$type}'></i></a>";
            }

            if ($item->received != '0000-00-00')
            {
                $title = "{$word} received on " . Carbon::parse($item->received)->format('m/d/y');
                $color = 'success';
                $type = 'fa-arrow-down';
            }
            else
            {
                $title = "{$word} not received";
                $color = 'danger';
                $type = 'fa-arrow-down';
            }
            if ($item->orderable)
            {
                $data .= "<a class='btn btn-{$color}' data-toggle='tooltip'
                        title='$title' href='/fft/$fft->id/item/$item->id/update'>
                      <i class='text-white fa {$type}'></i></a>";
            }


            if ($item->verified != '0000-00-00')
            {
                $title = "{$word} verified on " . Carbon::parse($item->ordered)->format('m/d/y');
                $color = 'success';
                $type = 'fa-check';
            }
            else
            {
                $title = "{$word} not verified";
                $color = 'danger';
                $type = 'fa-check';

            }
            $data .= "<a class='btn btn-{$color}' data-toggle='tooltip'
                        title='{$title}' href='/fft/$fft->id/item/$item->id/update'>
                        <i class='text-white fa {$type}'></i></a>";

            $i = '<a href="#" onclick="ShowModalEditReference(' . $item->id . ')">' . $item->reference . '</a>';

            $poItem = PoItem::where('job_item_id', $item->id)->first();
            $po = false;
            if($poItem) $po = Po::find($poItem->po_id);
            if($po)
            {
                $link = $poItem ? "<br><span class='pull-right text-success'><small>Linked to PO: <a href='/po/{$po->id}'>{$po->number}</a> - Ships on: {$po->projected_ship}</small>" : null;
            }
            else
            {
                $link = '';
            }

            $notes = $item->notes ? '<a href="#" onclick="ShowModalEditNotes(' . $item->id . ')">' . $item->notes . '</a>' : '<a href="#" onclick="ShowModalEditNotes(' . $item->id . ')">--Empty--</a>';

            $contractor_notes = $item->contractor_notes ? '<a href="#" onclick="ShowModalEditContractorNotes(' . $item->id . ')">' . $item->contractor_notes . '</a>' : '<a href="#" onclick="ShowModalEditContractorNotes(' . $item->id . ')">--Empty--</a>';

            if ($item->orderable)
            {
                $data .= "<span class='btn btn-info'>Must be ordered</span>";
            }
            if ($item->replacement)
            {
                $data .= "<span class='btn btn-warning'>Replacement Part</span>";
            }
            if ($item->image1)
            {
                $data .= "<a class='btn btn-info' target='_blank' href='/item/" . $item->id . "/file/1/download'><i class='fa fa-image'></i>";
            }
            if ($item->image2)
            {
                $data .= "<a class='btn btn-info' target='_blank' href='/item/" . $item->id . "/file/2/download'><i class='fa fa-image'></i>";
            }
            if ($item->image3)
            {
                $data .= "<a class='btn btn-info' target='_blank' href='/item/" . $item->id . "/file/3/download'><i class='fa fa-image'></i>";
            }

            $group = Group::find($item->group_id);
            if(!$group)
            {
                $designation = '<a href="#" onclick="ShowModalSetGroup(' . $item->id . ')">--None Set--</a>';
            }
            else
            {
                $designation = '<a href="#" onclick="ShowModalSetGroup(' . $item->id . ')">' . $group->name . '</a>';
            }

            $extras = "
            <span class='pull-right'>
               &nbsp; <a class='get' href='/item/$item->id/orderable'><i class='fa fa-dollar'></i></a>
               &nbsp; <a class='get' href='/item/$item->id/replacement'><i class='fa fa-refresh'></i></a>
               &nbsp; <a class='get' href='/item/$item->id/delete'><i class='fa fa-trash-o'></i></a>
               </span>";

            $objItems[] = $i . $link . $extras;
            $objItems[] = $data;
            $objItems[] = $designation;
            $objItems[] = $notes;
            $objItems[] = $contractor_notes;
            $objItems[] = $item->contractor_complete ? "Yes" : "<a href='/item/$item->id/contractor_complete'>No</a>";
            $objItems[] = $item->created_at->format("m/d/y h:i a");

            $newItems[] = $objItems;

            //check Shop
            $shop = Shop::where('job_item_id', $item->id)->first();

            if($shop)
            {
                $shopCabinets = ShopCabinet::where('shop_id', $shop->id)->get();
                foreach ($shopCabinets as $cabinet)
                {
                    $objItems = array();
                    $delete = Auth::user()->id == 1 || Auth::user()->id == 5 ? "<a class='get' href='/shopitem/$cabinet->id/delete'><i class='fa fa-times'></i></a> " : null;

                    $quoteCabinet = QuoteCabinet::find($cabinet->quote_cabinet_id);
                    $cabinetModel = Cabinet::find($quoteCabinet->cabinet_id);
                    $objItems[] = '<span class="pull-right">' . $delete . '<b>Shop Work:</b> ' . $cabinetModel->name . '/' . $quoteCabinet->color . '</span>';
                    $objItems[] = $this->renderProgress($cabinet);
                    $objItems[] = $cabinet->notes ?: "No Notes Found";

                    $newItems[] = $objItems;
                }
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function renderProgress($item)
    {
        $user = Auth::user();
        // Only allow frugal orders to be able to approve.
        // We have approved - started and completed
        $icons = null;

        // Not approved
        if (!$item->approved && ($user->group_id == 12 || $user->id == 1 || $user->id == 5 || $user->level_id == 1))
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' data-target='#workModal'
                      title='Not Approved' href='/shopitem/$item->id/approved'>
                      <i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
        }
        // Approved
        if ($item->approved)
        {
            $icons .= "<a class='btn btn-success' data-toggle='tooltip' data-target='#workModal'
                      title='Approved' href='#'>
                      <i class=' fa fa-exclamation'></i></a> &nbsp;&nbsp;";
        }

        if ($item->approved && !$item->started) // Approved and not started
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' data-target='#workModal'
                      title='Not Started' href='/shopitem/$item->id/started'>
                      <i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
        }
        if ($item->approved && $item->started)
        {
            $icons .= "<a class='btn btn-success' data-toggle='tooltip' data-target='#workModal'
                      title='Started' href='#'>
                      <i class=' fa fa-gears'></i></a> &nbsp;&nbsp;";
        }

        if ($item->approved && !$item->completed)
        {
            $icons .= "<a class='btn btn-danger' data-toggle='tooltip' data-target='#workModal'
                      title='Not Completed' href='/shopitem/$item->id/completed'>
                      <i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
        }
        if ($item->approved && $item->completed)
        {
            $icons .= "<a class='btn btn-success' data-toggle='tooltip' data-target='#workModal'
                      title='Completed' href='#'>
                      <i class=' fa fa-check'></i></a> &nbsp;&nbsp;";
        }


        return $icons;

    }

    public function getJobItem(Request $request)
    {
        $job_item_id = $request->job_item_id;
        $reference = $request->reference;

        $jobItem = JobItem::find($job_item_id);

        return Response::json(
          [
            'response' => 'success',
            'reference' => $jobItem->reference
          ]
        );
    }

    public function setJobItem(Request $request)
    {
        $job_item_id = $request->job_item_id;
        $reference = $request->reference;
        if($reference == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Reference cannot be empty.'
              ]
            );
        }
        $jobItem = JobItem::find($job_item_id);
        $jobItem->reference = $reference;
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Item updated.'
          ]
        );
    }

    /**
     * Get Group
     * @return json
     */
    public function getGroup(Request $request)
    {
        $jobItem = JobItem::join('groups', 'job_items.group_id', '=', 'groups.id')
                      ->where('job_items.id', $request->job_item_id)
                      ->select('job_items.group_id', 'groups.name')
                      ->first();

        if(!$jobItem)
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
            if(empty($jobItem->name) || is_null($jobItem->name))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            return Response::json(
              [
                'response' => 'success',
                'group_id' => $jobItem->group_id,
                'name' => $jobItem->name
              ]
            );
        }
    }

    /**
     * Set Group
     * @return json
     */
    public function setGroup(Request $request)
    {
        $group_id = $request->group_id;
        $job_item_id = $request->job_item_id;

        if($group_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select group.'
            ]
          );
        }

        $jobItem = JobItem::find($job_item_id);
        if(!$jobItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item not found.'
              ]
            );
        }

        $jobItem->group_id = $group_id;
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Group set.'
          ]
        );
    }

    /**
     * Get Notes
     * @return json
     */
    public function getNotes(Request $request)
    {
        $job_item_id = $request->job_item_id;

        $jobItem = JobItem::find($job_item_id);

        if(!$jobItem)
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
                'notes' => $jobItem->notes
              ]
            );
        }
    }

    /**
     * Set Notes
     * @return json
     */
    public function setNotes(Request $request)
    {
        $job_item_id = $request->job_item_id;
        $notes = $request->notes;

        $jobItem = JobItem::find($job_item_id);
        if(!$jobItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item not found.'
              ]
            );
        }

        $jobItem->notes = $notes;
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Notes set.'
          ]
        );
    }

    /**
     * Get Contractor Notes
     * @return json
     */
    public function getContractorNotes(Request $request)
    {
        $job_item_id = $request->job_item_id;

        $jobItem = JobItem::find($job_item_id);

        if(!$jobItem)
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
                'contractor_notes' => $jobItem->contractor_notes
              ]
            );
        }
    }

    /**
     * Set Contractor Notes
     * @return json
     */
    public function setContractorNotes(Request $request)
    {
        $job_item_id = $request->job_item_id;
        $contractor_notes = $request->contractor_notes;

        $jobItem = JobItem::find($job_item_id);
        if(!$jobItem)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Item not found.'
              ]
            );
        }

        $jobItem->contractor_notes = $contractor_notes;
        $jobItem->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Contractor Notes set.'
          ]
        );
    }


    /**
     * Show an existing punch
     * @param Punch $punch
     * @return mixed
     */
    public function show(Punch $punch)
    {
        return view('admin.punches.create')->withPunch($punch);
    }

    /**
     * Create new punch
     * @return mixed
     */
    public function create()
    {
        return view('admin.punches.create')->withPunch(new Punch);
    }

    /**
     * Store a new punch
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function store(Request $request)
    {
        if (!$request->question || empty($request->question) || !$request->group_id) {
            throw new FrugalException("You must specify a punch question, and a group.");
        }
        $request->merge(['active' => 1]);
        (new Punch)->create($request->all());

        audit($this->auditPage, "Created $request->question as a new punch");
        return ['callback' => "redirect:/admin/punches"];
    }

    /**
     * Update a punch.
     * @param Punch $punch
     * @param Request $request
     * @return array
     * @throws FrugalException
     */
    public function update(Punch $punch, Request $request)
    {
        if (!$request->question || empty($request->question))
            throw new FrugalException("You must specify a punch option question.");
        $punch->update($request->all());

        audit($this->auditPage, "Updated $request->question");
        return ['callback' => "redirect:/admin/punches"];
    }

    /**
     * Activate/Deactivate Lead source
     * @param Punch $punch
     * @return array
     */
    public function destroy(Punch $punch)
    {
        $punch->update(['active' => !$punch->active]);
        $message = (!$punch->active) ? "Deactivated" : "Activated";
        audit($this->auditPage, "$message $punch->question");
        return ['callback' => "redirect:/admin/punches"];
    }
}
