<?php
namespace App\Jobs;

use App\Imports\StudentsImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
{
    if ($this->filePath && file_exists($this->filePath)) {
        $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
        
        if (!empty($extension)) {
            // Path has an extension, try to use it
            Excel::import(new StudentsImport, $this->filePath);
        } else {
            // You'll need to know what type of file it is and use the appropriate constant
            Excel::import(new StudentsImport, $this->filePath, null, \Maatwebsite\Excel\Excel::XLSX);
        }
    } else {
        \Log::error('File path not valid for import: ' . $this->filePath);
    }
}
}

