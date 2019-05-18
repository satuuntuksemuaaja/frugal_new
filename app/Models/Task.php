<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Task extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function assigned()
    {
      return $this->belongsTo(User::class, 'assigned_id');
    }

    public function customer()
    {
      return $this->belongsTo(Customer::class);
    }

    public function job()
    {
      return $this->belongsTo(Job::class);
    }

    public function notes()
    {
      return $this->hasMany('TaskNote');
    }
}
