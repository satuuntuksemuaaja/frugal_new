<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 8:45 PM
 */

namespace FK3\Models;


use FK3\User;
use Vocalogic\Eloquent\Model;

/**
 * @property mixed acls
 */
class Group extends Model
{
    protected $guarded = ['id'];

    /**
     * A group has many ACL pivots
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function acls()
    {
        return $this->hasMany(GroupAcl::class);
    }

    /**
     * A group has many users.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}