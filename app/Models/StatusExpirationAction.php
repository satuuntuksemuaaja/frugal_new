<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:20 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class StatusExpirationAction extends Model
{
    protected $guarded = ['none'];

    public function leads()
    {

    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

}
