<?php
namespace FK3\Models\Traits;

trait GroupPercentage
{
    /**
     * Get price field.
     *
     * It's usually called price, but it might be called 'amount'.
     *
     * Allow it to be overridden if the table using this trait uses something
     * else for 'price', and to indicate that, this table has set a
     * `priceField` property.
     *
     * @return string The price field.
     */
    public function getPriceField()
    {
        if (isset($this->priceField)) {
            return $this->priceField;
        }

        return 'price';
    }
    public function getMultiplier()
    {
        return isset($this->multiplierField) ? $this->multiplierField : 1;
    }
    /**
     * Get percentage options for use in form select dropdown.
     *
     * @return array An array of percentages for use in form.
     */
    public static function getPercentages()
    {
        $percents = array_map(function ($percent) {
            return $percent . '%';
        }, array_combine(range(100, 0), range(100, 0)));

        return $percents;
    }
    public function getPercentage()
    {
        $percentageField = isset($this->percentageField) ? $this->percentageField : 'percentage';

        return $this->{$percentageField};
    }
    /**
     * Get the percentage frugal gets of the price.
     *
     * accessor $record->frugal_percentage
     *
     */
    public function getFrugalPercentageAttribute()
    {
        $percentage = $this->getPercentage();

        return 100 - $percentage;
    }

    protected function getRealPrice()
    {
        $priceField = $this->getPriceField();
        $multiplier = $this->getMultiplier();
        $once = isset($this->once) && $this->once === true;

        if ($once) {
            return $this->{$priceField};
        }

        return $this->{$priceField} * $multiplier;
    }

    /**
     * Get the amount frugal gets from the total price.
     *
     * accessor $record->frugal_cut
     *
     */
    public function getFrugalCutAttribute()
    {
        $price = $this->getRealPrice();
        $percentage = $this->getPercentage();

        $frugalPercentage = 100 - $percentage;

        return $price * ($frugalPercentage / 100);
    }

    /**
     * Get the amount the designated group gets from the total price.
     *
     * accessor $record->group_cut
     *
     */
    public function getGroupCutAttribute()
    {
        $price = $this->getRealPrice();

        return $price * ($this->percentage / 100);
    }

    /**
     * Get the amount the 2nd designated group gets from the total price.
     *
     * accessor $record->second_group_cut
     *
     */
    public function getSecondGroupCutAttribute()
    {
        $price = $this->getRealPrice();
        $groupCut = $price * ($this->percentage / 100);
        $groupPercentage = 100 - $this->second_group_percentage;

        return $groupCut - (($groupPercentage / 100) * $groupCut);
    }
}
