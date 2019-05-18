<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */


$fields = [
    'name'              => [
        'label'    => "Vendor Name:",
        '_comment' => "Enter the name of the Vendor"
    ],
    'shipping_days'     => [
        'label'    => 'Days to Ship:',
        '_comment' => 'Enter the number of days until this vendor typically ships'
    ],
    'confirmation_days' => [
        'label'    => 'Confirmation Days:',
        '_comment' => 'Typically how long it takes for a vendor to confirm an order'
    ],
    'multiplier'        => [
        'label'    => 'Multiplier:',
        '_comment' => 'Enter the multiplier',
    ],
    'freight'           => [
        'label'    => 'Freight Cost:',
        '_pre' => "$",
        '_comment' => 'Enter the amount for freight',
    ],
    'build_up'           => [
        'label'    => 'Build-Up Cost:',
        '_pre' => "$",
        '_comment' => 'Enter the amount for build-up',
    ],

    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $vendor->id ? "Updating $vendor->name" : "Creating Vendor..",
        'label'        => $vendor->id ? "Update $vendor->name" : "Create Vendor",
    ]

];
if ($vendor->id)
{
    if ($vendor->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Vendor' data-method='DELETE'
             data-message='Are you sure you want to deactivate this vendor?'
            href='#'>Deactivate Vendor</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactive Vendor' data-method='DELETE'
             data-message='Are you sure you want to reactivate this Vendor?'
            href='#'>Reactivate Vendor</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($vendor->id ? "PUT" : "POST")
    ->bind($vendor)
    ->uri($vendor->id ? "/admin/vendors/$vendor->id" : "/admin/vendors")
    ->fields($fields);
