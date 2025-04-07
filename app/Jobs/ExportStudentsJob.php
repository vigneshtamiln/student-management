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

class ExportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle()
    {
        $filename = 'exports/students_' . now()->format('Ymd_His') . '.xlsx';
    
        Excel::store(new StudentsExport, $filename, 'public');
    
        // Save filename to session cache for this user
        Cache::put('export_file_' . $filename, now()->addMinutes(10));
    }
    
   
}
