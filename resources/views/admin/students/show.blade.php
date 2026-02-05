@extends('layouts.app')

@section('title', $student->full_name)

@push('styles')
    <style>
        .profile-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .profile-header {
            display: flex;
            align-items: flex-start;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-initial-large {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 3rem;
            border: 3px solid #e9ecef;
        }

        .info-item {
            margin-bottom: 20px;
        }

        .info-label {
            color: #6c757d;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 500;
            font-family: MontserratLight, sans-serif;
            font-size: 16px;
            color: #252525;
        }

        .stat-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0 5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .class-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .class-card:hover {
            box-shadow: 0 3px 10px rgba(0,123,255,0.1);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .action-button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .role-badge {
            padding: 4px 8px;
        }
    </style>
@endpush

@section('content')
    <div class="content-wrap">
        <div class="main">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-lg-8 p-r-0 title-margin-right">
                        <div class="page-header">
                            <div class="page-title">
                                <h1>{{ $student->full_name }}</h1>
                            </div>
                        </div>
                        <span>View complete student information</span>
                    </div>
                    <div class="col-lg-4 p-l-0 title-margin-left">
                        <div class="page-header">
                            <div class="page-title">
                                <ol class="breadcrumb text-right">
                                    <li><a href="{{ route('admin.students.index') }}">Students</a></li>
                                    <li class="active">{{ $student->full_name }}</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="main-content">
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="action-button-group" style="margin-top: 10px; margin-bottom: 10px;">
                                <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary">
                                    <i class="ti-pencil-alt"></i> Edit Student
                                </a>
                              
                                <form action="{{ route('admin.students.destroy', $student) }}" 
                                    method="POST" 
                                    style="display: inline-block;"
                                    id="deleteStudentForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti-trash"></i> Delete Student
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Student Info Card -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="user-profile">
                                        <div style="display: flex; align-items: center;">
                                            <!-- Profile Photo -->
                                            <div class="user-photo m-b-30">
                                                @if($student->profile_photo)
                                                    <img src="{{ asset('storage/' . $student->profile_photo) }}" alt="{{ $student->full_name }}" class="img-responsive" width="150" height="150" style="border-radius: 12px; object-fit: cover;">
                                                @else
                                                    <div class="profile-initial-large bg-primary text-white">
                                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="user-profile-name">{{ $student->full_name }}</div>
                                                <div class="info-item" style="padding: 0 15px">
                                                    <div class="info-label">Status</div>
                                                    <div class="info-value">
                                                        @if($student->status === 'active')
                                                            <span class="badge badge-success role-badge">Active</span>
                                                        @elseif($student->status === 'inactive')
                                                            <span class="badge badge-secondary role-badge">Inactive</span>
                                                        @elseif($student->status === 'graduated')
                                                            <span class="badge badge-info role-badge">Graduated</span>
                                                        @else
                                                            <span class="badge badge-danger role-badge">Withdrawn</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                               
                                        <div class="custom-tab user-profile-tab">
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li role="presentation" class="active"><a href="#1" aria-controls="1" role="tab" data-toggle="tab">Student information</a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <div role="tabpanel" class="tab-pane active" id="1">
                                                    <div class="contact-information">
                                                        
                                                        <div class="phone-content">
                                                            <span class="contact-title">Full Name:</span>
                                                            <span class="phone-number">{{ $student->full_name }}</span>
                                                        </div>
                                                        <div class="address-content">
                                                            <span class="contact-title">Date of Birth:</span>
                                                            <span class="info-value">{{ $student->date_of_birth->format('d F Y') }}</span>
                                                            <small class="text-muted d-block">({{ $student->date_of_birth->age }} years old)</small>
                                                        </div>
                                                        <div class="email-content">
                                                            <span class="contact-title">Enrolment Date:</span>
                                                            <span class="contact-email">{{ $student->enrollment_date->format('d F Y') }}</span>
                                                        </div>
                                                        <div class="website-content">
                                                            <span class="contact-title">Parent:</span>
                                                            <span class="contact-website">{{ $student->parent->name }}
                                                            <small class="text-muted d-block">{{ $student->parent->email }}</small></span>
                                                        </div>
                                                        <div class="skype-content">
                                                            <span class="contact-title">Emergency Contact:</span>
                                                            <span class="contact-skype">
                                                                {{ $student->emergency_contact ?? 'N/A' }}
                                                                @if($student->emergency_phone)
                                                                    <small class="text-muted d-block">{{ $student->emergency_phone }}</small>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        @if($student->medical_info)
                                                        <div class="birthday-content">
                                                            <span class="contact-title">Medical Information:</span>
                                                            <span class="birth-date">{{ $student->medical_info }} </span>
                                                        </div>
                                                        @endif

                                                    </div>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->
                     
                    </div>
                    <!-- /# row -->

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="" style="display: flex; justify-content: center; align-items: center;">
                                        <div class="stat-icon bg-primary text-white">
                                            <i class="ti-book"></i>
                                        </div>
                                        <div style="margin-left: 15px; flex: 1;">
                                            <div class="stat-text">Enroled Classes</div>
                                            <div class="stat-digit">{{ $stats['enrolled_classes'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="" style="display: flex; justify-content: center; align-items: center;">
                                        <div class="stat-icon bg-success text-white">
                                            <i class="ti-check"></i>
                                        </div>
                                        <div style="margin-left: 15px; flex: 1;">
                                            <div class="stat-text">Attendance Rate</div>
                                            <div class="stat-digit">{{ number_format($stats['attendance_rate'], 1) }}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="" style="display: flex; justify-content: center; align-items: center;">
                                        <div class="stat-icon bg-warning text-white">
                                            <i class="ti-write"></i>
                                        </div>
                                        <div style="margin-left: 15px; flex: 1;">
                                            <div class="stat-text">Total Homework</div>
                                            <div class="stat-digit">{{ $stats['total_homework'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card">
                                <div class="stat-widget-one">
                                    <div class="" style="display: flex; justify-content: center; align-items: center;">
                                        <div class="stat-icon bg-info text-white">
                                            <i class="ti-clipboard"></i>
                                        </div>
                                        <div style="margin-left: 15px; flex: 1;">
                                            <div class="stat-text">Progress Reports</div>
                                            <div class="stat-digit">{{ $stats['graded_homework'] }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card alert">
                                <div class="card-body">
                                    <div class="custom-tab">
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li role="presentation" class="active"><a href="#classes" aria-controls="classes" role="tab" data-toggle="tab"><i class="ti-book"></i> Enroled Classes</a></li>
                                            <li role="presentation"><a href="#attendance" aria-controls="attendance" role="tab" data-toggle="tab"><i class="ti-check-box"></i> Attendance History</a></li>
                                            <li role="presentation"><a href="#homework" aria-controls="homework" role="tab" data-toggle="tab"> <i class="ti-write"></i> Homework</a></li>
                                            <li role="presentation"><a href="#progress" aria-controls="progress" role="tab" data-toggle="tab"><i class="ti-stats-up"></i> Progress Reports</a></li>
                                        </ul>
                                        <div class="tab-content">
                                            <!-- Classes Tab -->
                                            <div role="tabpanel" class="tab-pane active" id="classes">
                                                @if($student->classes->count() > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 15px;">
                                                        @foreach($student->classes as $class)
                                                        <div class="class-card">
                                                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                                                <div style="flex: 1;">
                                                                    <h4 style="margin-bottom: 10px;">{{ $class->name }}</h4>
                                                                    <p class="text-muted mb-1"><i class="ti-user"></i> Teacher: {{ $class->teacher->name }}</p>
                                                                    <p class="text-muted mb-0"><i class="ti-location-pin"></i> Room: {{ $class->room_number ?? 'N/A' }}</p>
                                                                </div>
                                                                <div style="text-align: right;">
                                                                    @if($class->pivot->status === 'active')
                                                                        <span class="badge badge-success role-badge">Active</span>
                                                                    @else
                                                                        <span class="badge badge-secondary role-badge">{{ ucfirst($class->pivot->status) }}</span>
                                                                    @endif
                                                                    <small class="text-muted d-block mt-2">
                                                                        Enroled: {{ \Carbon\Carbon::parse($class->pivot->enrollment_date)->format('d M Y') }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-book"></i>
                                                        <h4>No Classes Enroled</h4>
                                                        <p>This student is not enroled in any classes yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Attendance Tab -->
                                            <div role="tabpanel" class="tab-pane" id="attendance">
                                                @if($student->attendance->count() > 0)
                                                    <div class="table-responsive">
                                                        <table class="table table-hover">
                                                            <thead>
                                                                <tr>
                                                                    <th>Date</th>
                                                                    <th>Class</th>
                                                                    <th>Status</th>
                                                                    <th>Marked By</th>
                                                                    <th>Notes</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($student->attendance->sortByDesc('date')->take(20) as $record)
                                                                <tr>
                                                                    <td>{{ $record->date->format('d M Y') }}</td>
                                                                    <td>{{ $record->class->name }}</td>
                                                                    <td>
                                                                        @if($record->status === 'present')
                                                                            <span class="badge badge-success role-badge">Present</span>
                                                                        @elseif($record->status === 'absent')
                                                                            <span class="badge badge-danger role-badge">Absent</span>
                                                                        @elseif($record->status === 'late')
                                                                            <span class="badge badge-warning role-badge">Late</span>
                                                                        @else
                                                                            <span class="badge badge-secondary role-badge">{{ ucfirst($record->status) }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $record->markedBy->name }}</td>
                                                                    <td>{{ $record->notes ?? '-' }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-check-box"></i>
                                                        <h4>No Attendance Records</h4>
                                                        <p>No attendance has been recorded for this student yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <!-- Homework Tab -->
                                            <div role="tabpanel" class="tab-pane" id="homework">
                                                 @if($student->homeworkSubmissions->count() > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 15px;">
                                                        @foreach($student->homeworkSubmissions->sortByDesc('created_at')->take(20) as $submission)
                                                        <div class="class-card">
                                                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                                                <h4 style="margin: 0;">{{ $submission->homeworkAssignment->title }}</h4>
                                                                @if($submission->status === 'graded')
                                                                    <span class="badge badge-success role-badge">Graded</span>
                                                                @elseif($submission->status === 'submitted')
                                                                    <span class="badge badge-info role-badge">Submitted</span>
                                                                @elseif($submission->status === 'late')
                                                                    <span class="badge badge-danger role-badge">Late</span>
                                                                @else
                                                                    <span class="badge badge-warning role-badge">Pending</span>
                                                                @endif
                                                            </div>
                                                            <p class="text-muted mb-2"><i class="ti-book"></i> {{ $submission->homeworkAssignment->class->name }}</p>
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <small class="text-muted">
                                                                    <i class="ti-calendar"></i> Due: {{ $submission->homeworkAssignment->due_date->format('d M Y') }}
                                                                </small>
                                                                @if($submission->status === 'graded')
                                                                    <span style="font-weight: 500;">Grade: {{ $submission->grade }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-write"></i>
                                                        <h4>No Homework Submissions</h4>
                                                        <p>No homework submissions recorded for this student yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div role="tabpanel" class="tab-pane" id="progress">
                                                 @if($student->progressNotes->count() > 0)
                                                    <div style="display: flex; flex-direction: column; gap: 15px;">
                                                        @foreach($student->progressNotes->sortByDesc('created_at')->take(20) as $note)
                                                        <div class="class-card">
                                                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                                                <h4 style="margin: 0;">{{ $note->progressSheet->class->name }}</h4>
                                                                @if($note->performance)
                                                                    @if($note->performance === 'excellent')
                                                                        <span class="badge badge-success role-badge">Excellent</span>
                                                                    @elseif($note->performance === 'good')
                                                                        <span class="badge badge-info role-badge">Good</span>
                                                                    @elseif($note->performance === 'average')
                                                                        <span class="badge badge-warning role-badge">Average</span>
                                                                    @elseif($note->performance === 'struggling')
                                                                        <span class="badge badge-danger role-badge">Struggling</span>
                                                                    @else
                                                                        <span class="badge badge-secondary role-badge">{{ ucfirst($note->performance) }}</span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                            <p class="text-muted mb-2"><i class="ti-bookmark"></i> Topic: {{ $note->progressSheet->topic }}</p>
                                                            @if($note->notes)
                                                                <p style="font-style: italic; margin-bottom: 15px;">{{ $note->notes }}</p>
                                                            @endif
                                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                                <small class="text-muted">
                                                                    <i class="ti-calendar"></i> {{ $note->progressSheet->date->format('d M Y') }}
                                                                </small>
                                                                <small class="text-muted">
                                                                    <i class="ti-user"></i> Teacher: {{ $note->progressSheet->teacher->name }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="empty-state">
                                                        <i class="ti-clipboard"></i>
                                                        <h4>No Progress Reports</h4>
                                                        <p>No progress reports have been created for this student yet.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /# column -->
                    </div>

                    <!-- Footer -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="footer">
                                <p>MLC Classroom - Student Details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Handle student deletion with SweetAlert
            $('#deleteStudentForm').on('submit', function(e) {
                e.preventDefault(); // Prevent default submission
                
                const form = this;
                const studentName = "{{ $student->first_name }} {{ $student->last_name }}";
                const classCount = {{ $student->classes->count() ?? 0 }}; // Number of enroled classes
                
                let warningText = "You want to delete student '" + studentName + "'?\n\n";
                
                if (classCount > 0) {
                    warningText += "⚠️ This student is currently enroled in " + classCount + " class(es).\n\n";
                }
                
                warningText += "This action cannot be undone!";
                
                swal({
                    title: "Are you sure?",
                    text: warningText,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, delete student!",
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm){
                    if (isConfirm) {
                        form.submit(); // Submit the form
                    }
                });
                
                return false;
            });
        });
    </script>
@endpush