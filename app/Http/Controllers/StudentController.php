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
        $exportFile = Cache::get('export_file_');
    
        return view('students.index', compact('students', 'exportFile'));
    }
    

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        
        // Save the file in a persistent location
        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('imports', $filename); // stored in storage/app/imports/
        // Dispatch job with the full path
        ImportStudentsJob::dispatch(storage_path("app/{$path}"));

        return back()->with('success', 'Import job dispatched successfully!');
    }


    public function export()
    {
        ExportStudentsJob::dispatch();
        return redirect()
            ->back()
            ->with('export', 'Export process started. You will be notified when it\'s ready.');
    }

    
    public function download()
    {
        $filename = session('export_filename');
    
        if (!$filename) {
            return back()->with('error', 'Export file not ready yet.');
        }
    
        $filePath = storage_path("app/exports/{$filename}");
    
        if (!file_exists($filePath)) {
            return back()->with('error', 'Export file not found.');
        }
    
        return response()->download($filePath);
    }
    
}

