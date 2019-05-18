<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */
$headers = ['Source Name'];
$rows = [];
foreach (\FK3\Models\LeadSource::whereActive($active)->orderBy('name')->get() as $source)
{
    $rows[] = [
        "<a href='/admin/lead_sources/$source->id'>$source->name</a>",
    ];
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);
