<?php

use FK3\Models\Group;

$fields = [
    'question' => [
        'label'    => "Question:",
        '_comment' => "Enter the punch list question."
    ],
    'group_id' => [
        'label'       => 'Type:',
        'type'        => 'select',
        'class'       => 'select2',
        'textAsValue' => false,
        'opts'        => Group::all()->pluck("name", "id")->all()
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $punch->id ? "Updating $punch->question" : "Creating Punch Question..",
        'label'        => $punch->id ? "Update $punch->question" : "Create Punch Option",
    ]

];
if ($punch->id)
{
    if ($punch->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Punch Option' data-method='DELETE'
             data-message='Are you sure you want to deactivate this punch option?'
            href='#'>Deactivate Punch</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Punch' data-method='DELETE'
             data-message='Are you sure you want to reactivate this punch question?'
            href='#'>Reactivate Punch Question</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($punch->id ? "PUT" : "POST")
    ->bind($punch)
    ->uri($punch->id ? "/admin/punches/$punch->id" : "/admin/punches")
    ->fields($fields);
