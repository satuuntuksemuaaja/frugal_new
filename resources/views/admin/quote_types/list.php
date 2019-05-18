<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */

use FK3\Models\QuoteType;

$headers = ['Name'];

$rows = [];
foreach (QuoteType::whereActive($active)->orderBy('name')->get() as $quote_type)
{
    $rows[] = [
        "<a href='/admin/quote_types/$quote_type->id'>$quote_type->name</a>",
    ];
}
if ($active)
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows)->datatable();
}
else
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);
}
