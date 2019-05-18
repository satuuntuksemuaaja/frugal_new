<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class QuoteQuestionAnswer extends Model
{
    protected $guarded = ['id'];

    public function quote()
    {
      return $this->belongsTo(Quote::class);
    }

    public function question()
    {
      return $this->belongsTo(Question::class);
    }

    public function quote_question()
    {
      return $this->belongsTo(QuoteQuestion::class);
    }
}
