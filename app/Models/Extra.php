<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Extra extends Model
{
    protected $guarded = ['none'];

    /**
     * An Extra belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
