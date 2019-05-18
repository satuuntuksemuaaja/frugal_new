<?php
namespace FK3\vl\core;
use Auth;
use \Carbon\Carbon;
use FK3\Models\User;
use FK3\Models\Group; //Designation
use FK3\Models\Location; //Showroom
use FK3\Models\Job;
use FK3\Models\JobSchedule;
use FK3\Models\Quote;
use FK3\Models\Lead;
use FK3\Models\Customer;
use FK3\Models\Fft;
use FK3\Models\Task;
//Measure; changed to Lead->digital_scheduled

class CalendarEngineNew
{

  const FORMAT = "events: [%s],";

  static public function byUser($user)
  {
      if (Auth::user()->group_id == 20)
          $user = Auth::user()->id;
    $events = [];
    $userObj = User::find($user);
    switch ($userObj->group_id)
    {
      case 1: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 2: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 3: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 4: $schedules = JobSchedule::whereUserId($user)->get(); break;
      case 5: $schedules = Fft::whereUserId($user)->get(); break;
      default : null;
    }
    if (isset($schedules))
    {
      foreach ($schedules AS $schedule)
      {
        $job = Job::find($schedule->job_id);
        if(!$job) continue;

        $quote = Quote::find($job->quote_id);
        if(!$quote) continue;

        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $user = User::find($schedule->user_id);
        if ($job && $quote && $user)
        $events[] = [
          'title' => $user->name . " for " .
             @$customer->name,
          'time' => ($userObj->group_id == 5) ? $schedule->start : $schedule->start,
          'color' => '', //$schedule->user->color,
          'url' => "/job/{$schedule->job->id}/schedules"
        ];
      } // fe
      // #30 - Add tasks to calendar
    } // if scheules
    else
      $events = self::byAppointments(null, $user);
    $tasks = Task::whereAssignedId($user)->get();
      foreach ($tasks AS $task)
      {
        if ($task->due != '0000-00-00')
        $events[] = [
          'title' => $task->subject,
          'time' => $task->due,
          'color' => '', //$task->user->color,
          'url' => "/task/{$task->id}/view"
        ];
      }
    return self::render($events);

  }

  static public function render(Array $events)
  {
    $data = null;
    foreach ($events AS $event)
    {
      //$time = strtotime($event['time']);
      $time = Carbon::parse($event['time'])->format("m/d/y H:i");
      $end = Carbon::parse($event['time'])->addMinutes(30)->format("m/d/y H:i");
      $title = $event['title'];
      $title = str_replace("'", null, $title);
      $title = str_replace('"', null, $title);
      $url = (isset($event['url'])) ? "url: '$event[url]', " : null;
      if (!isset($event['color']))
        $event['color'] = '#5bc0de';
      else $event['color'] = '#' . $event['color'];
      $data .= "{
                  title: '$title',
                  start: '{$time}:00',
                  end: '{$end}:00',
                  backgroundColor: '$event[color]',
                  {$url}
                },

                ";
    }
    return sprintf(self::FORMAT, $data);
  }

  static public function byGroup ($group)
  {
      if (Auth::user()->level_id == 4)
          return null;
    $events = [];
    if ($group == 20 || $group == 22) // Get FFTs
    $schedules = Fft::all();
    else
    $schedules = JobSchedule::whereGroupId($group)->get();
    foreach ($schedules AS $schedule)
      {
        $job = Job::find($schedule->job_id);
        if(!$job) continue;

        $quote = Quote::find($job->quote_id);
        if(!$quote) continue;

        $lead = Lead::find($quote->lead_id);
        $customer = Customer::find($lead->customer_id);
        $user = User::find($schedule->user_id);
        if ($job && $quote && $user)
        $events[] = [
          'title' => $user->name . " for " .
             @$customer->name,
          'time' => ($group == 20) ? $schedule->schedule_start : $schedule->start,
          'color' => '', //$schedule->user->color,
          'url' => "/job/{$job->id}/schedules"
        ];
      } // fe
    return self::render($events);
  }

  static public function byAppointments($location_id = null, $user = null)
  {
    $start = Carbon::now()->startOfMonth();

    $leadShowrooms = Lead::where('showroom_scheduled', '>=', $start);
    if($location_id) $leadShowrooms = $leadShowrooms->where('showroom_location_id', $location_id);
    $leadShowrooms = $leadShowrooms->get();

    $leadClosings = Lead::where('closing_scheduled', '>=', $start)->get();

    $leadDigitals = Lead::where('digital_scheduled', '>=', $start)->get();

    $events = [];
    foreach ($leadShowrooms AS $leadShowroom)
      {
        $userShowroom = User::find($leadShowroom->showroom_user_id);
        if (!$user || !$userShowroom) continue;
        if ($user && $userShowroom->id != $user) continue;
        $customer = Customer::find($leadShowroom->customer_id);
        $location = Location::find($leadShowroom->showroom_location_id);
        $events[] = [
        'title' => "(S) ".$customer->name . " in ". $location->name,
        'time' => $leadShowroom->showroom_scheduled,
        'color' => '', //$showroom->lead->user->color,
        'url' => "/profile/{$customer->id}/view"
        ];
      }
    foreach ($leadClosings AS $leadClosing)
      {
        $userClosing = User::find($leadClosing->closing_user_id);
        if (!$userClosing) continue;
        if ($user && $userClosing->closing_user_id != $user) continue;
        $customer = Customer::find($leadClosing->customer_id);
        $location = Location::find($leadClosing->closing_location_id);
        $events[] = [
        'title' => "(C) ".$customer->name,
        'time' => $leadClosing->closing_scheduled,
        'color' => '', //$closing->lead->user->color,
        'url' => "/profile/{$customer->id}/view"];
      }
     foreach ($leadDigitals AS $leadDigital)
      {
        $userDigital = User::find($leadClosing->digital_user_id);
        if (!$userDigital) continue;
        if ($user && $userDigital->digital_user_id != $user) continue;
        $customer = Customer::find($leadClosing->customer_id);
        $location = Location::find($leadClosing->closing_location_id);
        $events[] = [
        'title' => "(M) ".$customer->name,
        'time' => $leadDigital->digital_user_id,
        'color' => '', //$measure->lead->user->color,
        'url' => "/profile/{$customer->id}/view"];
      }
    if ($user) return $events;
    return self::render($events);
  }


  static public function byJobs($jobs = null)
  {
    $events = [];
    if (!$jobs)
      $jobs = Job::whereClosed(false)->get();
    foreach ($jobs AS $job)
    {
      foreach ($job->schedules AS $schedule)
      {
          if (Auth::user()->group_id == 20 && $schedule->user_id != Auth::user()->id)
              continue;

              $events[] = [
          'title' => $schedule->user->name . " for " . $job->quote->lead->customer->name,
          'time' => $schedule->start,
          'color' => '', //$schedule->user->color,
          'url' => "/job/{$job->id}/schedules"
        ];
      } // fe
    } //fe
    return self::render($events);

  } //class


}
