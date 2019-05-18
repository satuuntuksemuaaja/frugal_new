<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class CountertopType extends Model
{
    protected $guarded = ['none'];

    /**
     * A countertop type has many countertops.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function countertops()
    {
        return $this->hasMany(Countertop::class, 'type_id');
    }

}
