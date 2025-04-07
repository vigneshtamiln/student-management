<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
Route::get('/download-export', [StudentController::class, 'download'])->name('students.download');
Route::get('/export-students', [StudentController::class, 'export'])->name('students.export');
