<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Cabinet extends Model
{
    protected $guarded = ['none'];

    /**
     * A cabinet belongs to a vendor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
