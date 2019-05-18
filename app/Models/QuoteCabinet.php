<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class QuoteCabinet extends Model
{
    protected $guarded = ['id'];

    public function cabinet()
    {
      return $this->belongsTo(Cabinet::class);
    }

    public function quote()
    {
      return $this->belongsTo(Quote::class);
    }
}
