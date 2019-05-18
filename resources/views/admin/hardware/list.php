<?php

use FK3\Models\Hardware;
use FK3\Models\Vendor;

if ($active)
    $headers = ['SKU', 'Price', 'Vendor'];
else
    $headers = ['SKU'];
$rows = [];
foreach (Hardware::whereActive($active)->orderBy('sku')->get() as $hardware)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/hardware/$hardware->id'>$hardware->sku</a>",
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/hardware/$hardware->id'>$hardware->sku</a>",
            number_format($hardware->price, 2),
            $hardware->vendor ? $hardware->vendor->name : "No Vendor Assigned"
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
