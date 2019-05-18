<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class QuoteQuestion extends Model
{
    protected $guarded = ['id'];

    public function condition()
    {
      return $this->hasOne(QuoteQuoestionCondition::class, 'question_id');
    }

    public function group()
    {
      return $this->belongsTo(Group::class);
    }

    public function category()
    {
      return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }
}
