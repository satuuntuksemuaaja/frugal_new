<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

$fields = [
    'contract'     => [
        'type' => 'textarea',
        'rows' => 50,
        '_span' => 12
    ],

    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.contractBody',
        'data-message' => "Updating Contract..",
        'label'        => "Update Contract"
    ]

];


echo Form::init(['ajax' => true])
    ->method($quote_type->id ? "PUT" : "POST")
    ->bind($quote_type)
    ->uri($quote_type->id ? "/admin/quote_types/$quote_type->id" : "/admin/quote_types")
    ->fields($fields);
