<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Hardware extends Model
{
    protected $guarded = ['none'];
    protected $table = 'hardwares';

    /**
     * A hardware belongs to a vendor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

}
