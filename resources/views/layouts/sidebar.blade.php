<div class="sidebar sidebar-hide-to-small sidebar-shrink sidebar-gestures">
    <div class="nano">
        <div class="nano-content">
            <ul>
                <li class="label">Main</li>
                
                {{-- Dashboard for all roles --}}
                @superadmin
                    <li><a href="{{ route('superadmin.dashboard') }}"><i class="ti-home"></i> Dashboard</a></li>
                @endsuperadmin
                @admin
                    <li><a href="{{ route('admin.dashboard') }}"><i class="ti-home"></i> Dashboard</a></li>
                @endadmin
                @teacher
                    <li><a href="{{ route('teacher.dashboard') }}"><i class="ti-home"></i> Dashboard</a></li>
                @endteacher
                @parent
                    <li><a href="{{ route('parent.dashboard') }}"><i class="ti-home"></i> Dashboard</a></li>
                @endparent

                {{-- SuperAdmin Only: System Management --}}
                @superadmin
                    <li class="label">System Management</li>
                    <li><a class="sidebar-sub-toggle"><i class="ti-settings"></i> System Management <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            <li><a href="{{ route('superadmin.users.index') }}">User Management</a></li>
                            <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
                            <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
                            <li><a href="{{ route('superadmin.activity-logs.index') }}">Activity Logs</a></li>
                            <li><a href="{{ route('superadmin.settings.index') }}">System Settings</a></li>
                        </ul>
                    </li>
                @endsuperadmin

                {{-- Admin Only: User Management --}}
                @admin
                    <li class="label">User Management</li>
                    <li><a class="sidebar-sub-toggle"><i class="ti-user"></i> Users <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            <li><a href="{{ route('admin.users.index') }}">All Users</a></li>
                            <li><a href="{{ route('admin.users.create') }}">Add New User</a></li>
                        </ul>
                    </li>
                @endadmin

                {{-- Academic Management (SuperAdmin, Admin, Teacher) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <li class="label">Academic Management</li>
                    
                    {{-- Students (SuperAdmin & Admin only) --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <li><a class="sidebar-sub-toggle"><i class="ti-book"></i> Students <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                            <ul>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.students.index' : 'admin.students.index') }}">All Students</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.students.create' : 'admin.students.create') }}">Add New Student</a></li>
                            </ul>
                        </li>
                    @endif

                    {{-- Classes (All three roles) --}}
                    <li><a class="sidebar-sub-toggle"><i class="ti-blackboard"></i> Classes <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @if(auth()->user()->isTeacher())
                                <li><a href="{{ route('teacher.classes.index') }}">My Classes</a></li>
                            @else
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.classes.index' : 'admin.classes.index') }}">All Classes</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.classes.create' : 'admin.classes.create') }}">Add New Class</a></li>
                            @endif
                        </ul>
                    </li>

                    {{-- Schedules (SuperAdmin & Admin) --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <li><a class="sidebar-sub-toggle"><i class="ti-calendar"></i> Schedules <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                            <ul>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.schedules.index' : 'admin.schedules.index') }}">All Schedules</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.schedules.create' : 'admin.schedules.create') }}">Create Schedule</a></li>
                            </ul>
                        </li>
                    @endif

                    {{-- Attendance (All three roles) --}}
                    <li><a class="sidebar-sub-toggle"><i class="ti-check-box"></i> Attendance <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @if(auth()->user()->isTeacher())
                                <li><a href="{{ route('teacher.attendance.index') }}">View Attendance</a></li>
                                <li><a href="{{ route('teacher.attendance.create') }}">Mark Attendance</a></li>
                            @else
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.attendance.index' : 'admin.attendance.index') }}">View Attendance</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.attendance.create' : 'admin.attendance.create') }}">Mark Attendance</a></li>
                            @endif
                        </ul>
                    </li>

                    {{-- Homework (All three roles) --}}
                    <li><a class="sidebar-sub-toggle"><i class="ti-write"></i> Homework <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @if(auth()->user()->isTeacher())
                                <li><a href="{{ route('teacher.homework.index') }}">My Homework</a></li>
                                <li><a href="{{ route('teacher.homework.create') }}">Create Homework</a></li>
                            @else
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.homework.index' : 'admin.homework.index') }}">All Homework</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.homework.create' : 'admin.homework.create') }}">Create Homework</a></li>
                            @endif
                        </ul>
                    </li>

                    {{-- Progress Tracking (All three roles) --}}
                    <li><a class="sidebar-sub-toggle"><i class="ti-stats-up"></i> Progress <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @if(auth()->user()->isTeacher())
                                <li><a href="{{ route('teacher.progress-sheets.index') }}">Progress Sheets</a></li>
                                <li><a href="{{ route('teacher.progress-sheets.create') }}">Create Progress Sheet</a></li>
                            @else
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.progress-sheets.index' : 'admin.progress-sheets.index') }}">Progress Sheets</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.progress-sheets.create' : 'admin.progress-sheets.create') }}">Create Progress Sheet</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Parent Section --}}
                @parent
                    <li class="label">My Children</li>
                    <li><a href="{{ route('parent.students.index') }}"><i class="ti-user"></i> View Children</a></li>
                    
                    <li class="label">Academic</li>
                    <li><a href="{{ route('parent.attendance.index') }}"><i class="ti-check-box"></i> Attendance</a></li>
                    <li><a href="{{ route('parent.homework.index') }}"><i class="ti-write"></i> Homework</a></li>
                    <li><a href="{{ route('parent.progress.index') }}"><i class="ti-stats-up"></i> Progress Reports</a></li>
                @endparent

                {{-- Resources (SuperAdmin, Admin, Teacher) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isTeacher())
                    <li class="label">Resources</li>
                    <li><a class="sidebar-sub-toggle"><i class="ti-folder"></i> Learning Resources <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @if(auth()->user()->isTeacher())
                                <li><a href="{{ route('teacher.resources.index') }}">My Resources</a></li>
                                <li><a href="{{ route('teacher.resources.create') }}">Upload Resource</a></li>
                            @else
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.resources.index' : 'admin.resources.index') }}">All Resources</a></li>
                                <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.resources.create' : 'admin.resources.create') }}">Upload Resource</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Resources for Parent (View Only) --}}
                @parent
                    <li class="label">Resources</li>
                    <li><a href="{{ route('parent.resources.index') }}"><i class="ti-folder"></i> Learning Resources</a></li>
                @endparent

                {{-- Communication (SuperAdmin & Admin) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <li class="label">Communication</li>
                    <li><a class="sidebar-sub-toggle"><i class="ti-bell"></i> Communication <span class="sidebar-collapse-icon ti-angle-down"></span></a>
                        <ul>
                            @superadmin
                                <li><a href="{{ route('superadmin.sms-config.index') }}">SMS Configuration</a></li>
                                <li><a href="{{ route('superadmin.sms-logs.index') }}">SMS Logs</a></li>
                            @endsuperadmin
                            <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.notifications.index' : 'admin.notifications.index') }}">Notifications</a></li>
                        </ul>
                    </li>
                @endif

                {{-- Reports (SuperAdmin & Admin) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <li class="label">Reports</li>
                    <li><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.reports.index' : 'admin.reports.index') }}"><i class="ti-bar-chart-alt"></i> Reports</a></li>
                @endif

                {{-- Settings (SuperAdmin & Admin) --}}
                @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                    <li class="label">Settings</li>
                    @admin
                        <li><a href="{{ route('admin.settings.index') }}"><i class="ti-settings"></i> Settings</a></li>
                    @endadmin
                @endif

                {{-- Logout for all roles --}}
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="ti-close"></i> Logout</a></li>
                
            </ul>
        </div>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>