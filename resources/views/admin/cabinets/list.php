<?php

use FK3\Models\Cabinet;

if ($active)
{
    $headers = ['Name', 'Frugal Name', 'Price', 'Vendor'];
}
else
{
    $headers = ['Name', 'Frugal Name'];
}
$rows = [];
foreach (Cabinet::whereActive($active)->orderBy('name')->get() as $cabinet)
{
    if (!$active)
    {
        $rows[] = [
            "<a href='/admin/cabinets/$cabinet->id'>$cabinet->name</a>",
            $cabinet->frugal_name,
        ];
    }
    else
    {
        $rows[] = [
            "<a href='/admin/cabinets/$cabinet->id'>$cabinet->name</a>",
            $cabinet->frugal_name,
            number_format($cabinet->price, 2),
            $cabinet->vendor ? $cabinet->vendor->name : "No Vendor Assigned"
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
