<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:09 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class ChangeOrder extends Model
{
    public function getDates()
    {
        return ['created_at', 'updated_at', 'signed_on', 'sent_on'];
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ChangeOrderDetail::class, 'change_order_id');
    }
    /*
     * Is everything ordered?
     *
     */
    public function getOrderedAttribute()
    {
        $allOrdered = true;
        foreach ($this->items AS $item)
        {
            if ($item->orderable && !$item->ordered_by)
                $allOrdered = false;
        }
        return $allOrdered;
    }
}
