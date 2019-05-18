<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */


$fields = [
    'name' => [
        'label'    => "Lead Source:",
        '_comment' => "Enter the lead source name"
    ],

   
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.sourceBody',
        'data-message' => $source->id ? "Updating $source->name" : "Creating Source..",
        'label'        => $source->id ? "Update $source->name" : "Create Lead Source",
    ]

];
if ($source->id)
{
    if ($source->active)
    $fields[] = [
        'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Lead Source' data-method='DELETE'
             data-message='Are you sure you want to deactivate this lead source?'
            href='#'>Deactivate Source</a>"
    ];
    else
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactive Lead Source' data-method='DELETE'
             data-message='Are you sure you want to reactivate this lead source?'
            href='#'>Reactivate Source</a>"
        ];

}


echo Form::init(['ajax' => true])
    ->method($source->id ? "PUT" : "POST")
    ->bind($source)
    ->uri($source->id ? "/admin/lead_sources/$source->id" : "/admin/lead_sources")
    ->fields($fields);
