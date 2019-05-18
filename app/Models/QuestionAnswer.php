<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;
use FK3\Models\Traits\GroupPercentage;
use FK3\Models\QuestionCondition;

class QuestionAnswer extends Model
{
    use GroupPercentage;

    protected $table = 'quote_question_answers';
    protected $guarded = ['none'];

    /**
     * Override GroupPercentage trait's getRealPrice method because the
     * fields related to price are actually in the associated
     * `quote_question_conditions` table.
     *
     * @return float
     */
    public function getRealPrice()
    {
        $condition = (new QuestionCondition)->find($this->question_id)->first();
        $price = $condition->amount;

        if ($condition->once === true) {
            return $price;
        }

        return $price * $this->answer;
    }

    public function getPercentage()
    {
        $condition = (new QuestionCondition)->find($this->question_id)->first();

        return $condition->percentage;
    }

    /**
     * An answer belongs to a question.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function question()
    {
        return $this->belongsTo(QuoteQuestion::class);
    }

    public function quote_question()
    {
        return $this->belongsTo(QuoteQuestion::class);
    }

    /**
     * An answer belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * An answer belongs to a quote.
     * @todo add Quotes migration which needs leads and promotions models.
     *   Trying not to make this PR too big right now.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }


    /**
     * Model lifecycle callbacks.
     *
     * During creation, ensure that the group_id is inherited from the question.
     *
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function ($answer) {
            $question = (new Question)->find($answer->question_id)->first();
            $answer->group_id = $question->group_id;
        });
    }
}
