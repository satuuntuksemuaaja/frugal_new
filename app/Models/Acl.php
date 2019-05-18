<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/24/18
 * Time: 9:09 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Acl extends Model
{
    protected $guarded = ['id'];

    /**
     * An ACL belongs to a category.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(AclCategory::class, 'acl_category_id');
    }
}
