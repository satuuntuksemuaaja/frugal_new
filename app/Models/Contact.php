<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Contact extends Model
{
    protected $guarded = ['none'];

    /**
     * A contact belongs to a vendor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

}
