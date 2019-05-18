<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

use FK3\Models\AclCategory;


$fields = [
    'setting'     => [
        'label'    => "Internal Setting:",
        '_comment' => "This is the internal code that F3 will access to retreive the setting. (ie. admin.view, leads.all, leads.mine, etc)"
    ],
    'name'        => [
        'label'    => "Name:",
        '_comment' => 'Enter a short name for this setting'
    ],
    'description' => [
        'type'     => 'textarea',
        'label'    => "Description:",
        '_comment' => "Explain what this setting is for."
    ],
    'type'        => [
        'label'    => "Type of Setting",
        'type'     => 'select',
        'opts'     => ['text', 'select'],
        '_comment' => "What type of answer is given?"
    ],
    'meta'        => [
        'label'    => "Select Meta (opt):",
        '_comment' => "Give drop down options"
    ],
    'plugin'      => [
        'type' => 'hidden',
        'val'  => app('request')->plugin
    ],


    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $setting->id ? "Updating $setting->action" : "Creating ACL..",
        'label'        => $setting->id ? "Update $setting->action" : "Create ACL",
    ]

];
if ($setting->id)
{
    $fields[] = [
        'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Remove Setting' data-method='DELETE' 
             data-message='Are you sure you want to remove this setting?' 
            href='#'>Delete ACL</a>"
    ];
}

echo Form::init(['ajax' => true])
    ->method($setting->id ? "PUT" : "POST")
    ->bind($setting)
    ->uri($setting->id ? "/admin/settings/$setting->id" : "/admin/settings")
    ->fields($fields);
