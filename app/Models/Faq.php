<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Faq extends Model
{
    protected $guarded = ['none'];

    /**
     * An Faq belongs to a quote_types.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quote_type()
    {
        return $this->belongsTo(QuoteType::class);
    }
}
