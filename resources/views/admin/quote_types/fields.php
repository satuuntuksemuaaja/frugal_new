<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

$rows = [];
$checked = $quote_type->cabinets ? "checked" : null;
$rows[] = [
    "Require Cabinets",
    "<input type='checkbox' class='switch' name='cabinets' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->countertops ? "checked" : null;
$rows[] = [
    "Require Countertops",
    "<input type='checkbox' class='switch' name='countertops' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->sinks ? "checked" : null;
$rows[] = [
    "Require Sinks",
    "<input type='checkbox' class='switch' name='sinks' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->appliances ? "checked" : null;
$rows[] = [
    "Require Appliances",
    "<input type='checkbox' class='switch' name='appliances' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->accessories ? "checked" : null;
$rows[] = [
    "Require Accessories",
    "<input type='checkbox' class='switch' name='accessories' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->hardware ? "checked" : null;
$rows[] = [
    "Require Hardware",
    "<input type='checkbox' class='switch' name='hardware' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->led ? "checked" : null;
$rows[] = [
    "Require LED",
    "<input type='checkbox' class='switch' name='led' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->tile ? "checked" : null;
$rows[] = [
    "Require Tile",
    "<input type='checkbox' class='switch' name='tile' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->addons ? "checked" : null;
$rows[] = [
    "Require Addons",
    "<input type='checkbox' class='switch' name='addons' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->responsibilities ? "checked" : null;
$rows[] = [
    "Require Customer Responsibilities",
    "<input type='checkbox' class='switch' name='responsibilities' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->questionaire ? "checked" : null;
$rows[] = [
    "Require Questionnaire",
    "<input type='checkbox' class='switch' name='questionaire' $checked data-on-text='Yes' data-off-text='No'>"
];
$checked = $quote_type->buildup ? "checked" : null;
$rows[] = [
    "Require Buildup",
    "<input type='checkbox' class='switch' name='buildup' $checked data-on-text='Yes' data-off-text='No'>"
];


$table = Html::table(['class' => "table-striped"])->body($rows);
$fields = [
    'name' => [
        'label'    => "Quote Type:",
        '_comment' => "Enter the type of quote"
    ],
    [
        'raw' => $table
    ],


    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $quote_type->id ? "Updating $quote_type->name" : "Creating Quote Type..",
        'label'        => $quote_type->id ? "Update $quote_type->name" : "Create Quote Type",
    ]

];
if ($quote_type->id)
{
    if ($quote_type->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Quote Type' data-method='DELETE'
             data-message='Are you sure you want to deactivate this Quote Type?'
            href='#'>Deactivate Quote Type</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Quote Type' data-method='DELETE'
             data-message='Are you sure you want to reactivate this Quote Type?'
            href='#'>Reactivate Quote Type</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($quote_type->id ? "PUT" : "POST")
    ->bind($quote_type)
    ->uri($quote_type->id ? "/admin/quote_types/$quote_type->id" : "/admin/quote_types")
    ->fields($fields);
