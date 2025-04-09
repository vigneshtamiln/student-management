<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Jobs\ImportStudentsJob;
use App\Jobs\ExportStudentsJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::paginate(20);
        $exportFile = Cache::get('export_file');
        $exportStatus = Cache::get('export_status', 'not_started');
    
        return view('students.index', compact('students', 'exportFile', 'exportStatus'));
    }
    

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename);
        ImportStudentsJob::dispatch(storage_path("app/{$path}"));

        return back()->with('success', 'Import job dispatched successfully!');
    }


    public function export()
    {
        if (Cache::has('export_file')) {
            return redirect()
                ->back()
                ->with('message', 'An export file is already available. You can download it now.');
        }
        
        ExportStudentsJob::dispatch();
        
        return redirect()
            ->back()
            ->with('message', 'Export process started. Please wait a moment and refresh the page to check if it\'s ready.');
    }

    
    public function download()
    {
        $filename = Cache::get('export_file');
    
        if (!$filename) {
            return back()->with('error', 'Export file not ready yet. Please try again in a moment.');
        }
    
        $filePath = storage_path("app/{$filename}");
        
        if (!file_exists($filePath)) {
            Cache::forget('export_file');
            return back()->with('error', 'Export file not found. Please try exporting again.');
        }
        
        $fileSize = filesize($filePath);
        
        if ($fileSize === 0) {
            Cache::forget('export_file');
            return back()->with('error', 'Export file is empty. Please try exporting again.');
        }
        
        try {
            $downloadName = basename($filename);
            
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Content-Length: ' . $fileSize);
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            readfile($filePath);
            exit;
            
        } catch (\Exception $e) {
            return back()->with('error', 'Download failed: ' . $e->getMessage());
        }
    }

    public function checkExportStatus()
    {
        $status = Cache::get('export_status', 'not_started');
        $file = Cache::get('export_file');
        
        
        $response = [
            'status' => $status,
            'file' => $file
        ];

        if ($status === 'complete' && $file && Storage::exists($file)) {
            $response['downloadUrl'] = route('students.download');
        }
        
        return response()->json($response);
    }
    
    public function clearExportCache()
    {
        // Get the filename from cache
        $filename = Cache::get('export_file');
        
        // If the file exists in storage, delete it
        if ($filename && Storage::exists($filename)) {
            Storage::delete($filename);
        }
        
        // Forget the cache keys
        Cache::forget('export_file');
        Cache::forget('export_status');
        
        return redirect()->back()->with('message', 'Export cache and file cleared successfully.');
    }
}

