<!DOCTYPE html>
<html>
<head>
    <title>Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

    @if(session('export_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('export_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('import_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('import_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('import_results'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5>Import Results:</h5>
            <p>Successfully imported: {{ session('import_results')['success'] }} records</p>
            <p>Failed records: {{ session('import_results')['errors'] }}</p>
            
            @if(session('import_results')['errors'] > 0)
                <div class="mt-2">
                    <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#errorDetails">
                        Show Error Details
                    </button>
                    <div class="collapse mt-2" id="errorDetails">
                        <div class="card card-body">
                            <ul class="mb-0">
                                @foreach(session('import_results')['error_details'] as $error)
                                    <li>Row {{ $error->row() }}: {{ $error->errors()[0] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
            
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $exportFile = Cache::get('export_file');
        // For debugging
        Log::info('Export file from cache: ' . ($exportFile ? $exportFile : 'not found'));
    @endphp
    {{-- @dd($exportFile) --}}
    @if($exportStatus === 'in_progress')
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin"></i> Export is in progress. Please wait...
        </div>
    @elseif($exportStatus === 'complete' && $exportFile)
        <div class="alert alert-success">
            <i class="fas fa-check"></i> Export is ready!
            <a href="{{ route('students.download') }}" class="btn btn-success btn-sm ms-2">
                <i class="bi bi-download"></i> Download Excel
            </a>
        </div>
    @elseif($exportStatus === 'failed')
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> Export failed. Please try again.
        </div>
    @endif

    {{-- Import/Export Section --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">📊 Data Management</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-center">
                @csrf
                <div class="col-md-4">
                    <label for="file" class="form-label">Select Excel/CSV File</label>
                    <input type="file" name="file" id="file" class="form-control" required>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-upload"></i> Import Students
                    </button>
                    <a href="{{ route('students.export') }}" class="btn btn-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Export to Excel
                    </a>
                    <a href="/sample/students.xlsx" class="btn btn-outline-secondary" download>
                        <i class="bi bi-download"></i> Download Sample File
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Student Table --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 Student List</h5>
            <span class="badge bg-primary">{{ $students->total() }} Students</span>
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
                            <td>{{ $student->phone ?? 'N/A' }}</td>
                            <td>{{ $student->course ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="bi bi-info-circle me-2"></i> No students found. Import some data to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $students->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@push('scripts')
<script>
    // Function to check export status
    function checkExportStatus() {
        if ('{{ $exportStatus }}' === 'in_progress') {
            fetch('/check-export-status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'complete' && data.downloadUrl) {
                        // Auto-download the file
                        window.location.href = data.downloadUrl;
                        // Reload the page after a short delay to show the success message
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (data.status === 'failed') {
                        // Reload the page to show the error message
                        window.location.reload();
                    } else {
                        // Check again in 2 seconds
                        setTimeout(checkExportStatus, 2000);
                    }
                })
                .catch(error => {
                    console.error('Error checking export status:', error);
                    // Check again in 5 seconds if there's an error
                    setTimeout(checkExportStatus, 5000);
                });
        }
    }

    // Start checking if export is in progress
    checkExportStatus();
</script>
@endpush
</body>
</html>