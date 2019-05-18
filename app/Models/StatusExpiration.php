<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:20 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class StatusExpiration extends Model
{

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function actions()
    {
      return $this->hasMany(StatusExpirationAction::class, 'status_expiration_id');
    }

}
