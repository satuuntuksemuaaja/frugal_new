<?php
namespace FK3\Controllers;

use FK3\Exceptions\FrugalException;
use FK3\Models\Lead;
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
use Mail;

class TaskController extends Controller
{
    public $auditPage = "Task";

    /*
     * Show Task Index.
     */
    public function index(Request $request)
    {
        self::sendDueDateNotification();

        $users = User::where('active', '1')->orderBy('name')->get();
        $customers  = Customer::where('active', '1')->orderBy('name')->get();
        $userGroups  = User::join('groups', 'users.group_id', '=', 'groups.id')
                            ->where('users.active', '1')
                            ->select('users.id', 'users.name', 'groups.name as group_name')
                            ->orderBy('name')
                            ->get();

        return view('task.index', compact('users', 'customers', 'userGroups'));
    }

    /**
     * Display Task Data
     * @return Array
     */
    public function displayTasks()
    {
        $items = Task::leftJoin('users as userAssigned', 'tasks.assigned_id', '=', 'userAssigned.id')
                      ->leftJoin('customers', 'tasks.customer_id', '=', 'customers.id')
                      ->leftJoin('users as userJob', 'tasks.job_id', '=', 'userJob.id')
                      ->leftJoin('users as userCreated', 'tasks.user_id', '=', 'userCreated.id');

        $admin = false;
        if(Auth::user()->superuser == '1' || Auth::user()->manager == '1')
        {
            $admin = true;
        }
        if(!$admin)
        {
            $items = $items->where('tasks.assigned_id', Auth::user()->id);
        }
        $items = $items->where('tasks.closed', '0')
                      ->select(
                                'tasks.id',
                                'tasks.subject',
                                'userAssigned.name as user_assigned_name',
                                'customers.name as cust_name',
                                'userJob.id as user_job_id',
                                'tasks.created_at',
                                'userCreated.name as user_created_name',
                                'tasks.updated_at',
                                'tasks.due'
                              )
                      ->where('tasks.closed', '0');

        // Get Total
        $total = $items->count();

        $items = $items->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = $item->id;
              $objItems[] = '<a href="#" onclick="ShowModalTaskNotes(\'' . $item->id . '\', \'' . $item->subject . '\');">' . $item->subject . '</a>';
              $objItems[] = $item->user_assigned_name;

              $cust_name = '-- no customer --';
              if($item->cust_name != '') $cust_name = $item->cust_name;
              $objItems[] = $cust_name;

              $user_job_name = '-- no job --';
              if($item->user_job_id > 0 || $item->user_job_id != '')
              {
                  $userGroup = User::leftJoin('groups', 'users.group_id', '=', 'groups.id')->first();
                  if($userGroup) $user_job_name = $userGroup->name;
              }
              $objItems[] = $user_job_name;
              $objItems[] = Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
              $objItems[] = $item->user_created_name;
              $objItems[] = Carbon::parse($item->updated_at)->format('Y-m-d H:i:s');
              $objItems[] = $item->due;

              $newItems[] = $objItems;
            }
        }

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Show an existing task
     * @param Task $task
     * @return mixed
     */
    public function show(Task $task)
    {

    }

    /**
     * Create new task
     * @return mixed
     */
    public function create()
    {
        return view('task.create');
    }

    /**
     * Store a new task
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
    public function update(Task $task, Request $request)
    {

    }

    public function SaveTask(Request $request)
    {
        $subject = $request->subject;
        $due_date = $request->due_date;
        $due_time = $request->due_time;
        $urgent = $request->urgent;
        $assigned_id = $request->assigned_id;
        $customer_id = $request->customer_id;
        $job_id = $request->job_id;
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

        $task = new Task;
        $task->user_id = Auth::user()->id;
        $task->assigned_id = $assigned_id;
        $task->subject = $subject;
        $task->body = $body;
        $task->job_id = $job_id;
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

    public function getTaskNote(Request $request)
    {
        $task_id = $request->task_id;

        $task = Task::find($task_id);
        if(!$task)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'No task found.'
            ]
          );
        }

        $due = '<b>* This task has no due date yet.</b>';
        if($task->due != null || $task->due != '') $due = '<b>* This task is due on ' . Carbon::parse($task->due)->format('Y-m-d H:i') . '</b>';
        $body = '<pre>' . $task->body . '</pre>' . $due;

        $taskNotes = TaskNote::leftJoin('users', 'task_notes.user_id', '=', 'users.id')
                              ->where('task_notes.task_id', $task->id)
                              ->select('task_notes.body', 'users.name', 'task_notes.created_at')
                              ->orderBy('task_notes.created_at', 'dsc')
                              ->get();
        $notes = '';

        foreach($taskNotes as $taskNote)
        {
            $notes .= '<div class="form-group form-row ">
                        <div class="card col-md-12">
                          <div class="panel panel-primary">
                            <div class="panel-heading">
                              <span class="panel-title">
                                <b>' . $taskNote->body . '</b>
                              </span>
                            </div>
                            <div class="panel-body">
                              Added by ' . $taskNote->name . ' on ' . Carbon::parse($taskNote->created_at)->format('Y-m-d H:i:s') . '
                           </div>
                        </div>
                      </div>
                    </div>
                    ';
        }

        return Response::json(
          [
            'response' => 'success',
            'body' => $body,
            'notes' => $notes
          ]
        );
    }

    public function SaveTaskNote(Request $request)
    {
        $task_id = $request->task_id;
        $body = $request->body;

        if($task_id == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Task ID required.'
              ]
            );
        }

        if($body == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please fill the note.'
              ]
            );
        }

        $taskNote = new TaskNote();
        $taskNote->task_id = $task_id;
        $taskNote->user_id = Auth::user()->id;
        $taskNote->body = $body;
        $taskNote->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Task Note added.'
          ]
        );
    }

    public function closeTask(Request $request)
    {
      $task_id = $request->task_id;

      if($task_id == '')
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Task ID required.'
            ]
          );
      }

      $task = Task::find($task_id);
      if(!$task)
      {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Task not found.'
            ]
          );
      }
      $closed = '0';
      if($task->closed == '0') $closed = '1';

      $task->closed = $closed;
      $task->save();

      return Response::json(
        [
          'response' => 'success',
          'message' => 'Task closed.'
        ]
      );
    }

    public static function sendDueDateNotification()
    {
        $recipients = Task::leftJoin('users as userAssigned', 'tasks.assigned_id', '=', 'userAssigned.id')
                            ->where('tasks.due', '>', Carbon::now())
                            ->where('tasks.closed', '0')
                            ->groupBy('tasks.assigned_id')
                            ->get();

        foreach($recipients as $recipient);
        {
            $items = Task::leftJoin('users as userAssigned', 'tasks.assigned_id', '=', 'userAssigned.id')
                          ->leftJoin('customers', 'tasks.customer_id', '=', 'customers.id')
                          ->leftJoin('users as userJob', 'tasks.job_id', '=', 'userJob.id')
                          ->leftJoin('users as userCreated', 'tasks.user_id', '=', 'userCreated.id')
                          ->where('tasks.closed', '0')
                          ->where('tasks.assigned_id', $recipient->assigned_id)
                          ->select(
                                    'tasks.id',
                                    'tasks.subject',
                                    'userAssigned.name as user_assigned_name',
                                    'userAssigned.email as user_assigned_email',
                                    'customers.name as cust_name',
                                    'userJob.id as user_job_id',
                                    'tasks.created_at',
                                    'userCreated.name as user_created_name',
                                    'tasks.updated_at',
                                    'tasks.due'
                                  )
                          ->get();
                          
            $data = [
                'tasks' => $items,
                'recipient' => $recipient
            ];

            // Mail it out
            Mail::send('emails.tasknotification', $data, function ($message) use ($recipient) {
                $message->to($recipient->email, $recipient->user_assigned_name);
                $message->subject($recipient->subject);
            });
        }

    }
}
