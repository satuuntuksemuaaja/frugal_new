<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 5/22/18
 * Time: 4:29 PM
 */

namespace FK3\Models;


use Carbon\Carbon;
use Vocalogic\Eloquent\Model;

/**
 * @property mixed customer
 * @property mixed created_at
 * @property mixed source
 */
class Lead extends Model
{
    public    $table   = "leads";
    protected $guarded = ['none'];

    /**
     * A lead belongs to a customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * A lead has a source.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function source()
    {
        return $this->belongsTo(LeadSource::class);
    }

    /**
     * A lead belongs to a designer/user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Datatable Computed Attribute
     * @return mixed
     */
    public function getCustomerNameAttribute()
    {
        if (!$this->customer) return "Unknown Customer";
        return $this->customer->name . " ($this->id)";
    }

    /**
     * Get age property.
     * @return mixed
     */
    public function getAgeAttribute()
    {
        if ($this->created_at)
        {
            return $this->created_at->format("m/d/y");
        }
    }

    /**
     * Get the human attribute for lead source.
     * @return mixed
     */
    public function getSourceNameAttribute()
    {
        return $this->source ? $this->source->name : "Unknown Source";
    }

    /**
     * Get Designer
     * @return string
     */
    public function getDesignerAttribute()
    {
        return $this->user ? $this->user->name : "Unknown Designer";
    }

    public function showroom()
    {
        return $this->hasOne(Appointment::class)->whereType('showroom');
    }

    public function closing()
    {
        return $this->hasOne(Appointment::class)->whereType('Closing');
    }

    public function measure()
    {
        return $this->hasOne(Appointment::class)->whereType('Measure');
    }

    public function getShowroomHumanAttribute()
    {
        $name = $this->showroom && $this->showroom->user ? $this->showroom->user->name : "Nobody";
        return $this->showroom ? Carbon::parse($this->showroom->scheduled)
                ->format("m/d/y") . "<br/><small>" . $this->showroom->location . " with $name</small>" : "Not Set";
    }

    public function getClosingHumanAttribute()
    {
        $name = $this->closing && $this->closing->user ? $this->closing->user->name : "Nobody";
        return $this->closing ? Carbon::parse($this->closing->scheduled)
                ->format("m/d/y") . "<br/><small>" . $this->closing->location . " with $name</small>" : "Not Set";
    }

    public function getMeasureHumanAttribute()
    {
        $name = $this->measure && $this->measure->user ? $this->measure->user->name : "Nobody";
        return $this->measure ? Carbon::parse($this->measure->scheduled)
                ->format("m/d/y") . "<br/><small>" . $this->measure->location . " with $name</small>" : "Not Set";
    }

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function showroom_location()
    {
        return $this->belongsTo(Location::class, 'showroom_location_id');
    }
}
