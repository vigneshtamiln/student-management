<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExportStudentsJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
Route::get('/download-export', [StudentController::class, 'download'])->name('students.download');
Route::get('/export-students', [StudentController::class, 'export'])->name('students.export');
Route::get('/check-export-status', [StudentController::class, 'checkExportStatus'])->name('students.checkExportStatus');
Route::post('/delete-export', [StudentController::class, 'deleteExport'])->name('students.deleteExport');
Route::get('/clear-export-cache', [StudentController::class, 'clearExportCache'])->name('students.clearExportCache');
// Test route to verify queue is working
Route::get('/test-queue', function() {
    Log::info('Testing queue...');
    
    // Dispatch the job synchronously for testing
    $job = new ExportStudentsJob();
    $job->handle();
    
    return 'Queue test completed. Check the logs for details.';
});

// Test route to verify storage is working
Route::get('/test-storage', function() {
    Log::info('Testing storage...');
    
    // Create a test file
    $filename = 'test.txt';
    $content = 'This is a test file created at ' . now();
    
    // Save the file
    Storage::put($filename, $content);
    
    // Check if the file exists
    $exists = Storage::exists($filename);
    Log::info("File exists: " . ($exists ? 'Yes' : 'No'));
    
    // Get the file path
    $path = Storage::path($filename);
    Log::info("File path: {$path}");
    
    // Read the file
    $readContent = Storage::get($filename);
    Log::info("File content: {$readContent}");
    
    // Delete the file
    Storage::delete($filename);
    
    return 'Storage test completed. Check the logs for details.';
});

// Debug route to check cache
Route::get('/debug-cache', function() {
    $exportFile = Cache::get('export_file');
    
    return [
        'export_file' => $exportFile,
        'all_cache_keys' => Cache::get('export_file') ? ['export_file'] : [],
    ];
});

// Direct export route (no queue)
Route::get('/direct-export', function() {
    Log::info('Starting direct export...');
    
    try {
        // Generate a unique filename
        $filename = 'exports/students_' . now()->format('Ymd_His') . '.xlsx';
        Log::info("Generated filename: {$filename}");
        
        // Make sure the exports directory exists
        if (!Storage::exists('exports')) {
            Log::info('Creating exports directory...');
            Storage::makeDirectory('exports');
        }
        
        // Store the file in the storage/app directory
        Log::info('Storing Excel file...');
        Excel::store(new \App\Exports\StudentsExport, $filename);
        
        // Verify the file was created
        if (!Storage::exists($filename)) {
            throw new \Exception("Export file was not created successfully");
        }
        
        Log::info("File created successfully at: " . Storage::path($filename));
        
        // Save the filename to cache
        Log::info('Saving filename to cache with key: export_file');
        Cache::put('export_file', $filename, now()->addMinutes(10));
        
        // Verify the cache was set
        $cachedFile = Cache::get('export_file');
        Log::info("Cache verification - export_file: " . ($cachedFile ? $cachedFile : 'not found'));
        
        return redirect()->route('students.index')->with('message', 'Export completed successfully!');
    } catch (\Exception $e) {
        Log::error("Export failed: " . $e->getMessage());
        return redirect()->route('students.index')->with('error', 'Export failed: ' . $e->getMessage());
    }
});
