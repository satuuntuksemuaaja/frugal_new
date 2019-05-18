<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class QuoteResponsibility extends Model
{
    protected $guarded = ['none'];

    /**
     * A quote responsibility belongs to a responsibility
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsibility()
    {
        return $this->belongsTo(Responsibility::class);
    }

    /**
     * A quote responsibility belongs to a quote
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}
