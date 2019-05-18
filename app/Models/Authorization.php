<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/10/18
 * Time: 2:40 PM
 */

namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Authorization extends Model
{
  public function job()
  {
      return $this->belongsTo(Job::class);
  }

  public function items()
  {
      return $this->hasMany(AuthorizationItem::class);
  }

}
