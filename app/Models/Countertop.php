<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Countertop extends Model
{
    protected $guarded = ['none'];

    /**
     * A countertop belongs to a countertop type.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(CountertopType::class, 'type_id');
    }

}
