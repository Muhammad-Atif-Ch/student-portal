<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="#"> <img alt="image" src="{{ asset('assets/img/logo.png') }}" class="header-logo" /> <span class="logo-name">Student</span>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>
            <li class="dropdown {{ Request::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="nav-link"><i data-feather="monitor"></i><span>Dashboard</span></a>
            </li>
            <li class="dropdown {{ Request::is('language*') ? 'active' : '' }}">
                <a href="{{ route('admin.language.index') }}" class="nav-link"><i data-feather="globe"></i><span>Language</span></a>
            </li>
            <li class="dropdown {{ Request::is('contact-us*') ? 'active' : '' }}">
                <a href="{{ route('admin.contact-us.index') }}" class="nav-link"><i data-feather="phone-incoming"></i><span>Contact Us</span></a>
            </li>
            <li class="dropdown {{ Request::is('quiz*') ? 'active' : '' }}">
                <a href="{{ route('admin.quiz.index') }}" class="nav-link"><i data-feather="clipboard"></i><span>Quiz</span></a>
            </li>
            {{-- <li class="{{ Request::is('admin/translation*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.translation.index') }}">
                    <i data-feather="type"></i>
                    <span>{{ __('Translation') }}</span>
                </a>
            </li> --}}
            {{-- <li class="{{ Request::is('admin/text-to-speech*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.tts.index') }}">
                    <i data-feather="volume-2"></i>
                    <span>{{ __('Text to Speech') }}</span>
                </a>
            </li> --}}
            <li class="{{ Request::is('admin/translations*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.translations.index') }}">
                    <i data-feather="list"></i>
                    <span>{{ __('Translations') }}</span>
                </a>
            </li>
            <li class="dropdown {{ Request::is('users*') ? 'active' : '' }}">
                <a href="{{ route('admin.users.index') }}" class="nav-link"><i data-feather="user"></i><span>Users</span></a>
            </li>
            <li class="dropdown {{ Request::is('setting*') ? 'active' : '' }}">
                <a href="{{ route('admin.setting.appImage') }}" class="nav-link"><i data-feather="image"></i><span>App Image</span></a>
            </li>

            {{-- <li class="dropdown {{ Request::is('category*') || Request::is('question*') || Request::is('users*') ? 'active' : '' }}">
                <a href="#" class="menu-toggle nav-link has-dropdown"><i
                        data-feather="briefcase"></i><span>Main</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('category*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('category.index') }}">Category</a></li>
                    <li class="{{ Request::is('question*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('question.index') }}">Question</a></li>
                    <li class="{{ Request::is('users*') ? 'active' : '' }}"><a class="nav-link" href="{{ route('users.index') }}">User</a></li>
                </ul>
            </li> --}}

            {{-- <li class="menu-header">UI Elements</li>
            <li class="dropdown">
                <a href="#" class="menu-toggle nav-link has-dropdown"><i data-feather="copy"></i><span>Basic
                        Components</span></a>
                <ul class="dropdown-menu">
                    <li><a class="nav-link" href="alert.html">Alert</a></li>
                </ul>
            </li> --}}
        </ul>
    </aside>
</div>
