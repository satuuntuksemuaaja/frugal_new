<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class QuestionCategory extends Model
{
    protected $guarded = ['none'];

    /**
     * A question category has many questions.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

}
