@php
    $users = \Auth::user();
    $languages = \App\Models\Custom::languages();
    $userLang = $users ? $users->lang : 'english';
    $profile = asset(Storage::url('upload/profile'));
@endphp

<header class="pc-header">
    <div class="header-wrapper"><!-- [Mobile Media Block] start -->
        <div class="me-auto pc-mob-drp">
            <ul class="list-unstyled">
                <li class="pc-h-item header-mobile-collapse">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="sidebar-hide">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>
                <li class="pc-h-item pc-sidebar-popup">
                    <a href="#" class="pc-head-link head-link-secondary ms-0" id="mobile-collapse">
                        <i class="ti ti-menu-2"></i>
                    </a>
                </li>

            </ul>
        </div>
        <!-- [Mobile Media Block end] -->
        <div class="ms-auto d-flex align-items-center gap-2" style="margin-right: 18px;">
            <ul class="list-unstyled mb-0 d-flex align-items-center gap-2">
                <li class="dropdown pc-h-item" data-bs-toggle="tooltip" data-bs-original-title="{{__('Language')}}" data-bs-placement="bottom">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                        href="#" role="button" aria-haspopup="false" aria-expanded="false" >
                        <i class="ti ti-language"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
                        @foreach($languages as $language)
                            @if($language!='en')
                                <a href="{{route('language.change',$language)}}" class="dropdown-item {{ $userLang==$language?'active':'' }}">
                                    <span class="align-middle">{{ucfirst( $language)}}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </li>
                @if ($users && ($users->type == 'super admin' || $users->type == 'owner'))
                    <li class="dropdown pc-h-item pc-mega-menu" data-bs-toggle="tooltip" data-bs-original-title="{{__('Theme Settings')}}" data-bs-placement="bottom">
                        <a href="#" class="pc-head-link head-link-secondary dropdown-toggle arrow-none me-0"
                            data-bs-toggle="offcanvas" data-bs-target="#offcanvas_pc_layout">
                            <i class="ti ti-settings"></i>
                        </a>
                    </li>
                @endif
                <li class="dropdown pc-h-item header-user-profile">
                    <a class="pc-head-link head-link-primary dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown"
                        href="#" role="button" aria-haspopup="false" aria-expanded="false">
                        <img src="{{(!empty($users->profile)? $profile.'/'.$users->profile : $profile.'/avatar.png')}}" alt="user-image" class="user-avtar" style="width:38px;height:38px;border-radius:50%;object-fit:cover;" />
                        <span>
                            <i class="ti ti-user-check"></i>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                        <div class="dropdown-header text-center">
                            @php
                                $hour = \Carbon\Carbon::now()->format('H');
                                if ($hour < 12) {
                                    $greeting = 'Good morning';
                                } elseif ($hour < 18) {
                                    $greeting = 'Good afternoon';
                                } else {
                                    $greeting = 'Good evening';
                                }
                            @endphp
                            <span class="fw-bold d-block mb-1" style="font-size:1.1rem; color:#222;">{{ $greeting }},</span>
                            <span class="fw-bold d-block mb-1" style="font-size:1.35rem; letter-spacing:0.5px; color:#444;">{{ $users ? $users->name : 'Guest' }}</span>
                            <span class="text-muted small">{{ $users ? ucfirst($users->type) : 'Guest' }}</span>
                        </div>
                        <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 280px)">
                            @if($users && $users->type !== 'tenant')
                                <a href="{{ route('setting.index') }}#user_profile_settings" class="dropdown-item">
                                    <i class="ti ti-user"></i>
                                    <span>{{ __('Profile') }}</span>
                                </a>
                            @elseif($users && $users->type === 'tenant')
                                <a href="{{ route('tenant.password.edit') }}" class="dropdown-item">
                                    <i class="ti ti-lock"></i>
                                    <span>{{ __('Change Password') }}</span>
                                </a>
                            @endif
                            <hr />
                            @impersonating()
                            <a href="{{ route('impersonate.leave') }}" class="dropdown-item" data-actions="Account">
                                <i class="ti ti-transfer-out"></i>
                                <span>{{ __('Leave') }}</span>
                            </a>
                            @endImpersonating
                            <a href="{{ route('logout') }}" class="dropdown-item"  onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">
                                <i class="ti ti-logout"></i>
                                <span>{{ __('Logout') }}</span>
                                <form id="frm-logout" action="{{ route('logout') }}" method="POST" class="d-none">
                                    {{ csrf_field() }}
                                </form>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</header>
