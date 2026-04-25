<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class DatabaseController extends Controller
{
    // Backup the database (creates .sql file only)
    public function backup()
    {
        $database = env('DB_DATABASE', 'it12');
        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        
        $timestamp = date('Y-m-d_H-i-s');
        $sqlFile = storage_path("app/backups/backup_{$timestamp}.sql");
        
        // Create backups directory if it doesn't exist
        if (!File::exists(storage_path('app/backups'))) {
            File::makeDirectory(storage_path('app/backups'), 0755, true);
        }
        
        // Backup database
        $passwordOption = $pass === '' ? '' : "--password={$pass}";
        $command = '"C:\xampp\mysql\bin\mysqldump.exe" -h '.$host.' -u '.$user.' '.$passwordOption.' --add-drop-table '.$database;
        $command .= ' > "'.$sqlFile.'"';
        
        exec($command, $output, $returnVar);
        
        if($returnVar !== 0) {
            return back()->with('error', 'Backup failed. Make sure mysqldump is in your system PATH. Error code: '.$returnVar);
        }
         
        return response()->download($sqlFile)->deleteFileAfterSend(true);
    }

    // Restore the database
     public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|mimes:sql,txt',
        ]);
        
        $file = $request->file('backup_file');
        
        try {
            // Count images before deletion
            $imagesPath = public_path('images/products');
            $imagesDeleted = 0;
            if (File::exists($imagesPath)) {
                $imagesDeleted = count(File::allFiles($imagesPath));
            }
            
            // FIRST: Delete all current images
            $this->deleteAllCurrentImages();
            
            // SECOND: Restore the database
            $this->restoreDatabase($file);
            
            // Prepare success/warning message
            $message = 'Database restored successfully!';
            
            if ($imagesDeleted > 0) {
                $message .= " WARNING: $imagesDeleted product images were deleted.";
                $message .= " Images referenced in the restored database may not exist.";
                            return back()->with('warning', $message);
            }
            
                    return back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->route('account.settings')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }
    
    // Delete all current product images
    private function deleteAllCurrentImages()
    {
        $imagesPath = public_path('images/products');
        
        if (File::exists($imagesPath)) {
            // Get list of all files in the directory
            $files = File::allFiles($imagesPath);
            
            foreach ($files as $file) {
                try {
                    File::delete($file);
                } catch (\Exception $e) {
                    // Log error but continue
                    Log::error('Failed to delete image: ' . $file->getPathname());
                }
            }
            
            // Also delete empty subdirectories if any
            $directories = File::directories($imagesPath);
            foreach ($directories as $dir) {
                if (count(File::allFiles($dir)) === 0) {
                    File::deleteDirectory($dir);
                }
            }
        }
    }
    
    // Restore database from SQL file
    private function restoreDatabase($file)
    {
        $tempPath = storage_path('app/temp_restore.sql');
        
        // Move uploaded file
        $file->move(storage_path('app'), 'temp_restore.sql');
        
        $database = env('DB_DATABASE', 'it12');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        
        $passwordOption = $password === '' ? '' : "--password={$password}";
        
        $command = '"C:\xampp\mysql\bin\mysql.exe" -h '.$host.' -u '.$username.' '.$passwordOption.' '.$database.' < "'.$tempPath.'"';
        
        exec($command . ' 2>&1', $output, $returnVar);
        
        // Clean up temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        
        if($returnVar !== 0) {
            $error = implode("\n", $output);
            throw new \Exception('Database restore failed: ' . $error);
        }
    }
}