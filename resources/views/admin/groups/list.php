<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */
$headers = ['Group', 'Users Associated'];
$rows = [];
foreach (\FK3\Models\Group::orderBy('name')->get() as $group)
{
    $rows[] = [
        "<a href='/admin/groups/$group->id'>$group->name</a>",
        implode(", ", $group->users()->pluck('name')->all())
    ];
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows)->datatable();