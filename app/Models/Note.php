<?php

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Note extends Model
{

  public function customer()
  {
    return $this->belongsTo(Customer::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
