<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */


use FK3\Models\Group;

$fields = [
    'name'       => [
        'label'    => "Appliance Name:",
        '_comment' => "Enter the name of the appliance"
    ],
    'price'      => [
        'label'    => "Price:",
        '_comment' => "Enter the price for the appliance",
        '_pre'     => "$"
    ],
    'group_id'   => [
        'label'       => "Money goes to:",
        'type'        => 'select',
        'opts'        => array_replace([0 => '-- Select Group --'],
            Group::get()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'split_group_id'   => [
        'label'       => "Split money with:",
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
    'second_group_percentage' => [
        'label'       => '2nd Group Percentage:',
        '_comment'    => 'What percentage should go to the 2nd designated group? (default 0%)',
        'type'        => 'select',
        'textAsValue' => false,
        'opts'        => FK3\Models\Appliance::getPercentages(),
    ],
    'count_as'   => [
        'label'    => "Counts As:",
        '_comment' => "How many appliances does this count for"
    ],


    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.applianceBody',
        'data-message' => $appliance->id ? "Updating $appliance->name" : "Creating Appliance..",
        'label'        => $appliance->id ? "Update $appliance->name" : "Create Appliance",
    ]

];
if ($appliance->id)
{
    if ($appliance->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Appliance' data-method='DELETE'
             data-message='Are you sure you want to deactivate this appliance?'
            href='#'>Deactivate Appliance</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Appliance' data-method='DELETE'
             data-message='Are you sure you want to reactivate this appliance?'
            href='#'>Reactivate Appliance</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($appliance->id ? "PUT" : "POST")
    ->bind($appliance)
    ->uri($appliance->id ? "/admin/appliances/$appliance->id" : "/admin/appliances")
    ->fields($fields);
