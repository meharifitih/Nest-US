@php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = null;
    if ($authUser && $authUser->subscription) {
        $subscription = \App\Models\Subscription::find($authUser->subscription);
    }
    $routeName = \Request::route() ? \Request::route()->getName() : '';
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header text-center py-3">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary d-inline-block">
                <img src="{{ asset(Storage::url('upload/logo/landing_logo.png')) }}" alt="logo" class="img-fluid sidebar-modern-logo" style="max-height:48px; display:block; margin:0 auto;" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>{{ __('Home') }}</label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item {{ in_array($routeName, ['dashboard', 'home', '']) ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if (\Auth::user() && \Auth::user()->type == 'super admin')
                    @if (Gate::check('manage user'))
                        <li class="pc-item {{ in_array($routeName, ['users.index', 'users.show']) ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext">{{ __('Customers') }}</span>
                            </a>
                        </li>
                    @endif
                @else
                    @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-users"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Staff Management') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage user'))
                                    <li class="pc-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('users.index') }}">{{ __('Users') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage role'))
                                    <li
                                        class="pc-item  {{ in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('role.index') }}">{{ __('Roles') }} </a>
                                    </li>
                                @endif
                                @if ($pricing_feature_settings == 'off' || (isset($subscription) && $subscription->enabled_logged_history == 1))
                                    @if (Gate::check('manage logged history'))
                                        <li
                                            class="pc-item  {{ in_array($routeName, ['logged.history']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('logged.history') }}">{{ __('Logged History') }}</a>
                                        </li>
                                    @endif
                                @endif
                            </ul>
                        </li>
                    @endif
                @endif

                @if (Auth::user() && Auth::user()->type == 'owner')
                    <li class="pc-item {{ in_array($routeName, ['owner.tutorial_videos']) ? 'active' : '' }}">
                        <a href="{{ route('owner.tutorial_videos') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-video"></i></span>
                            <span class="pc-mtext">{{ __('Tutorial Videos') }}</span>
                        </a>
                    </li>
                @endif

                @if (Gate::check('manage property') ||
                        Gate::check('manage unit') ||
                        Gate::check('manage tenant') ||
                        Gate::check('manage invoice') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage maintainer') ||
                        Gate::check('manage maintenance request') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage support') ||
                        Gate::check('manage note'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('Business Management') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage tenant'))
                        <li
                            class="pc-item {{ in_array($routeName, ['tenant.index', 'tenant.create', 'tenant.edit', 'tenant.show']) ? 'active' : '' }}">
                            <a href="{{ route('tenant.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user"></i></span>
                                <span class="pc-mtext">{{ __('Tenants') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage maintainer'))
                        <li class="pc-item {{ in_array($routeName, ['maintainer.index']) ? 'active' : '' }}">
                            <a href="{{ route('maintainer.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                                <span class="pc-mtext">{{ __('Maintainers') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage tenant') || Gate::check('manage property') || Gate::check('manage unit'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Real Estate') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage property'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('property.index') }}">{{ __('Properties') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage unit'))
                                    <li class="pc-item {{ in_array($routeName, ['unit.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('unit.index') }}">{{ __('Units') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (Gate::check('manage maintainer') || Gate::check('manage maintenance request'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.inprogress']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-tool"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Maintenance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.inprogress']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage maintenance request'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.index') }}">{{ __('All Requests') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.pending']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.pending') }}">{{ __('Pending') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.inprogress']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.inprogress') }}">{{ __('In Progress') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (Gate::check('manage invoice') || Gate::check('manage expense'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index']) ? 'pc-trigger  active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Finance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage invoice'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('invoice.index') }}">{{ __('Invoices') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage expense'))
                                    <li class="pc-item {{ in_array($routeName, ['expense.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                                    </li>
                                @endif
                                <li class="pc-item {{ in_array($routeName, ['rent.index']) ? 'active' : '' }}">
                                    <a class="pc-link" href="{{ route('rent.index') }}">{{ __('Rent') }}</a>
                                </li>
                                <li class="pc-item {{ in_array($routeName, ['tenant.payments.index', 'tenant.payments.create', 'tenant.payments.show']) ? 'active' : '' }}">
                                    <a class="pc-link" href="{{ route('tenant.payments.index') }}">{{ __('Tenant Payments') }}</a>
                                </li>
                                @if (Auth::user()->hasRole('owner') || Auth::user()->hasRole('tenant'))
                                    <li class="pc-item {{ in_array($routeName, ['hoa.index', 'hoa.create', 'hoa.show']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('hoa.index') }}">{{ __('HOA') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (Gate::check('manage contact'))
                        <li class="pc-item {{ in_array($routeName, ['contact.index']) ? 'active' : '' }}">
                            <a href="{{ route('contact.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                <span class="pc-mtext">{{ __('Contact Diary') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage note'))
                        <li class="pc-item {{ in_array($routeName, ['note.index']) ? 'active' : '' }} ">
                            <a href="{{ route('note.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext">{{ __('Notice Board') }}</span>
                            </a>
                        </li>
                    @endif
                @endif


                @if (Gate::check('manage notification') || Gate::check('manage types'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Configuration') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    @if (Gate::check('manage types'))
                        <li class="pc-item {{ in_array($routeName, ['type.index']) ? 'active' : '' }}">
                            <a href="{{ route('type.index') }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="file-text"></i></span>
                                <span class="pc-mtext">{{ __('Types') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage notification'))
                        <li class="pc-item {{ in_array($routeName, ['notification.index']) ? 'active' : '' }} ">
                            <a href="{{ route('notification.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext">{{ __('Email Notification') }}</span>
                            </a>
                        </li>
                    @endif
                @endif


                @if (Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Settings') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage FAQ') || Gate::check('manage Page'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext">{{ __('CMS') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage home page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['homepage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('homepage.index') }}"
                                            class="pc-link">{{ __('Home Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage Page'))
                                    <li class="pc-item {{ in_array($routeName, ['pages.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('pages.index') }}"
                                            class="pc-link">{{ __('Custom Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage FAQ'))
                                    <li class="pc-item {{ in_array($routeName, ['FAQ.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('FAQ.index') }}" class="pc-link">{{ __('FAQ') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage footer'))
                                    <li class="pc-item {{ in_array($routeName, ['footerSetting']) ? 'active' : '' }} ">
                                        <a href="{{ route('footerSetting') }}"
                                            class="pc-link">{{ __('Footer') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage auth page'))
                                    <li class="pc-item {{ in_array($routeName, ['authPage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('authPage.index') }}"
                                            class="pc-link">{{ __('Auth Page') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Auth::user()->type == 'super admin' || $pricing_feature_settings == 'on')
                        @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation'))
                            <li
                                class="pc-item pc-hasmenu {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'pc-trigger active' : '' }}">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext">{{ __('Pricing') }}</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none' }}">
                                    @if (Gate::check('manage pricing packages'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscriptions.index') }}">{{ __('Packages') }}</a>
                                        </li>
                                    @endif
                                    @if (Gate::check('manage pricing transation'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscription.transaction']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscription.transaction') }}">{{ __('Transactions') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endif
                    @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Coupons') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage coupon'))
                                    <li class="pc-item {{ in_array($routeName, ['coupons.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.index') }}">{{ __('All Coupon') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage coupon history'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.history']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.history') }}">{{ __('Coupon History') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings'))
                        <li class="pc-item {{ in_array($routeName, ['setting.index']) ? 'active' : '' }} ">
                            <a href="{{ route('setting.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext">{{ __('Settings') }}</span>
                            </a>
                        </li>
                    @endif

                @endif
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>

<style>
    .sidebar-modern-logo {
        display: block;
        margin: 0 auto;
        max-height: 48px;
    }
</style>
