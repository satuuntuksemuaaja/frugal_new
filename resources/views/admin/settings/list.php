<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */
$headers = ['Name', 'Description', 'Value'];
$rows = [];
foreach (settingsFor($plugin) as $setting)
{
    $rows[] = [
        "<a href='/admin/settings/$setting->id'>$setting->name</a>",
        $setting->description,
        $setting->value
    ];
}
echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);