<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 6/5/15
 * Time: 12:36 PM
 */

 namespace FK3\Models;


 use Vocalogic\Eloquent\Model;

class LeadUpdate extends Model {

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function newstatus()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
