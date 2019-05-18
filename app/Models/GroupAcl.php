<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 8:52 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class GroupAcl extends Model
{
    protected $guarded = ['id'];

    /**
     * An ACL belongs to a group.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Pivot to the ACL table.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function acl()
    {
        return $this->belongsTo(Acl::class);
    }



}