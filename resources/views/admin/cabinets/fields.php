<?php

use FK3\Models\Vendor;

$fields = [
    'name'        => [
        'label'    => "Cabinet Name:",
        '_comment' => "Enter the name of the cabinet"
    ],
    'frugal_name' => [
        'label'    => "Frugal Name:",
        '_comment' => "Enter the frugal name of the cabinet"
    ],
    'removal_price'       => [
        'label'    => "Removal Price:",
        '_comment' => "Enter the removal price for the cabinet",
        '_pre'     => "$"
    ],
    'vendor_id'   => [
        'label'       => "Cabinet Vendor:",
        'type'        => 'select',
        'opts'        => array_replace([0 => '-- Select Vendor --'],
            Vendor::orderBy('name')->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'description' => [
        'label'    => "Description:",
        'type'     => 'textarea',
        '_comment' => "Enter a description of the cabinet"
    ],
    'image'       => [
        'label' => 'Image:',
        'type'  => 'file',
        'span'  => 7,
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.cabinetBody',
        'data-message' => $cabinet->id ? "Updating $cabinet->sku" : "Creating Hadrware..",
        'label'        => $cabinet->id ? "Update $cabinet->sku" : "Create Cabinet",
    ]

];
if ($cabinet->id)
{
    if ($cabinet->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Cabinet' data-method='DELETE'
             data-message='Are you sure you want to deactivate this cabinet?'
            href='#'>Deactivate Cabinet</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Cabinet' data-method='DELETE'
             data-message='Are you sure you want to reactivate this cabinet?'
            href='#'>Reactivate Cabinet</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($cabinet->id ? "PUT" : "POST")
    ->bind($cabinet)
    ->uri($cabinet->id ? "/admin/cabinets/$cabinet->id" : "/admin/cabinets")
    ->fields($fields);
