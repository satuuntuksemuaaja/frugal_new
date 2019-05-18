<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class QuoteAddon extends Model
{
    protected $guarded = ['id'];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }
}