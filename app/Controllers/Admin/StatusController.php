<?php

namespace FK3\Controllers\Admin;

use FK3\Exceptions\FrugalException;
use FK3\Models\Group;
use FK3\Models\Status;
use FK3\Models\StatusExpiration;
use FK3\Models\StatusExpirationAction;
use Illuminate\Http\Request;
use Vocalogic\Http\Controller;
use Response;

class StatusController extends Controller
{
    public $auditPage = "Status";

    /*
     * Show stores Index.
     */
    public function index()
    {
        $groups = Group::orderBy('name')->get();

        return view('admin.status.index', compact('groups'));
    }

    /**
     * Display Statuses Data
     * @return Array
     */

    public function displayStatuses()
    {
        $items = Status::where('deleted_at', null)->orderBy('name', 'asc')->get();

        if ( !empty( $items ) )
        {
            $newItems = array();
            foreach ( $items as $item )
            {
              $objItems = array();

              $objItems[] = '<a href="#" onclick="EditStatus(' . $item->id . ');">' . $item->name . '</a>';
              $objItems[] = $item->expirations()->count();

              $newItems[] = $objItems;
            }
        }

        // Get Total
        $total = Status::where('deleted_at', null)->orderBy('name', 'asc')->count();

        return array( 	'iTotalRecords' => $total,
              'iTotalDisplayRecords' => $total,
              'data' => $newItems
            );
    }

    /**
     * Store Status Data
     * @return Array
     */

    public function store(Request $request)
    {
        $name = $request->name;
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty'
              ]
            );
        }
        $followup_status = $request->followup_status;
        $followup_lock = $request->followup_lock;
        $setting_id = $request->setting_id;

        $status = new Status();
        $status->stage_id = '1';
        $status->name = $name;
        $status->followup_status = $followup_status;
        $status->followup_lock = $followup_lock;
        $status->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'New Status Added'
          ]
        );
    }

    /**
     * Show Status Data
     * @return Array
     */

    public function show()
    {

    }

    public function getStatus(Request $request)
    {
        $status_id = $request->status_id;

        $status = Status::find($status_id);

        if(!$status)
        {
          return Response::json(
            [
              'response' => 'error',
              'message' => 'Status not found'
            ]
          );
        }

        return Response::json(
          [
            'response' => 'success',
            'title' => 'Edit ' . $status->name,
            'name' => $status->name,
            'followup_status' => $status->followup_status,
            'followup_lock' => $status->followup_lock,
            'status_id' => $status->id
          ]
        );
    }

    /**
     * Update Status Data
     * @return Array
     */

    public function updateStatus(Request $request)
    {
        $name = $request->name;
        if($name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty'
              ]
            );
        }
        $followup_status = $request->followup_status;
        $followup_lock = $request->followup_lock;
        $setting_id = $request->setting_id;

        $status = Status::find($request->status_id);
        if(!$status)
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Status not found.'
              ]
            );
        }
        $status->stage_id = '1';
        $status->name = $name;
        $status->followup_status = $followup_status;
        $status->followup_lock = $followup_lock;
        $status->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Updated'
          ]
        );
    }

    public function getExpirations(Request $request)
    {
        $status_id = $request->status_id;

        $statusExpirations = StatusExpiration::where('status_id', $status_id)->get();

        $data = '';
        foreach($statusExpirations as $expiration)
        {
            $data .= '<tr>';
            $data .= '<td><a href="#" onclick="ShowEditExpiration(' . $expiration->id . ')">' . $expiration->name . '</a></td>';
            $data .= '<td>' . $expiration->expires / 60 / 60 . '</td>';
            $data .= '<td><a href="#" onclick="DeleteExpiration(' . $expiration->id . ')"><i class="fa fa-trash"></i></a></td>';
            $data .= '</tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function saveExpiration(Request $request)
    {
        if($request->name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty.'
              ]
            );
        }
        $status = Status::find($request->status_id);
        $expiration = new StatusExpiration();
        $expiration->status_id = $request->status_id;
        $expiration->name = $request->name;
        $expiration->expires = $request->expires * 60 * 60;
        $expiration->expires_before = $request->expires_before * 60 * 60;
        $expiration->expires_after  = $request->expires_after * 60 * 60;
        $expiration->type = $request->type;
        $expiration->warning = $request->warning;
        $expiration->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Added'
          ]
        );
    }

    public function deleteExpiration(Request $request)
    {
        $expiration = StatusExpiration::find($request->expiration_id);
        $expiration->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Deleted'
          ]
        );
    }

    public function getExpiration(Request $request)
    {
        $expiration = StatusExpiration::find($request->expiration_id);

        return Response::json(
          [
            'response' => 'success',
            'name' => $expiration->name,
            'expires' => $expiration->expires / 60 / 60,
            'expires_before' => $expiration->expires_before / 60 / 60,
            'expires_after' => $expiration->expires_after / 60 / 60,
            'type' => $expiration->type,
            'warning' => $expiration->warning,
            'expiration_id' => $expiration->id
          ]
        );
    }

    public function updateExpiration(Request $request)
    {
        if($request->name == '')
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Name cannot be empty.'
              ]
            );
        }
        $expiration = StatusExpiration::find($request->expiration_id);
        $expiration->name = $request->name;
        $expiration->expires = $request->expires * 60 * 60;
        $expiration->expires_before = $request->expires_before * 60 * 60;
        $expiration->expires_after  = $request->expires_after * 60 * 60;
        $expiration->type = $request->type;
        $expiration->warning = $request->warning;
        $expiration->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Updated'
          ]
        );
    }

    public function getExpirationActions(Request $request)
    {
        $status_expiration_id = $request->status_expiration_id;

        $expirationActions = StatusExpirationAction::where('status_expiration_id', $status_expiration_id)->get();

        $data = '';
        foreach($expirationActions as $expirationAction)
        {
            $data .= '<tr>';
            $data .= '<td><a href="#" onclick="ShowEditExpirationAction(' . $expirationAction->id . ')">' . $expirationAction->description . '</a></td>';
            $data .= '<td>' . $expirationAction->group->name . '</td>';
            $data .= '<td>' . ($expirationAction->sms ? 'Yes' : 'No') . '</td>';
            $data .= '<td>' . ($expirationAction->email ? 'Yes' : 'No') . '</td>';
            $data .= '<td><a href="#" onclick="ShowModalAttachment(' . $expirationAction->id . ');">' . ($expirationAction->attachment ? 'Change' : 'None') . '</a> ' . ($expirationAction->attachment ? '<a href="' . route('status_expiration_action_download_file', ['id' => $expirationAction->id]) . '" target="_blank"><i class="fa fa-download"></i></a>' : '') . '</td>';
            $data .= '<td><a href="#" onclick="DeleteExpirationAction(' . $expirationAction->id . ')"><i class="fa fa-trash"></i></a></td>';
            $data .= '</tr>';
        }

        return Response::json(
          [
            'response' => 'success',
            'data' => $data
          ]
        );
    }

    public function getExpirationAction(Request $request)
    {
        $expirationAction = StatusExpirationAction::find($request->action_id);

        return Response::json(
          [
            'response' => 'success',
            'description' => $expirationAction->description,
            'group_id' => $expirationAction->group_id,
            'sms' => $expirationAction->sms,
            'sms_content' => $expirationAction->sms_content,
            'email' => $expirationAction->email,
            'email_subject' => $expirationAction->email_subject,
            'email_content' => $expirationAction->email_content,
            'action_id' => $expirationAction->id
          ]
        );
    }

    public function saveExpirationAction(Request $request)
    {
        $statusExpiration = StatusExpiration::find($request->expiration_id);
        $expirationAction = new StatusExpirationAction();
        $expirationAction->status_expiration_id = $request->expiration_id;
        $expirationAction->description = $request->description ?: '';
        $expirationAction->sms = $request->sms;
        $expirationAction->email_subject = $request->email_subject ?: '';
        $expirationAction->email  = $request->email;
        $expirationAction->email_content = $request->email_content ?: '';
        $expirationAction->sms_content = $request->sms_content ?: '';
        $expirationAction->group_id = $request->group_id;
        $expirationAction->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Action Added'
          ]
        );
    }

    public function updateExpirationAction(Request $request)
    {
        $expirationAction = StatusExpirationAction::find($request->action_id);
        $expirationAction->description = $request->description ?: '';
        $expirationAction->sms = $request->sms;
        $expirationAction->email_subject = $request->email_subject ?: '';
        $expirationAction->email  = $request->email;
        $expirationAction->email_content = $request->email_content ?: '';
        $expirationAction->sms_content = $request->sms_content ?: '';
        $expirationAction->group_id = $request->group_id;
        $expirationAction->save();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Action Updated'
          ]
        );
    }

    public function deleteExpirationAction(Request $request)
    {
        $expirationAction = StatusExpirationAction::find($request->action_id);
        $expirationAction->delete();

        return Response::json(
          [
            'response' => 'success',
            'message' => 'Status Expiration Action Deleted'
          ]
        );
    }

    public function uploadAttachment(Request $request)
    {
        if ($request->hasFile('attachment'))
        {
            $expirationAction = StatusExpirationAction::find($request->upload_action_id);
            $fileName = $request->file('attachment')->getClientOriginalName();
            $filePath = $request->file('attachment')->store('status_expiration_action');
            $expirationAction->attachment = $filePath;
            $expirationAction->save();

            return Response::json(
              [
                'response' => 'success',
                'message' => 'File Uploaded'
              ]
            );
        }
        else
        {
            return Response::json(
              [
                'response' => 'error',
                'message' => 'Please select a file'
              ]
            );
        }
    }

    public function downloadFile($id)
    {
        $expirationAction = StatusExpirationAction::find($id);

        return response()->download(storage_path("app/") . $expirationAction->attachment);
    }
}
