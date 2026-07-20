<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function downloadDatabaseBackup()
    {
        $dbName = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $backupPath = storage_path("app/backups");
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $fileName = "backup_" . date('Y_m_d_His') . ".sql";
        $filePath = $backupPath . '/' . $fileName;

        // Create backup using `mysqldump`
        $command = "mysqldump --user={$username} --password={$password} --host={$host} --port={$port} {$dbName} > {$filePath}";
        $result = null;
        $output = null;
        // exec($command . ' 2>&1', $output, $result);
        exec($command, $output, $result);

        // Debug output
        // dd([
        //     'command' => $command,
        //     'output' => $output,
        //     'result' => $result,
        // ]);


        if ($result !== 0 || !file_exists($filePath)) {
            return response()->json(['error' => 'Backup failed'], 500);
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    public function backup()
    {
        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');
        $output = "-- Backup of `$dbName` on " . now()->toDateTimeString() . "\n\n";

        foreach ($tables as $table) {
            $tableName = array_values((array)$table)[0];

            // Create Table
            $create = DB::select("SHOW CREATE TABLE `$tableName`")[0]->{"Create Table"};
            $output .= "DROP TABLE IF EXISTS `$tableName`;\n";
            $output .= "$create;\n\n";

            // Insert Data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    return is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                }, (array) $row);

                $output .= "INSERT INTO `$tableName` VALUES (" . implode(', ', $values) . ");\n";
            }

            $output .= "\n\n";
        }

        // Save the file
        $fileName = 'backup_' . date('Y_m_d_His') . '.sql';
        $filePath = storage_path("app/backups/$fileName");

        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents($filePath, $output);

        // Return response for download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
