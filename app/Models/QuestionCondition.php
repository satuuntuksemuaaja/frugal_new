<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;
use FK3\Models\Question;
use FK3\Models\QuestionAnswer;

class QuestionCondition extends Model
{
    protected $table = 'quote_question_conditions';
    protected $guarded = ['none'];

    public function question()
    {
        return $this->belongsTo(QuoteQuestion::class);
    }
}
