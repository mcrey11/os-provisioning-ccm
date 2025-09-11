<?php

use Modules\Marketing\Entities\Consent;

return [
    'name' => 'Marketing',
    // 'link' => 'Marketing.index',
    'MenuItems' => [
        'Consent' => [
            'link'	=> 'Consent.index',
            'icon'	=> Consent::$icon,
            'class' => Consent::class,
        ],
    ],
];
