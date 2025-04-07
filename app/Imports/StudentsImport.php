<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentsImport implements ToModel
{
    public function model(array $row)
    {
        return new Student([
            'name' => $row[0],
            'email' => $row[1],
            'phone' => $row[2],
            'course' => $row[3],
        ]);
    }
}
