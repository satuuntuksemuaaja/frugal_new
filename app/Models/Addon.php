<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;
use FK3\Models\Traits\GroupPercentage;

class Addon extends Model
{
    use GroupPercentage;

    protected $guarded = ['none'];
    protected $table = 'addons';

    /**
     * An addon belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

}
