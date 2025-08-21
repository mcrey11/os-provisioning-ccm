<?php

namespace App\Traits;

use Modules\ProvBase\Entities\Address;

trait HasAddress
{
    protected static function bootHasAddress()
    {
        static::created(function ($model) {
            if ($model->addressData) {
                $address = $model->address()->create($model->addressData);
                // Set the address_id field to the newly created address ID
                $model->address_id = $address->id;
                // Update the model without triggering events to avoid infinite loop
                $model->updateQuietly(['address_id' => $address->id]);
                // Clear the address data after processing
                $model->addressData = [];
            }
        });

        static::saving(function ($model) {
            if ($model->addressData) {
                // If we have an existing address_id, update that address
                if ($model->address_id) {
                    $address = $model->address;
                    if ($address) {
                        $address->update($model->addressData);
                    }
                } else {
                    // If no address_id, create a new address
                    $address = $model->address()->create($model->addressData);
                    $model->address_id = $address->id;
                    $model->updateQuietly(['address_id' => $address->id]);
                }
                // Clear the address data after processing
                $model->addressData = [];
            }
        });
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Refresh address data from database
     */
    public function refreshAddressData()
    {
        if ($this->address_id && $this->address) {
            $this->address->refresh();
        }
    }

    // Container for address data that gets passed during save/create
    protected $addressData = [];

    // Mutators that intercept data
    public function setStreetAttribute($value)
    {
        $this->addressData['street'] = $value;
    }

    public function setHouseNumberAttribute($value)
    {
        $this->addressData['house_number'] = $value;
    }

    public function setZipAttribute($value)
    {
        $this->addressData['zip'] = $value;
    }

    public function setCityAttribute($value)
    {
        $this->addressData['city'] = $value;
    }

    // Accessors – so the fields appear as if they're directly present
    public function getStreetAttribute()
    {
        return $this->address?->street;
    }

    public function getHouseNumberAttribute()
    {
        return $this->address?->house_number;
    }

    public function getZipAttribute()
    {
        return $this->address?->zip;
    }

    public function getCityAttribute()
    {
        return $this->address?->city;
    }
}
