<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/24/18
 * Time: 6:35 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Appointment extends Model
{
    protected $guarded = ['none'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}