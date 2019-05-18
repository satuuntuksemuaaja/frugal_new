<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 2:47 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class AclCategory extends Model
{
    protected $guarded = ['id'];

    /**
     * A category has many acls
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function acls()
    {
        return $this->hasMany(Acl::class);
    }

}