<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Punch extends Model
{
    protected $guarded = ['none'];

    /**
     * A punch belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

}
