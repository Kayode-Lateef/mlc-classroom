<div class="header">
    <div class="pull-left">
        <div class="logo"><a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.dashboard' : (auth()->user()->isAdmin() ? 'admin.dashboard' : (auth()->user()->isTeacher() ? 'teacher.dashboard' : 'parent.dashboard'))) }}"><span>MLC Classroom</span></a></div>
        <div class="hamburger sidebar-toggle">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
        </div>
    </div>
    <div class="pull-right p-r-15">
        <ul>
            {{-- <li class="header-icon dib"><a href="#search"><i class="ti-search"></i></a></li> --}}
            
            {{-- Dynamic Notification Bell --}}
            <li class="header-icon dib notification-bell-wrapper">
                <i class="ti-bell"></i>
                <span class="notification-badge" id="notification-count" style="display: none;">0</span>
                
                <div class="drop-down">
                    {{-- Header --}}
                    <div class="dropdown-content-heading">
                        <span class="text-left">Recent Notifications</span>
                        <a href="#" id="mark-all-read" style="font-size: 12px; float: right; color: #007bff; opacity: 0.9;">Mark all read</a>
                    </div>
                    
                    {{-- Body --}}
                    <div class="dropdown-content-body" id="notification-list">
                        <ul>
                            {{-- Loading State --}}
                            <li class="text-center" style="padding: 30px 20px;">
                                <i class="ti-reload" style="font-size: 2rem; color: #cbd5e0; animation: spin 1s linear infinite;"></i>
                                <p style="font-size: 13px; color: #6c757d; margin-top: 10px;">Loading...</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>
         
            {{-- User Avatar Dropdown --}}
            <li class="header-icon dib">
                {{-- User Avatar --}}
                <img class="avatar-img" 
                    src="{{ auth()->user()->profile_photo ? asset('storage/' . auth()->user()->profile_photo) : asset('assets/images/avatar/1.jpg') }}" 
                    alt="{{ auth()->user()->name }}" /> 
                
                {{-- User Name with Dropdown Arrow --}}
                <span class="user-avatar">
                    {{ auth()->user()->name }} 
                    <i class="ti-angle-down f-s-10"></i>
                </span>
                
                {{-- Dropdown Menu --}}
                <div class="drop-down dropdown-profile">
                    <div class="dropdown-content-body">
                        <ul>
                            {{-- Profile --}}
                            <li>
                                <a href="{{ route('profile.edit') }}">
                                    <i class="ti-user"></i> <span>Profile</span>
                                </a>
                            </li>
                            
                            {{-- Settings (SuperAdmin & Admin only) --}}
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <li>
                                    <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.settings.index') : route('admin.settings.index') }}">
                                        <i class="ti-settings"></i> <span>Settings</span>
                                    </a>
                                </li>
                            @endif
                            
                            {{-- Logout --}}
                            <li>
                                <a href="{{ route('logout') }}" 
                                onclick="event.preventDefault(); document.getElementById('navbar-logout-form').submit();">
                                    <i class="ti-power-off"></i> <span>Logout</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </li>

            {{-- Logout Form --}}
            <form id="navbar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </ul>
    </div>
</div>

{{-- Minimal Additional Styles (Only for badge and unread state) --}}
<style>
    /* Notification Badge */
    .notification-bell-wrapper {
        position: relative;
    }

    .notification-badge {
        position: absolute;
        top: 10px;
        right: -5px;
        background: #007bff;
        color: white;
        font-size: 10px;
        font-weight: 600;
        padding: 2px 5px;
        border-radius: 10px;
        min-width: 16px;
        text-align: center;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .dropdown-content-heading a#mark-all-read:hover {
        text-decoration: underline !important;
    }

    /* Unread notification highlight */
    .notification-item-unread {
        background-color: #f0f4ff !important;
    }

    /* Notification type icons */
    .notif-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }

    .notif-icon.type-general { background-color: #e7f3ff; color: #007bff; }
    .notif-icon.type-emergency { background-color: #ffe7e7; color: #dc3545; }
    .notif-icon.type-homework { background-color: #fff3cd; color: #ffc107; }
    .notif-icon.type-progress_report { background-color: #d4edda; color: #28a745; }
    .notif-icon.type-schedule_change { background-color: #f8d7da; color: #dc3545; }
    .notif-icon.type-absence { background-color: #cfe2ff; color: #0d6efd; }
</style>

{{-- Notification Bell JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notificationBell = document.querySelector('.notification-bell-wrapper');
    const notificationDropdown = notificationBell.querySelector('.drop-down');
    const notificationList = document.getElementById('notification-list');
    const notificationCount = document.getElementById('notification-count');
    const markAllReadBtn = document.getElementById('mark-all-read');
    
    let isDropdownOpen = false;

    // Toggle dropdown on bell click
    notificationBell.addEventListener('click', function(e) {
        e.stopPropagation();
        isDropdownOpen = !isDropdownOpen;
        
        if (isDropdownOpen) {
            notificationDropdown.style.display = 'block';
            loadNotifications();
        } else {
            notificationDropdown.style.display = 'none';
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!notificationBell.contains(e.target)) {
            notificationDropdown.style.display = 'none';
            isDropdownOpen = false;
        }
    });

    // Prevent dropdown from closing when clicking inside
    notificationDropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Load notifications
    function loadNotifications() {
        showLoading();
        
        fetch('{{ route("notifications.unread") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount(data.count);
                renderNotifications(data.notifications);
            } else {
                showError('Failed to load notifications');
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            showError('Failed to load notifications');
        });
    }

    // Render notifications in existing template format
    function renderNotifications(notifications) {
        if (notifications.length === 0) {
            notificationList.innerHTML = `
                <ul>
                    <li class="text-center" style="padding: 30px 20px;">
                        <i class="ti-bell" style="font-size: 2.5rem; color: #cbd5e0;"></i>
                        <p style="font-size: 13px; color: #6c757d; margin-top: 10px;">No new notifications</p>
                    </li>
                </ul>
            `;
            return;
        }

        const notificationItems = notifications.map(notification => `
            <li class="notification-item-unread" data-id="${notification.id}" data-url="${notification.url || ''}" style="cursor: pointer;">
                <a href="#" onclick="return false;">
                    <div class="notif-icon type-${notification.type} pull-left">
                        <i class="${notification.icon}"></i>
                    </div>
                    <div class="notification-content">
                        <small class="notification-timestamp pull-right">${notification.time_ago}</small>
                        <div class="notification-heading">${escapeHtml(notification.title)}</div>
                        <div class="notification-text">${escapeHtml(notification.message)}</div>
                    </div>
                </a>
            </li>
        `).join('');

        const viewAllLink = `
            <li class="text-center">
                <a href="{{ route(auth()->user()->isSuperAdmin() ? 'superadmin.notifications.index' : 'admin.notifications.index') }}" class="more-link">See All</a>
            </li>
        `;

        notificationList.innerHTML = `<ul>${notificationItems}${viewAllLink}</ul>`;

        // Add click handlers to notification items
        document.querySelectorAll('.notification-item-unread').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const notificationId = this.dataset.id;
                const notificationUrl = this.dataset.url;
                markAsRead(notificationId, notificationUrl);
            });
        });
    }

    // Show loading state
    function showLoading() {
        notificationList.innerHTML = `
            <ul>
                <li class="text-center" style="padding: 30px 20px;">
                    <i class="ti-reload" style="font-size: 2rem; color: #cbd5e0; animation: spin 1s linear infinite;"></i>
                    <p style="font-size: 13px; color: #6c757d; margin-top: 10px;">Loading...</p>
                </li>
            </ul>
        `;
    }

    // Show error state
    function showError(message) {
        notificationList.innerHTML = `
            <ul>
                <li class="text-center" style="padding: 30px 20px;">
                    <i class="ti-alert" style="font-size: 2rem; color: #dc3545;"></i>
                    <p style="font-size: 13px; color: #dc3545; margin-top: 10px;">${message}</p>
                </li>
            </ul>
        `;
    }

    // Update notification count badge
    function updateNotificationCount(count) {
        if (count > 0) {
            notificationCount.textContent = count > 99 ? '99+' : count;
            notificationCount.style.display = 'inline-block';
        } else {
            notificationCount.style.display = 'none';
        }
    }

    // Mark single notification as read
    function markAsRead(notificationId, url) {
        fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount(data.unread_count);
                
                // Close dropdown and redirect if URL exists
                if (url && url !== '' && url !== 'null') {
                    notificationDropdown.style.display = 'none';
                    isDropdownOpen = false;
                    window.location.href = url;
                } else {
                    loadNotifications();
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (confirm('Mark all notifications as read?')) {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateNotificationCount(0);
                    loadNotifications();
                }
            })
            .catch(error => {
                console.error('Error marking all as read:', error);
            });
        }
    });

    // Fetch notification count on page load
    function fetchNotificationCount() {
        fetch('{{ route("notifications.count") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount(data.count);
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
    }

    // Auto-refresh notification count every 30 seconds
    fetchNotificationCount(); // Initial load
    setInterval(fetchNotificationCount, 30000); // Every 30 seconds

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
});
</script>