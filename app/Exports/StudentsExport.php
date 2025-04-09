<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Student::all(); // Export all records
    }
    
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Course',
        ];
    }
    
    public function map($student): array
    {
        return [
            $student->name,
            $student->email,
            $student->phone ?? 'N/A',
            $student->course ?? 'N/A',
        ];
    }
}

