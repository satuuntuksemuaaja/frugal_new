<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class User extends Model
{
    protected $guarded = ['none'];

    /**
     * A user belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * A user belongs to a customer.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotes()
  	{
  		return $this->hasManyThrough(Quote::class, Lead::class);
  	}
}
