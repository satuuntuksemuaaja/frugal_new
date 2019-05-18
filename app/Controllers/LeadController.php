<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/22/18
 * Time: 4:35 PM
 */

namespace FK3\Controllers;

use FK3\Models\User;
use FK3\Models\Contact;
use FK3\Models\Lead;
use FK3\Models\LeadSource;
use FK3\Models\LeadNote;
use FK3\Models\Location;
use FK3\Models\Customer;
use FK3\Models\Status;
use FK3\Models\Followup;
use FK3\Models\Quote;
use FK3\Models\QuoteType;
use FK3\Models\Traits\DataTrait;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Auth;

class LeadController extends Controller
{
    use DataTrait;

    /**
     * Show Leads Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->ajax())
        {
            // We're formatting a table.
            //return $this->renderTable($request);
        }

        $locations = Location::orderBy('name')->get();
        $designers = User::where('group_id', '20')->orderBy('name')->get();
        $showroomUsers = User::where('group_id', '23')->orderBy('name')->get();
        $digitalUsers = User::where('group_id', '14')->orderBy('name')->get();
        $closingUsers = User::where('group_id', '5')->orderBy('name')->get();
        $statuses = Status::orderBy('name')->get();
        $quoteTypes = QuoteType::orderBy('name')->where('active', 1)->get();
        $leadSources = LeadSource::orderBy('name')->where('active', 1)->get();

        return view('leads.index', compact('locations', 'designers', 'showroomUsers', 'digitalUsers', 'closingUsers', 'statuses', 'quoteTypes', 'leadSources'));
    }

    /**
     * Display Leads Data
     * @return Array
     */

    public function displayLeads()
    {
        $items = Lead::join('customers', 'leads.customer_id', '=', 'customers.id')
                      ->leftJoin('lead_sources', 'lead_sources.id', '=', 'leads.source_id')
                      ->leftJoin('users', 'leads.user_id', '=', 'users.id')
                      ->leftJoin('locations', 'leads.showroom_location_id', '=', 'locations.id')
                      ->leftJoin('users as showroomUser', 'leads.showroom_user_id', '=', 'showroomUser.id')
                      ->leftJoin('users as digitalUser', 'leads.digital_user_id', '=', 'digitalUser.id')
                      ->leftJoin('users as closingUser', 'leads.closing_user_id', '=', 'closingUser.id')
                      ->leftJoin('statuses', 'leads.status_id', '=', 'statuses.id')
                      ->leftJoin('users as statusUser', 'leads.last_status_by', '=', 'statusUser.id')
                      ->select(
                                'customers.id as cust_id',
                                'customers.name as cust_name',
                                'leads.id',
                                'leads.user_id',
                                'leads.created_at',
                                'leads.status_id',
                                'lead_sources.name as lead_sources_name',
                                'leads.showroom_scheduled',
                                'leads.showroom_user_id',
                                'showroomUser.name as showroom_user_name',
                                'locations.name as location',
                                'users.name as designer_name',
                                'leads.digital_scheduled',
                                'leads.digital_user_id',
                                'digitalUser.name as digital_user_name',
                                'leads.closing_scheduled',
                                'leads.closing_user_id',
                                'closingUser.name as closing_user_name',
                                'statuses.name as status_name',
                                'statusUser.name as last_status_name'
                              )
                      ->where('leads.archived', '0')
                      ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();
              $objItems[] = '<a href="' .  route('view_profile', ['id' => $item->cust_id]) . '">' . $item->cust_name . '</a><a data-toggle="tooltip" title="Archive" href="#" style="float:right;" onclick="ShowModalArchiveConfirm(' . $item->id . ')"><i class="fa fa-eraser"></i></a> <a data-toggle="tooltip" title="Notes" href="#" style="float:right;" onclick="ShowModalNotes(' . $item->id . ')"><i class="fa fa-edit"></i></a>';
              $objItems[] = Carbon::parse($item->created_at)->age;

              $lastStatusName = '';
              if($item->last_status_name != '') $lastStatusName = ' <i>(' . $item->last_status_name . ')</i>';

              $statusName = '--status not set--';
              if($item->status_name != '') $statusName = $item->status_name;

              $objItems[] = '<a data-toggle="tooltip" href="#" onclick="ShowModalSetStatus(' . $item->id . ')"><span id="span_status_' . $item->id . '">' . $statusName . '</span></a> ' . $lastStatusName . '<a data-toggle="tooltip" title="Follow Ups" href="#" style="float:right;" onclick="ShowModalFollowUp(' . $item->id . ',\'' . $item->cust_name . '\')"><i class="fa fa-phone"></i></a> <a data-toggle="tooltip" title="Spawn Quote from Lead" href="#" style="float:right;" onclick="ShowModalQuote(' . $item->id . ',\'' . $item->cust_name . '\')"><i class="fa fa-arrow-right"></i></a>';

              $objItems[] = '<a data-toggle="tooltip" href="#" onclick="ShowModalSetSource(' . $item->id . ')">' . $item->lead_sources_name . '</a>';

              $showroom_scheduled = '--date time not set--';
              if($item->showroom_scheduled != null) $showroom_scheduled = $item->showroom_scheduled;

              $location = '--location not set--';
              if($item->location != null) $location = $item->location;

              $showroom_user = '--user not set--';
              if($item->showroom_user_id != '0') $showroom_user = $item->showroom_user_name;

              $objItems[] = '<a href="#" onclick="ShowModalSetShowroomSchedule(' . $item->id . ', \'' . $item->showroom_scheduled . '\')">' . $showroom_scheduled . '</a> in <a href="#" onclick="ShowModalSetShowroomLocation(' . $item->id . ', \'' . $item->location . '\')">' . $location . '</a> (<a href="#" onclick="ShowModalSetShowroomUser(' . $item->id . ', \'' . $item->showroom_user_id . '\')">' . $showroom_user . '</a>)';

              $closing_scheduled = '--schedule not set--';
              if($item->closing_scheduled != null) $closing_scheduled = $item->closing_scheduled;

              $closing_user_name = '--closer not set--';
              if($item->closing_user_id != 0) $closing_user_name = $item->closing_user_name;

              $objItems[] = '<a href="#" onclick="ShowModalSetClosingSchedule(' . $item->id . ', \'' . $item->closing_scheduled . '\')">' . $closing_scheduled . '</a> (<a href="#" onclick="ShowModalSetClosingUser(' . $item->id . ', \'' . $item->closing_user_id . '\')">' . $closing_user_name . '</a>)';

              $digital_scheduled = '--schedule not set--';
              if($item->digital_scheduled != null) $digital_scheduled = $item->digital_scheduled;

              $digital_user_name = '--measurer not set--';
              if($item->digital_user_id != 0) $digital_user_name = $item->digital_user_name;

              $objItems[] = '<a href="#" onclick="ShowModalSetDigitalSchedule(' . $item->id . ', \'' . $item->digital_scheduled . '\')">' . $digital_scheduled . '</a> (<a href="#" onclick="ShowModalSetDigitalUser(' . $item->id . ', \'' . $item->digital_user_id . '\')">' . $digital_user_name . '</a>)';

              $designerName = '--no designer assigned--';
              if($item->designer_name != '') $designerName = $item->designer_name;
              $objItems[] = '<a href="#" onclick="ShowModalSetDesigner(' . $item->id . ', \'' . $item->user_id . '\')">' . $designerName . '</a>';

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Lead::where('archived', '0')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Show Add Leads Page
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $customers = Customer::where('active', '1')
                              ->orderBy('name', 'asc')
                              ->get();

        $leadSources = LeadSource::where('active', '1')
                                  ->orderBy('name', 'asc')
                                  ->get();

        $designers = User::where('group_id', '20')
                          ->orderBy('name', 'asc')
                          ->get();

        $locations = Location::all();

        return view('leads.create', compact('customers', 'leadSources', 'designers', 'locations'));
    }

    /**
     * Store Leads
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function store(Request $request)
    {
        $lead = new Lead();

        if ($request->customer_id != '')
        {
            $lead->customer_id = $request->customer_id;
            $this->createSecondaryLead($request);
        }
        else
        {
            $customer = new Customer;
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->address = $request->address;
            $customer->city = $request->city;
            $customer->state = $request->state;
            $customer->zip = $request->zip;
            $customer->job_address = $request->job_address;
            $customer->job_city = $request->job_city;
            $customer->job_state = $request->job_state;
            $customer->job_zip = $request->job_zip;
            $customer->save();

            $contact = new Contact;
            $contact->customer_id = $customer->id;
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->mobile = preg_replace('/\D/', null, $request->mobile);
            $contact->home = preg_replace('/\D/', null, $request->phone);
            $contact->alternate = preg_replace('/\D/', null, $request->alternate);
            $contact->primary = 1;
            $contact->save();

            $lead->customer_id = $customer->id;
        }

        $lead->source_id = $request->source_id;
        $lead->user_id = $request->user_id;
        $lead->provided = '0';
        $lead->showroom_location_id = $request->location_id;
        $lead->save();

        return redirect(route('leads.index'))->with('success', 'Lead added!');
    }

    public function createSecondaryLead($request)
    {
        $oldlead = Lead::where('customer_id', $request->customer_id)->first();
        $oldcustomer = Customer::find($request->customer_id);
        $oldcontact = Contact::where('customer_id', $request->customer_id)->first();

        if ($request->name != '')
        {
            $lead = new Lead();
            $lead->customer_id = $request->customer_id;
            $lead->provided = 0;

            if($request->source_id != '') $lead->source_id = $request->source_id;
            if($request->user_id != '') $lead->user_id = $request->user_id;
            $lead->save();
        }
        else
        {
            $c = new Customer;
            $c->email = $request->email;
            $c->name = $request->name;
            $c->address = $oldcustomer->address;
            $c->city = $oldcustomer->city;
            $c->state = $oldcustomer->state;
            $c->zip = $oldcustomer->zip;
            $c->job_address = $oldcustomer->job_address;
            $c->job_city = $oldcustomer->job_city;
            $c->job_state = $oldcustomer->job_state;
            $c->job_zip = $oldcustomer->job_zip;
            $c->save();

            $co = new Contact;
            $co->customer_id = $c->id;
            $co->name = $oldcontact->name;
            $co->email = $oldcontact->email;
            $co->mobile = $oldcontact->mobile;
            $co->home = $oldcontact->home;
            $co->alternate = $oldcontact->alternate;
            $co->primary = 1;
            $co->save();

            $lead = new Lead();
            $lead->customer_id = $c->id;
            if($request->source_id != '') $lead->source_id = $request->source_id;
            if($request->user_id != '') $lead->user_id = $request->user_id;
            $lead->provided = '0';
            $lead->save();
        }
    }

    /**
     * Get Showroom Schedule
     * @return json
     */
    public function getShowroomSchedule(Request $request)
    {
        $lead = Lead::find($request->lead_id);
        if(!$lead)
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
            if(empty($lead->showroom_scheduled) || is_null($lead->showroom_scheduled))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($lead->showroom_scheduled)->format('m/d/Y');
            $timeFormat = Carbon::parse($lead->showroom_scheduled)->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    /**
     * Set Showroom Schedule
     * @return json
     */
    public function setShowroomSchedule(Request $request)
    {
        $date = $request->date;
        $time = $request->time;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
        }
        if($time == '')
        {
            if($messageError != '') $messageError .= ', Time ';
            else $messageError = 'Time ';
        }
        if($messageError != '')
        {
            $messageError .= 'cannot empty.';
            return Response::json(
              [
                'response' => 'error',
                'message' => $messageError
              ]
            );
        }

        $dateFormat = Carbon::parse($date)->format('Y-m-d');
        $timeFormat = Carbon::parse($time)->format('H:i:s');

        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $lead->showroom_scheduled = $dateFormat . ' ' . $timeFormat;
        $lead->save();

        if($lead->showroom_user_id != 0 && $lead->showroom_scheduled != null && $lead->showroom_location_id != null)
        {
            // Create a Google Calendar Event
            try
            {
                $params = [];
                $params['title'] = "Lead #{$lead->id} in frugalk.com";
                $params['location'] = 'Showroom: ' . $lead->showroom_location->name . ', Address: ' . $lead->showroom_location->address . ', City: ' . $lead->showroom_location->city . ', State: ' . $lead->showroom_location->state . ', Number: ' . $lead->showroom_location->number;
                $params['description'] = "Showroom Scheduled";
                $params['start'] = Carbon::parse($lead->showroom_scheduled);
                $params['end'] = Carbon::parse($lead->showroom_scheduled)->addMinutes(30);
                $eventId = \FK3\vl\core\Google::event(User::find($lead->showroom_user_id), $params);
                //dd($eventId);
            }
            catch (\Exception $e)
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => $e
                  ]
                );
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    /**
     * Get Showroom Location
     * @return json
     */
    public function getShowroomLocation(Request $request)
    {
        $lead = Lead::find($request->lead_id);
        if(!$lead)
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
            if(empty($lead->showroom_location_id) || is_null($lead->showroom_location_id))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $showroomLocation = Location::find($lead->showroom_location_id);

            if(!$showroomLocation)
            {
              return Response::json(
                [
                  'response' => 'error',
                  'message' => 'No Data'
                ]
              );
            }

            if($lead->showroom_user_id != 0 && $lead->showroom_scheduled != null && $lead->showroom_location_id != null)
            {
                // Create a Google Calendar Event
                try
                {
                    $params = [];
                    $params['title'] = "Lead #{$lead->id} in frugalk.com";
                    $params['location'] = 'Showroom: ' . $lead->showroom_location->name . ', Address: ' . $lead->showroom_location->address . ', City: ' . $lead->showroom_location->city . ', State: ' . $lead->showroom_location->state . ', Number: ' . $lead->showroom_location->number;
                    $params['description'] = "Showroom Scheduled";
                    $params['start'] = Carbon::parse($lead->showroom_scheduled);
                    $params['end'] = Carbon::parse($lead->showroom_scheduled)->addMinutes(30);
                    $eventId = \FK3\vl\core\Google::event(User::find($lead->showroom_user_id), $params);
                    //dd($eventId);
                }
                catch (\Exception $e)
                {
                    return Response::json(
                      [
                        'response' => 'error',
                        'message' => $e
                      ]
                    );
                }
            }

            return Response::json(
              [
                'response' => 'success',
                'showroom_location_id'  => $showroomLocation->id,
                'name' => $showroomLocation->name
              ]
            );
        }
    }

    /**
     * Set Showroom Location
     * @return json
     */
    public function setShowroomLocation(Request $request)
    {
        $showroomLocation = $request->showroom_location_id;

        if($showroomLocation == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select Showroom Location.'
            ]
          );
        }

        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data.'
              ]
            );
        }

        $lead->showroom_location_id = $request->showroom_location_id;
        $lead->save();

        if($lead->showroom_user_id != 0 && $lead->showroom_scheduled != null && $lead->showroom_location_id != null)
        {
            // Create a Google Calendar Event
            try
            {
                $params = [];
                $params['title'] = "Lead #{$lead->id} in frugalk.com";
                $params['location'] = 'Showroom: ' . $lead->showroom_location->name . ', Address: ' . $lead->showroom_location->address . ', City: ' . $lead->showroom_location->city . ', State: ' . $lead->showroom_location->state . ', Number: ' . $lead->showroom_location->number;
                $params['description'] = "Showroom Scheduled";
                $params['start'] = Carbon::parse($lead->showroom_scheduled);
                $params['end'] = Carbon::parse($lead->showroom_scheduled)->addMinutes(30);
                $eventId = \FK3\vl\core\Google::event(User::find($lead->showroom_user_id), $params);
                //dd($eventId);
            }
            catch (\Exception $e)
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => $e
                  ]
                );
            }
        }

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Showroom Location set.'
          ]
        );
    }

    /**
     * Get Showroom User
     * @return json
     */
    public function getShowroomUser(Request $request)
    {
        $lead = Lead::join('users', 'leads.showroom_user_id', '=', 'users.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.showroom_user_id', 'users.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->name) || is_null($lead->name))
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
                'showroom_user_id' => $lead->showroom_user_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Showroom User
     * @return json
     */
    public function setShowroomUser(Request $request)
    {
        $showroom_user_id = $request->showroom_user_id;

        if($showroom_user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select showroom user.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->showroom_user_id = $showroom_user_id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Showroom User set.'
          ]
        );
    }

    /**
     * Get Designer
     * @return json
     */
    public function getDesigner(Request $request)
    {
        $lead = Lead::join('users', 'leads.user_id', '=', 'users.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.user_id', 'users.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->name) || is_null($lead->name))
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
                'user_id' => $lead->user_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Designer
     * @return json
     */
    public function setDesigner(Request $request)
    {
        $user_id = $request->user_id;

        if($user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select designer.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->user_id = $user_id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Designer set.'
          ]
        );
    }

    /**
     * Get Digital Schedule
     * @return json
     */
    public function getDigitalSchedule(Request $request)
    {
        $lead = Lead::find($request->lead_id);
        if(!$lead)
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
            if(empty($lead->digital_scheduled) || is_null($lead->digital_scheduled))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($lead->digital_scheduled)->format('m/d/Y');
            $timeFormat = Carbon::parse($lead->digital_scheduled)->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    /**
     * Set Digital Schedule
     * @return json
     */
    public function setDigitalSchedule(Request $request)
    {
        $date = $request->date;
        $time = $request->time;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
        }
        if($time == '')
        {
            if($messageError != '') $messageError .= ', Time ';
            else $messageError = 'Time ';
        }
        if($messageError != '')
        {
            $messageError .= 'cannot empty.';
            return Response::json(
              [
                'response' => 'error',
                'message' => $messageError
              ]
            );
        }

        $dateFormat = Carbon::parse($date)->format('Y-m-d');
        $timeFormat = Carbon::parse($time)->format('H:i:s');

        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $lead->digital_scheduled = $dateFormat . ' ' . $timeFormat;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    /**
     * Get Digital User
     * @return json
     */
    public function getDigitalUser(Request $request)
    {
        $lead = Lead::join('users', 'leads.digital_user_id', '=', 'users.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.digital_user_id', 'users.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->name) || is_null($lead->name))
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
                'digital_user_id' => $lead->digital_user_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Digital User
     * @return json
     */
    public function setDigitalUser(Request $request)
    {
        $digital_user_id = $request->digital_user_id;

        if($digital_user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select digital user.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->digital_user_id = $digital_user_id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Digital User set.'
          ]
        );
    }

    /**
     * Get Closing Schedule
     * @return json
     */
    public function getClosingSchedule(Request $request)
    {
        $lead = Lead::find($request->lead_id);
        if(!$lead)
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
            if(empty($lead->closing_scheduled) || is_null($lead->closing_scheduled))
            {
                return Response::json(
                  [
                    'response' => 'error',
                    'message' => 'No Data'
                  ]
                );
            }

            $dateFormat = Carbon::parse($lead->closing_scheduled)->format('m/d/Y');
            $timeFormat = Carbon::parse($lead->closing_scheduled)->format('h:i A');

            return Response::json(
              [
                'response' => 'success',
                'date' => $dateFormat,
                'time' => $timeFormat
              ]
            );
        }
    }

    /**
     * Set Closing Schedule
     * @return json
     */
    public function setClosingSchedule(Request $request)
    {
        $date = $request->date;
        $time = $request->time;

        $messageError = '';
        if($date == '')
        {
            $messageError = 'Date';
        }
        if($time == '')
        {
            if($messageError != '') $messageError .= ', Time ';
            else $messageError = 'Time ';
        }
        if($messageError != '')
        {
            $messageError .= 'cannot empty.';
            return Response::json(
              [
                'response' => 'error',
                'message' => $messageError
              ]
            );
        }

        $dateFormat = Carbon::parse($date)->format('Y-m-d');
        $timeFormat = Carbon::parse($time)->format('H:i:s');

        $lead = Lead::find($request->lead_id);
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'No Data'
              ]
            );
        }
        $lead->closing_scheduled = $dateFormat . ' ' . $timeFormat;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Schedule set.'
          ]
        );
    }

    /**
     * Get Closing User
     * @return json
     */
    public function getClosingUser(Request $request)
    {
        $lead = Lead::join('users', 'leads.closing_user_id', '=', 'users.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.closing_user_id', 'users.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->name) || is_null($lead->name))
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
                'closing_user_id' => $lead->closing_user_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Closing User
     * @return json
     */
    public function setClosingUser(Request $request)
    {
        $closing_user_id = $request->closing_user_id;

        if($closing_user_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select Closing user.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->closing_user_id = $closing_user_id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Closing User set.'
          ]
        );
    }

    /**
     * Get Status
     * @return json
     */
    public function getStatus(Request $request)
    {
        $lead = Lead::join('statuses', 'leads.status_id', '=', 'statuses.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.status_id', 'statuses.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->status_id) || is_null($lead->status_id))
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
                'status_id' => $lead->status_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Status
     * @return json
     */
    public function setStatus(Request $request)
    {
        $status_id = $request->status_id;

        if($status_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select Status.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->status_id = $status_id;
        $lead->last_status_by = Auth::user()->id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status set.',
            'data' => $lead->status->name
          ]
        );
    }

    /**
     * Get Source
     * @return json
     */
    public function getSource(Request $request)
    {
        $lead = Lead::join('lead_sources', 'leads.source_id', '=', 'lead_sources.id')
                      ->where('leads.id', $request->lead_id)
                      ->select('leads.source_id', 'lead_sources.name')
                      ->first();

        if(!$lead)
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
            if(empty($lead->source_id) || is_null($lead->source_id))
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
                'source_id' => $lead->source_id,
                'name' => $lead->name
              ]
            );
        }
    }

    /**
     * Set Source
     * @return json
     */
    public function setSource(Request $request)
    {
        $source_id = $request->source_id;

        if($source_id == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please select Source.'
            ]
          );
        }

        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $lead->source_id = $source_id;
        $lead->lead_source_id = Auth::user()->id;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Source set.'
          ]
        );
    }

    /**
     * Set Archive Lead
     * @return json
     */
    public function setArchived(Request $request)
    {
        $lead = Lead::where('id', $request->lead_id)->first();
        if(!$lead)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Lead not found.'
              ]
            );
        }
        $archived = '1';
        if($lead->archived == '1') $archived = '0';
        else if($lead->archived == '0') $archived = '1';

        $lead->archived = $archived;
        $lead->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Lead Archive set.'
          ]
        );
    }

    public function displayLeadNotes(Request $request)
    {
        $lead_id = $request->lead_id;

        $items = LeadNote::leftJoin('users', 'lead_notes.user_id', '=', 'users.id')
                          ->where('lead_notes.lead_id', $lead_id)
                          ->where('deleted_at', null)
                          ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = Carbon::parse($item->created_at)->format('m/d/Y H:i:s');
              $objItems[] = $item->name;
              $objItems[] = $item->note;

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = LeadNote::where('lead_id', $lead_id)
                          ->where('deleted_at', null)
                          ->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    public function saveLeadNotes(Request $request)
    {
        $lead_id = $request->lead_id;
        $note = $request->notes;

        if($note == '')
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Please fill notes field.'
            ]
          );
        }

        $leadNote = new LeadNote();
        $leadNote->lead_id = $lead_id;
        $leadNote->note = $note;
        $leadNote->user_id = Auth::user()->id;
        $leadNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Note Added.'
          ]
        );
    }

    public function displayLeadFollowUp(Request $request)
    {
        $lead_id = $request->lead_id;

        $items = Followup::leftJoin('users', 'followups.user_id', '=', 'users.id')
                          ->leftJoin('statuses', 'followups.status_id', '=', 'statuses.id')
                          ->select(
                                    'followups.id',
                                    'followups.created_at',
                                    'statuses.name as status_name',
                                    'users.name as by_user_name',
                                    'followups.comments'
                                  )
                          ->where('followups.lead_id', $lead_id)
                          ->where('followups.closed', '0')
                          ->where('followups.deleted_at', null)
                          ->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = Carbon::parse($item->created_at)->format('m/d/Y H:i:s');
              $objItems[] = $item->status_name . '<a data-toggle="tooltip" title="Close" href="#" style="float:right;" onclick="SetCloseFollowup(' . $item->id . ')"><i class="fa fa-times-circle-o"></i></a>';
              $objItems[] = $item->by_user_name;
              $objItems[] = $item->comments;

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = LeadNote::where('deleted_at', null)->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Set Close Followup
     * @return json
     */
    public function setCloseFollowup(Request $request)
    {
        $followup = Followup::find($request->followup_id);
        if(!$followup)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Follow Up not found.'
              ]
            );
        }
        $closed = '1';
        if($followup->closed == 1) $closed = 0;
        else if($followup->closed == 0) $closed = 1;

        $followup->closed = $closed;
        $followup->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Follow Up closed set.',
            'lead_id' => $followup->lead_id
          ]
        );
    }

    /**
     * Save Lead Quote
     * @return json
     */
    public function saveLeadQuote(Request $request)
    {
        $lead_id = $request->lead_id;
        $quote_type_id = $request->quote_type_id;

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
            'message' => 'Lead Quote Added.',
            'quote_id' => $quote->id
          ]
        );
    }
}
