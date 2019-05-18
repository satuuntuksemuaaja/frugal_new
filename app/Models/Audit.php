<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/30/18
 * Time: 5:01 PM
 */

namespace FK3\Models;


use FK3\User;
use Vocalogic\Eloquent\Model;

class Audit extends Model
{
    protected $guarded = ['id'];

    /**
     * An Audit is created by a user.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}