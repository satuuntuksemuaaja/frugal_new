<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/25/18
 * Time: 6:18 PM
 */
$headers = $active ? ['Name', 'Email', 'Group', 'Tags'] : ['Name'];
$rows = [];
foreach (\FK3\User::whereCustomerId(0)->whereActive($active)->get() as $user)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/users/$user->id'>$user->name</a>",
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/users/$user->id'>$user->name</a>",
            $user->email,
            $user->group ? $user->group->name : "Unassigned",
            "Tags"
        ];
    }
}
if ($active)
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);
}
else
{
    echo Html::table(['class' => 'table-striped'])->head($headers)->body($rows);
}
