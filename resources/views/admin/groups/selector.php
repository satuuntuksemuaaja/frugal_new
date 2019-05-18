<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 3:39 PM
 */

$headers = [null, 'Action', 'Description'];
$rows = [];
foreach ($category->acls as $acl)
{
    if (groupCan($group, $acl->acl))
    {
        $checked = "checked";
    }
    else $checked = null;
    $checkbox = " <input type=\"checkbox\" $checked name='acl_{$acl->id}' class=\"form-control\">";
    $rows[] = [
        $checkbox,
        $acl->action . "<br/><small>{$acl->acl}</small>",
        $acl->description
    ];
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);