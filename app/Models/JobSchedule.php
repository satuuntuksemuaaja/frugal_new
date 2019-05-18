<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:20 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class JobSchedule extends Model
{
    public function getDates()
    {
      return ['created_at', 'updated_at', 'start', 'end'];
    }

    public function job()
    {
      return $this->belongsTo(Job::class);
    }

    public function group()
    {
      return $this->belongsTo(Group::class);
    }

    public function user()
    {
      return $this->belongsTo(User::class);
    }

}
