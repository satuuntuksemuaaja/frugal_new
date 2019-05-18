<?php

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Notification extends Model
{
  public function status()
  {
    return $this->belongsTo(Status::class);
  }

  public function expiration()
  {
    return $this->belongsTo(StatusExpiration::class, 'status_expiration_id');
  }



}
