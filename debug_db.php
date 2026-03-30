<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
     Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Schema;

foreach(['recoveries', 'customer_recoveries', 'vendor_payments', 'add_expenses'] as $t) {
    echo "$t COLUMNS:\n";
    print_r(Schema::getColumnListing($t));
}
