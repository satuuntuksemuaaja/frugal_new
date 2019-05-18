<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Question extends Model
{
    protected $table = 'quote_questions';
    protected $priceField = 'amount';
    protected $guarded = ['none'];

    /**
     * A question belongs to a question category.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(QuestionCategory::class);
    }

    /**
     * A question belongs to a vendor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * A question belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * A question belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function condition()
    {
        return $this->hasOne(QuoteQuestionCondition::class);
    }
}
