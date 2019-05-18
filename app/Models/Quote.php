<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Quote extends Model
{
    protected $guarded = ['id'];

    public function job()
    {
        return $this->hasOne(Job::class);
    }

    public function snapshots()
    {
        return $this->hasMany(Snapshot::class);
    }

    public function cabinets()
    {
        return $this->hasMany(QuoteCabinet::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function answers()
    {
        return $this->hasMany(QuoteQuestionAnswer::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function granites()
    {
        return $this->hasMany(QuoteGranite::class);
    }

    public function addons()
    {
        return $this->hasMany(QuoteAddon::class);
    }

    /**
     * These are the stored brand models and size of the appliances.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appliances()
    {
        return $this->hasMany(QuoteAppliance::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responsibilities()
    {
        return $this->hasMany(QuoteResponsibility::class);
    }

    /**
     * Quote Tile configurations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tiles()
    {
        return $this->hasMany(QuoteTile::class);
    }

    public function getApplianceClass()
    {
        if ($this->appliances->count() == 0) return null;
        $class = 'text-success';
        foreach ($this->appliances as $appliance)
        {

            if (!$appliance->model || !$appliance->brand)
            {
                $class = 'text-danger';
            }
        }
        return $class;
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function type()
    {
        return $this->belongsTo(QuoteType::class, 'quote_type_id');
    }  
}
