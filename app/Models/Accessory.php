<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:09 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Accessory extends Model
{
  protected $dates = ['deleted_at'];

  public function vendor()
  {
    return $this->belongsTo(Vendor::class);
  }
}
