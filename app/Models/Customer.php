<?php
namespace FK3\Models;

use Vocalogic\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = ['none'];

    /**
     * A customer has (is linked to) one user.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class);
    }

    /**
     * A customer has many leads.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function getFullAddressAttribute()
    {
        return $this->address . " " . $this->city. ", " . $this->state . " " . $this->zip;
    }

    /**
     * A customer has (is linked to) many contacts.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function quotes()
    {
        return $this->hasManyThrough(Quote::class, Lead::class);
    }
}
