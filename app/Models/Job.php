<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 1/27/18
 * Time: 9:20 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Job extends Model
{
    protected $guarded = ['none'];

    public function getDates()
    {
        return ['created_at', 'updated_at', 'schedule_sent_on'];
    }

    public function pos()
    {
        return $this->hasMany(Po::class);
    }

    public function buildnotes()
    {
        return $this->hasMany(BuildupNote::class, 'job_id');
    }

    public function orders()
    {
        return $this->hasMany(ChangeOrder::class);
    }

    public function schedules()
    {
        return $this->hasMany(JobSchedule::class);
    }

    public function items()
    {
        return $this->hasMany(JobItem::class);
    }

    public function fft()
    {
        return $this->hasOne(Fft::class);
    }

    public function punches()
    {
        return $this->hasMany(Punch::class);
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function authorization()
    {
        return $this->hasOne(Authorization::class);
    }

    /**
     * A job has many payouts.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * A job has many notes
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notes()
    {
        return $this->hasMany(JobNote::class);
    }

}
