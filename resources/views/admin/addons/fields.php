<?php

use FK3\Models\Group;

$fields = [
    'item'       => [
        'label'    => "Addon Name:",
        '_comment' => "Enter the name of the addon"
    ],
    'price'      => [
        'label'    => "Price:",
        '_comment' => "Enter the price for the addon",
        '_pre'     => "$"
    ],
    'group_id'   => [
        'label'       => "Addon Group:",
        'type'        => 'select',
        'opts'        => array_replace([0 => '-- Select Group --'],
            Group::get()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'percentage' => [
        'label'       => 'Percentage:',
        '_comment'    => 'What percentage should go to the designated group? (default 100%)',
        'type'        => 'select',
        'textAsValue' => false,
        'opts'        => FK3\Models\Appliance::getPercentages(),
    ],
    'contract'   => [
        'label'    => "Contract:",
        'type'     => 'textarea',
        '_comment' => "Enter the contract for the addon."
    ],
    'automatic' => [
        'raw'      => '<div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="automatic" name="automatic" data-on-text="1" data-off-text="0" onchange="SetAutomatic();" value="' . $addon->automatic . '">
                          <label class="custom-control-label" for="automatic">Make Addon Automatic?</label>
                      </div>'
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.addonBody',
        'data-message' => $addon->id ? "Updating $addon->sku" : "Creating Hadrware..",
        'label'        => $addon->id ? "Update $addon->sku" : "Create Addon",
    ]

];
if ($addon->id)
{
    if ($addon->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Addon' data-method='DELETE'
             data-message='Are you sure you want to deactivate this addon?'
            href='#'>Deactivate Addon</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Addon' data-method='DELETE'
             data-message='Are you sure you want to reactivate this addon?'
            href='#'>Reactivate Addon</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($addon->id ? "PUT" : "POST")
    ->bind($addon)
    ->uri($addon->id ? "/admin/addons/$addon->id" : "/admin/addons")
    ->fields($fields);
