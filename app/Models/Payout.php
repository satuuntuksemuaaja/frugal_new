<?php

namespace FK3\Models;


use Carbon\Carbon;
use Vocalogic\Eloquent\Model;

class Payout extends Model
{
    protected $guarded = [];

    /**
     * A payout relates to a job.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * What contractor does this relate to?
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Has many Payout Items
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(PayoutItem::class);
    }
}
