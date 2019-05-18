<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */

use FK3\Models\Vendor;

if ($active)
$headers = ['Vendor', 'Shipping Days', 'Confirmation Days', 'Multiplier', 'Freight', 'Build-Up'];
else
    $headers = ['Vendor'];
$rows = [];
foreach (Vendor::whereActive($active)->orderBy('name')->get() as $vendor)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/vendors/$vendor->id'>$vendor->name</a>",
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/vendors/$vendor->id'>$vendor->name</a>",
            $vendor->shipping_days,
            $vendor->confirmation_days,
            $vendor->multiplier,
            $vendor->freight,
            $vendor->build_up
        ];
    }
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);