<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class PoItem extends Model
{
    protected $guarded = ['id'];

    public function po()
    {
        return $this->belongsTo(Po::class);
    }

    public function job_item()
    {
        return $this->belongsTo(JobItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
