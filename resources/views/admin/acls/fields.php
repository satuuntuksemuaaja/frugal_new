<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 7:59 PM
 */

use FK3\Models\AclCategory;


$fields = [
    'acl' => [
        'label'    => "ACL Identifier:",
        '_comment' => "This is the internal code that F3 will access to perform the access control check. (ie. admin.view, leads.all, leads.mine, etc)"
    ],

    'action'          => [
        'label'    => "ACL Action:",
        '_comment' => 'Enter the action to be allowed. (ie. Show all Leads)',
    ],
    'description'     => [
        'label'    => 'Description:',
        '_comment' => 'Brief description (i.e. Show all leads not just my own)',
        'type'     => 'textarea'
    ],
    'acl_category_id' => [
        'label'       => 'Category:',
        'type'        => 'select',
        'class'       => 'select2',
        'textAsValue' => false,
        'opts'        => AclCategory::all()->pluck("name", "id")->all()
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.groupBody',
        'data-message' => $acl->id ? "Updating $acl->action" : "Creating ACL..",
        'label'        => $acl->id ? "Update $acl->action" : "Create ACL",
    ]

];
if ($acl->id)
{
    $fields[] = [
        'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Remove ACL' data-method='DELETE' 
             data-message='Are you sure you want to remove this access control?' 
            href='#'>Delete ACL</a>"
    ];
}

if (!empty($category))
{
    $fields['acl_category_id']['opts'] = array_replace([$category->id => $category->name],
        AclCategory::all()->pluck("name", "id")->all());
}

echo Form::init(['ajax' => true])
    ->method($acl->id ? "PUT" : "POST")
    ->bind($acl)
    ->uri($acl->id ? "/admin/acls/$acl->id" : "/admin/acls")
    ->fields($fields);
