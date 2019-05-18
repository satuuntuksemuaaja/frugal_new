<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/10/18
 * Time: 2:40 PM
 */

namespace FK3\Models;

use FK3\Models\Traits\GroupPercentage;
use Vocalogic\Eloquent\Model;

class Appliance extends Model
{
    use GroupPercentage;

    protected $guarded = ['none'];

    /**
     * An appliance belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * An appliance belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function split_group()
    {
        return $this->belongsTo(Group::class, 'split_group_id');
    }

}
