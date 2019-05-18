<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class QuoteCountertop extends Model
{
    protected $guarded = ['none'];

    /**
     * A quote countertop belongs to a countertop
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function countertop()
    {
        return $this->belongsTo(Countertop::class);
    }

    /**
     * A quote countertop belongs to a quote
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
