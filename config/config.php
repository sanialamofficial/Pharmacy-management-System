<?php
declare(strict_types=1);

return [
    'app_name' => 'Dispensa',
    'pharmacy_name' => 'Greenleaf Community Pharmacy',
    'pharmacy_address' => '14 Hollis Market Road, Unit 3',
    'pharmacy_license' => 'PH-LIC 20931-B',
    'pharmacy_phone' => '+1 (555) 234-8910',
    'currency_symbol' => '$',
    'low_stock_threshold' => 5,
    'expiry_warning_days' => 30,
    'db_path' => __DIR__ . '/../database/dispensa.sqlite',
    'schema_path' => __DIR__ . '/../database/schema.sql',
];
