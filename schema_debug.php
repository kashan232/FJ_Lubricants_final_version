<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\Schema;

$tables = ['recoveries', 'customer_recoveries', 'vendor_payments', 'add_expenses', 'distributor_ledgers', 'customer_ledgers', 'vendor_ledgers'];
$out = "";
foreach($tables as $t) {
    $out .= "$t:\n" . implode(', ', Schema::getColumnListing($t)) . "\n\n";
}
file_put_contents('db_schema_check.txt', $out);
echo "Done";
