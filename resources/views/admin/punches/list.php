<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:36 PM
 */

use FK3\Models\Punch;

if ($active)
{
    $headers = ['Question', 'Group'];
}
else
{
    $headers = ['Question'];
}
$rows = [];
foreach (Punch::whereActive($active)->orderBy('question')->get() as $punch)
{
    if (!$active)
    {
        $rows[] = [
            "<a href='/admin/punches/$punch->id'>$punch->question</a>",
        ];
    }
    else
    {
        $rows[] = [
            "<a href='/admin/punches/$punch->id'>$punch->question</a>",
            $punch->group->name,

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
