<?php
/**
 * Created by PhpStorm.
 * User: chris
 * Date: 2/8/18
 * Time: 6:05 PM
 */

namespace FK3\Models;


use Vocalogic\Eloquent\Model;

class Fft extends Model
{
    protected $guarded = ['id'];

    public function getDates()
    {
      return ['created_at', 'updated_at', 'schedule_start', 'pre_schedule_start', 'signed'];
    }
    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function thread_notes()
    {
        return $this->hasMany(FFTNote::class, 'fft_id');
    }
    public function assigned()
    {
      return $this->belongsTo(User::class, 'user_id');
    }
    public function preassigned()
    {
      return $this->belongsTo(User::class, 'pre_assigned');
    }

    public function job()
    {
      return $this->belongsTo(Job::class);
    }
    public function customer()
    {
      return $this->belongsTo(Customer::class);
    }

    public static function getPaymentCategories()
    {
        return [
                'NO see notes',
                'Run Wells Fargo',
                'Run Credit Card on File',
                'Credit card already run by FFT contractor',
                'Collected Check',
                'Run new card see notes for number',
                'Other See Notes'
              ];
    }
}
