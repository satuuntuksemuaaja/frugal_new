<?php

use FK3\Models\Addon;

if ($active)
    $headers = ['Item', 'Price', 'Group'];
else
    $headers = ['Item'];
$rows = [];
foreach (Addon::whereActive($active)->orderBy('item')->get() as $addon)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/addons/$addon->id'>$addon->item</a>",
        $addon->item,
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/addons/$addon->id'>$addon->item</a>",
            number_format($addon->price, 2),
            $addon->group ? $addon->group->name : "No Group Assigned"
        ];
    }
}
if ($active)
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows)->datatable();
}
else
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);
}
