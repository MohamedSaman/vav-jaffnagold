<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting database timezone correction (subtracting 5 hours 30 mins) for records before 2026-06-23 06:00:00...\n\n";

// Get all tables
$tables = DB::select('SHOW TABLES');
$dbName = DB::connection()->getDatabaseName();
$tableKey = "Tables_in_{$dbName}";

$tablesToSkip = ['migrations', 'failed_jobs', 'personal_access_tokens', 'sessions', 'password_resets', 'password_reset_tokens'];
$columnsToSkip = ['payment_date'];

$totalUpdated = 0;

foreach ($tables as $tableInfo) {
    $table = (array)$tableInfo;
    $tableName = array_values($table)[0];

    if (in_array($tableName, $tablesToSkip)) {
        continue;
    }

    // Get columns for table
    $columns = DB::select("SHOW COLUMNS FROM `{$tableName}`");
    
    $dateColumns = [];
    foreach ($columns as $column) {
        $type = strtolower($column->Type);
        $field = $column->Field;
        
        if (in_array($field, $columnsToSkip)) {
            continue;
        }

        if (strpos($type, 'timestamp') !== false || strpos($type, 'datetime') !== false) {
            $dateColumns[] = $field;
        }
    }

    if (empty($dateColumns)) {
        continue;
    }

    echo "Processing table: {$tableName}\n";

    $tableUpdated = 0;
    foreach ($dateColumns as $column) {
        // Only update if the date in that column is strictly before June 23rd 06:00:00
        $affected = DB::table($tableName)
            ->whereNotNull($column)
            ->where($column, '<', '2026-06-23 06:00:00') 
            ->update([
                $column => DB::raw("DATE_SUB(`{$column}`, INTERVAL 5 HOUR) - INTERVAL 30 MINUTE")
            ]);

        if ($affected > 0) {
            echo " - Updated {$affected} rows for column '{$column}'\n";
            $tableUpdated += $affected;
            $totalUpdated += $affected;
        }
    }

    echo "Table {$tableName}: {$tableUpdated} rows updated.\n\n";
}

echo "Correction complete! Total values updated: {$totalUpdated}\n";
