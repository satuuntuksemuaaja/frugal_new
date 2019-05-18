<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */
$fields = [
    'name' => [
        'label' => "Name:",
        '_comment' => "Enter the name of the access control group."
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $group->id ? "Updating $group->name" : "Creating Group..",
        'label'        => $group->id ? "Update $group->name" : "Create Group",
    ]

];
if ($group->id)
{
    $fields[] = [
        'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Remove Group' data-method='DELETE' 
             data-message='Are you sure you want to remove this group?' 
            href='#'>Delete Group</a>"
    ];
}
echo Form::init(['ajax' => true])
    ->method($group->id ? "PUT" : "POST")
    ->bind($group)
    ->uri($group->id ? "/admin/groups/$group->id" : "/admin/groups")
    ->fields($fields);
