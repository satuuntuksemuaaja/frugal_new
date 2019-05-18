<?php

use FK3\Models\Question;

if ($active)
    $headers = ['Question', 'Money To', 'Stage', 'Category'];
else
    $headers = ['Question'];
$rows = [];
foreach (Question::whereActive($active)->orderBy('question')->get() as $question)
{
    if (!$active)
    $rows[] = [
        "<a href='/admin/questions/$question->id'>$question->question</a>",
    ];
    else
    {
        $rows[] = [
            "<a href='/admin/questions/$question->id'>$question->question</a>",
            number_format($question->price, 2),
            $question->group ? $question->group->name : "No Group Assigned",
            $question->category ? $question->category->name : "No Category",

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
