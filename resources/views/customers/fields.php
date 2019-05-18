<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 3/17/18
 * Time: 12:59 PM
 */


$fields = [
    'name'    => [
        'label'    => "Customer Name:",
        '_comment' => "Enter the name of the customer"
    ],
    'address' => [
        'label' => "Address:",
        '_comment' => "Enter the home/billing address for the customer"
    ],
    'city' => [
        'label' => "City:",
        '_comment' => "Enter the home/billing city for the customer"
    ],
    'state' => [
        'label' => "State:",
        '_comment' => "Enter the home/billing state for the customer"
    ],
    'zip' => [
        'label' => "Zip:",
        '_comment' => "Enter the home/billing zip for the customer"
    ],
    'email' => [
        'label' => "E-mail Address #1:",
        '_comment' => "Enter the email #1 address for the customer"
    ],
    'email2' => [
        'label' => "E-mail Address #2:",
        '_comment' => "Enter the email #2 address for the customer"
    ],
    'email3' => [
        'label' => "E-mail Address #3:",
        '_comment' => "Enter the email #3 address for the customer"
    ],
    'home' => [
        'label' => "Home/Primary Phone:",
        '_comment' => "Enter the home/primary phone number for the customer"
    ],
    'mobile' => [
        'label' => "Mobile/Alternate Phone:",
        '_comment' => "Enter the Mobile/Alternate phone number for the customer"
    ],





    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.customerBody',
        'data-message' => $customer->id ? "Updating $customer->name" : "Creating Customer..",
        'label'        => $customer->id ? "Update $customer->name" : "Create Customer",
    ]

];
if ($customer->id)
{
    if ($customer->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Customer' data-method='DELETE'
             data-message='Are you sure you want to deactivate this customer?'
            href='#'>Deactivate Customer</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Customer' data-method='DELETE'
             data-message='Are you sure you want to reactivate this customer?'
            href='#'>Reactivate Customer</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($customer->id ? "PUT" : "POST")
    ->bind($customer)
    ->uri($customer->id ? "/customers/$customer->id" : "/customers")
    ->fields($fields);
