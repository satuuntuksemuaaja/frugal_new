<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:20 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Status extends Model
{

    public function expirations()
    {
        return $this->hasMany(StatusExpiration::class);
    }

    static public function getStatusById($id)
    {
        return self::find($id)->name;
    }

}
