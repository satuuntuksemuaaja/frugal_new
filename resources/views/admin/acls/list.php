<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */
$headers = ['ACL', 'Action', 'Description'];
$rows = [];
foreach ($category->acls as $acl)
{
    $rows[] = [
        "<a href='/admin/acls/$acl->id'>$acl->acl</a>",
        $acl->action,
        $acl->description
    ];
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);