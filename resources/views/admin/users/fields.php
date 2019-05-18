<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

use FK3\Models\Group;

$fields = [
    'name'     => [
        'label'    => "Name:",
        '_comment' => "Enter the first and last name of the user."
    ],
    'password' => [
        'label'    => 'New Password:',
        '_comment' => "Enter a password for this user if new, or if you need to reset the password.",
        'type'     => 'password'
    ],
    'email'    => [
        'label'    => "E-mail Address:",
        '_comment' => "Enter the email address they will use to login to Frugal 3"
    ],
    'group_id' => [
        'label'       => 'Select Group:',
        'type'        => 'select',
        'class'       => 'select2',
        'opts'        => array_replace([0 => '-- Select Group --'], Group::all()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'mobile'   => [
        'label' => 'Mobile Number:',
    ],

    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.userBody',
        'data-message' => $user->id ? "Updating $user->name" : "Creating User..",
        'label'        => $user->id ? "Update $user->name" : "Create User",
    ]

];
if ($user->id)
{
    if ($user->active)
    {
        $fields[] = [
            'raw' => "
                 <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate User' data-method='DELETE'
                 data-message='Are you sure you want to deactivate this user?'
                href='#'>Deactivate User</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
                 <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Remove User' data-method='DELETE' 
                 data-message='Are you sure you want to de-activate this user?' 
                href='#'>Deactivate User</a>"
        ];
    }
}
echo Form::init(['ajax' => true])
    ->method($user->id ? "PUT" : "POST")
    ->bind($user)
    ->uri($user->id ? "/admin/users/$user->id" : "/admin/users")
    ->fields($fields);
