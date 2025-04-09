<?php

namespace App\Jobs;

use App\Exports\StudentsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {
        try {
            Log::info('Starting export job...');
            
            // Set status to in progress
            Cache::put('export_status', 'in_progress', now()->addMinutes(10));
            
            // Generate a unique filename with timestamp
            $filename = 'exports/students_' . now()->format('Ymd_His') . '.xlsx';
            Log::info("Generated filename: {$filename}");
            
            // Make sure the exports directory exists
            if (!Storage::exists('exports')) {
                Log::info('Creating exports directory...');
                Storage::makeDirectory('exports');
            }
            
            // Store the file in the storage/app directory
            Log::info('Storing Excel file...');
            Excel::store(new StudentsExport, $filename, 'local');
            
            // Verify the file was created
            if (!Storage::exists($filename)) {
                throw new \Exception("Export file was not created successfully");
            }
            
            // Verify file size
            $fileSize = Storage::size($filename);
            if ($fileSize === 0) {
                throw new \Exception("Export file is empty");
            }
            
            Log::info("File created successfully at: " . Storage::path($filename) . " with size: {$fileSize} bytes");
            
            // Save the filename to cache
            Log::info('Saving filename to cache with key: export_file');
            Cache::put('export_file', $filename, now()->addMinutes(10));
            
            // Set status to complete
            Cache::put('export_status', 'complete', now()->addMinutes(10));
            
            // Verify the cache was set
            $cachedFile = Cache::get('export_file');
            Log::info("Cache verification - export_file: " . ($cachedFile ? $cachedFile : 'not found'));
            
            Log::info("Export completed successfully: {$filename}");
        } catch (\Exception $e) {
            Log::error("Export failed: " . $e->getMessage());
            // Set status to failed
            Cache::put('export_status', 'failed', now()->addMinutes(10));
            Session::flash('export_error', 'Export failed: ' . $e->getMessage());
        }
    }
}
