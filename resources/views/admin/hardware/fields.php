<?php

use FK3\Models\Vendor;

$fields = [
    'sku'         => [
        'label'    => "Hardware SKU:",
        '_comment' => "Enter the sku of the hardware"
    ],
    'price'       => [
        'label'    => "Price:",
        '_comment' => "Enter the price for the hardware",
        '_pre'     => "$"
    ],
    'vendor_id'   => [
        'label'       => "Hardware Vendor:",
        'type'        => 'select',
        'opts'        => array_replace([0 => '-- Select Vendor --'],
            Vendor::orderBy('name')->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'description' => [
        'label'    => "Description:",
        'type'     => 'textarea',
        '_comment' => "Enter a description of the hardware"
    ],
    'image'       => [
        'label' => 'Image:',
        'type'  => 'file',
        'span'  => 7,
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.hardwareBody',
        'data-message' => $hardware->id ? "Updating $hardware->sku" : "Creating Hadrware..",
        'label'        => $hardware->id ? "Update $hardware->sku" : "Create Hardware",
    ]

];
if ($hardware->id)
{
    if ($hardware->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Hardware' data-method='DELETE'
             data-message='Are you sure you want to deactivate this hardware?'
            href='#'>Deactivate Hardware</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Hardware' data-method='DELETE'
             data-message='Are you sure you want to reactivate this hardware?'
            href='#'>Reactivate Hardware</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($hardware->id ? "PUT" : "POST")
    ->bind($hardware)
    ->uri($hardware->id ? "/admin/hardware/$hardware->id" : "/admin/hardware")
    ->fields($fields);

