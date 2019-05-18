<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */

use FK3\Models\Appliance;
use FK3\Models\Vendor;

if ($active)
$headers = ['Name', 'Price', 'Counts As', 'Group', 'Split Group'];
else
    $headers = ['Name'];
$rows = [];
foreach (Appliance::whereActive($active)->orderBy('name')->get() as $app)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/appliances/$app->id'>$app->name</a>",
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/appliances/$app->id'>$app->name</a>",
            number_format($app->price,2),
            $app->count_as,
            $app->group ? $app->group->name : "No Group Assigned",
            $app->split_group ? $app->split_group->name : "No Group Assigned"
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
