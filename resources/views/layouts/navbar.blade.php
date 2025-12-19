    <div class="header">
        <div class="pull-left">
            <div class="logo"><a href="index.html"><span>MLC Classroom</span></a></div>
            <div class="hamburger sidebar-toggle">
                <span class="line"></span>
                <span class="line"></span>
                <span class="line"></span>
            </div>
        </div>
        <div class="pull-right p-r-15">
            <ul>
                <li class="header-icon dib"><a href="#search"><i class="ti-search"></i></a></li>
                <li class="header-icon dib"><i class="ti-bell"></i>
                    <div class="drop-down">
                        <div class="dropdown-content-heading">
                            <span class="text-left">Recent Notifications</span>
                        </div>
                        <div class="dropdown-content-body">
                            <ul>
                                <li>
                                    <a href="#">
                                        <img class="pull-left m-r-10 avatar-img" src="assets/images/avatar/3.jpg" alt="" />
                                        <div class="notification-content">
                                            <small class="notification-timestamp pull-right">02:34 PM</small>
                                            <div class="notification-heading">Mr.  Ajay</div>
                                            <div class="notification-text">5 members joined today </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img class="pull-left m-r-10 avatar-img" src="assets/images/avatar/3.jpg" alt="" />
                                        <div class="notification-content">
                                            <small class="notification-timestamp pull-right">02:34 PM</small>
                                            <div class="notification-heading">Mr.  Ajay</div>
                                            <div class="notification-text">likes a photo of you</div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img class="pull-left m-r-10 avatar-img" src="assets/images/avatar/3.jpg" alt="" />
                                        <div class="notification-content">
                                            <small class="notification-timestamp pull-right">02:34 PM</small>
                                            <div class="notification-heading">Mr.  Ajay</div>
                                            <div class="notification-text">Hi Teddy, Just wanted to let you ...</div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <img class="pull-left m-r-10 avatar-img" src="assets/images/avatar/3.jpg" alt="" />
                                        <div class="notification-content">
                                            <small class="notification-timestamp pull-right">02:34 PM</small>
                                            <div class="notification-heading">Mr.  Ajay</div>
                                            <div class="notification-text">Hi Teddy, Just wanted to let you ...</div>
                                        </div>
                                    </a>
                                </li>
                                <li class="text-center">
                                    <a href="#" class="more-link">See All</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
             
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
  <!-- /# header -->