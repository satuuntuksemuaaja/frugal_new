<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */

use FK3\Models\Countertop;

if ($active)
{
    $headers = ['Name', 'Type', 'Price', 'Removal Price'];
}
else
{
    $headers = ['Name'];
}
$rows = [];
foreach (Countertop::whereActive($active)->orderBy('name')->get() as $countertop)
{
    if (!$active)
    {
        $rows[] = [
            "<a href='/admin/countertops/$countertop->id'>$countertop->name</a>",
        ];
    }
    else
    {
        $rows[] = [
            "<a href='/admin/countertops/$countertop->id'>$countertop->name</a>",
            $countertop->type->name,
            number_format($countertop->price, 2),
            number_format($countertop->removal_price, 2)
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
