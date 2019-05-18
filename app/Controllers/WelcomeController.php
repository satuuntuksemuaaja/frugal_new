<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
use FK3\Models\User;
use FK3\Models\File;
use FK3\Models\Task;
use FK3\Models\TaskNote;
use FK3\Models\Customer;
use FK3\Models\Location;
use FK3\Models\Group;
use FK3\vl\core\CalendarEngineNew;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Carbon\Carbon;
use Response;
use Storage;
use Auth;

class WelcomeController extends Controller
{
    public $auditPage = "Welcome";

    /*
     * Show Task Index.
     */
    public function welcome(Request $request)
    {
        $locations = Location::where('deleted_at', null)->orderBy('name', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get();
        $groups = Group::orderBy('name', 'asc')->get();

        // Event Gathering
        if ($request->category == 'appointments')
          $events = CalendarEngineNew::byAppointments($request->location);
        else if ($request->category == 'jobs')
          $events = CalendarEngineNew::byJobs();
        else if ($request->group)
          $events = CalendarEngineNew::byGroup($request->group);
        else if ($request->user)
          $events = CalendarEngineNew::byUser($request->user);
        else
        {
            if (Auth::user()->id == 5)
                $events = CalendarEngineNew::byAppointments();
            else
            $events = CalendarEngineNew::byUser(Auth::user()->id);
        }

        $btnLabelCategory = 'Show by Category';
        if($request->category)
        {
            if($request->has('location'))
            {
                $location = Location::find($request->location);
                $btnLabelCategory = 'Show by ' . $location->name . ' Appointments';
            }
            else if($request->category == 'appointments')
            {
                $btnLabelCategory = 'Show by All Appointments';
            }
            else if($request->category == 'jobs')
            {
                $btnLabelCategory = 'Show by Jobs';
            }
        }

        $btnLabelUser = 'Show by User';
        if($request->user)
        {
            $user = User::find($request->user);
            $btnLabelUser = 'Show by ' . $user->name;
        }

        $btnLabelGroup = 'Show By Group';
        if($request->group)
        {
            $group = Group::find($request->group);
            $btnLabelGroup = 'Show by ' . $group->name;
        }

        return view('welcome', compact('locations', 'users', 'groups', 'events', 'request', 'btnLabelCategory', 'btnLabelUser', 'btnLabelGroup'));
    }
}
