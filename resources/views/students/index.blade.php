<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">🎓 Student Management</h2>
    </div>

    {{-- Flash messages --}}
    @if(session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Import/Export Section --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="col-auto">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">📥 Import Students</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('students.export') }}" class="btn btn-success">📤 Export to Excel</a>
                </div>
                <div class="col-auto">
                    <a href="/sample/students.xlsx" class="btn btn-outline-secondary" download>📝 Download Sample File</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Student Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">📋 Student List</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>{{ $student->phone }}</td>
                            <td>{{ $student->course }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No students found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $students->links('pagination::bootstrap-5') }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>