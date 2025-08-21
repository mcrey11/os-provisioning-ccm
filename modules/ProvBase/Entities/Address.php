<?php

namespace Modules\ProvBase\Entities;

use App\BaseModel;

/*
 * DEPRECATED: use app/Models/Address.php instead
 *
 * TODO: move to app/Models/Address.php
 *
 * Torsten Schmidt, 2025-08-21
 */
class Address extends BaseModel
{
    // The associated SQL table for this Model
    public $table = 'address';
}
