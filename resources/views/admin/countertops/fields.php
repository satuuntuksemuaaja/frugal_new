<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

use FK3\Models\CountertopType;

$fields = [
    'name'  => [
        'label'    => "Countertop Name:",
        '_comment' => "Enter the name of the countertop"
    ],
    'type_id'  => [
        'label'       => 'Type:',
        'type'        => 'select',
        'textAsValue' => false,
        'opts'        => CountertopType::all()->pluck("name", "id")->all()
    ],
    'price' => [
        'label'    => "Price:",
        '_comment' => "Enter the base price for the countertop",
        '_pre'     => "$"
    ],

    'removal_price' => [
        'label'    => "Removal Price:",
        '_comment' => "Enter the cost to remove countertop",
        '_pre'     => "$"
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $countertop->id ? "Updating $countertop->name" : "Creating Countertop..",
        'label'        => $countertop->id ? "Update $countertop->name" : "Create Countertop Option",
    ]

];
if ($countertop->id)
{
    if ($countertop->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Countertop Option' data-method='DELETE'
             data-message='Are you sure you want to deactivate this countertop option?'
            href='#'>Deactivate Countertop</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Countertop' data-method='DELETE'
             data-message='Are you sure you want to reactivate this countertop option?'
            href='#'>Reactivate Countertop</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($countertop->id ? "PUT" : "POST")
    ->bind($countertop)
    ->uri($countertop->id ? "/admin/countertops/$countertop->id" : "/admin/countertops")
    ->fields($fields);
