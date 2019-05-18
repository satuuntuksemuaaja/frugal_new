<?php

use FK3\Models\Group;
use FK3\Models\Vendor;
use FK3\Models\QuestionCategory;
use FK3\Models\Question;

$fields = [
    'question'             => [
        'label'       => "Question:",
        'placeholder' => 'Question here - e.g. - How many electrical outlets need to be installed?',
        'type'        => 'textarea',
    ],
    'response_type'        => [
        'label' => 'Response Type?',
        'type'  => 'select',
        'opts'  => ['yes/no', 'numbers'],
    ],
    'stage'                => [
        'label'       => 'Ask In Stage:',
        'type'        => 'select',
        'opts'        => ['B' => 'Both', 'I' => 'Initial', 'F' => 'Final'],
        'textAsValue' => false
    ],
    'question_category_id' => [
        'label'       => 'Question Category:',
        'type'        => 'select',
        'class'       => 'select2',
        'opts'        => array_replace([0 => '-- Select Question Category --'],
            QuestionCategory::get()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'vendor_id'            => [
        'label'       => "Vendor Association:",
        'type'        => 'select',
        'class'       => 'select2',
        'opts'        => array_replace([0 => '-- Select Vendor --'],
            Vendor::get()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'group_id'             => [
        'label'       => "Group Money Associated To:",
        'type'        => 'select',
        'class'       => 'select2',
        'opts'        => array_replace([0 => '-- Select Group --'],
            Group::get()->pluck("name", "id")->all()),
        'textAsValue' => false,
    ],
    'contract' => [
        'raw'      => '<div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="contract" name="contract" data-on-text="1" data-off-text="0" onchange="SetContract();" value="' . $question->contract . '">
                          <label class="custom-control-label" for="contract">Ask On Contract?</label>
                      </div>'
    ],
    'contract_format'      => [
        'label' => 'Contract Format:',
        'type'  => 'textarea',
    ],
    'small_job' => [
        'raw'      => '<div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="small_job" name="small_job" data-on-text="1" data-off-text="0" onchange="SetSmallJob();" value="' . $question->small_job . '">
                          <label class="custom-control-label" for="small_job">Only for Small Job?</label>
                      </div>'
    ],
    'on_checklist' => [
        'raw'      => '<div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="on_checklist" name="on_checklist" data-on-text="1" data-off-text="0" onchange="SetOnChecklist();" value="' . $question->on_checklist . '">
                          <label class="custom-control-label" for="on_checklist">Include on Build-up Checklist?</label>
                      </div>'
    ],
    'on_job_board' => [
        'raw'      => '<div class="custom-control custom-checkbox">
                          <input type="checkbox" class="custom-control-input" id="on_job_board" name="on_job_board" data-on-text="1" data-off-text="0" onchange="SetOnJobBoard();" value="' . $question->on_job_board . '">
                          <label class="custom-control-label" for="on_job_board">Include on Item List in Job Board?</label>
                      </div>'
    ],
    [
        'type'         => 'submit',
        'class'        => 'btn btn-primary uiblock !important',
        'data-el'      => '.questionBody',
        'data-message' => $question->id ? "Updating $question->id" : "Creating Question..",
        'label'        => $question->id ? "Update Question" : "Create Question",
    ]

];
if ($question->id)
{
    if ($question->active)
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-danger' data-color='red' data-confirm='Deactivate Question' data-method='DELETE'
             data-message='Are you sure you want to deactivate this question?'
            href='#'>Deactivate Question</a>"
        ];
    }
    else
    {
        $fields[] = [
            'raw' => "
             <a class='confirm ml-3 btn btn-success' data-color='green' data-confirm='Reactivate Question' data-method='DELETE'
             data-message='Are you sure you want to reactivate this question?'
            href='#'>Reactivate Question</a>"
        ];
    }

}


echo Form::init(['ajax' => true])
    ->method($question->id ? "PUT" : "POST")
    ->bind($question)
    ->uri($question->id ? "/admin/questions/$question->id" : "/admin/questions")
    ->fields($fields);
