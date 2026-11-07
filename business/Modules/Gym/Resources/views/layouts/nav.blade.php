<section class="no-print">
    <nav
        class="navbar-default tw-transition-all tw-duration-5000 tw-shrink-0 tw-rounded-2xl tw-m-[16px] tw-border-2 !tw-bg-white">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false"
                    style="margin-top: 3px; margin-right: 3px;">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand"
                    href="{{ action([\Modules\Gym\Http\Controllers\DashBoardController::class, 'index']) }}"><i
                        class="fas fa-dumbbell"></i> @lang('gym::lang.gym')</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                @can('gym.manage_member')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'members') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\MemberController::class, 'index']) }}">@lang('gym::lang.members')</a>
                        </li>
                    </ul>
                @endcan
                @can('gym.manage_subscription')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'subscriptions') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\SubscriptionController::class, 'index']) }}">@lang('gym::lang.subscriptions')</a>
                        </li>
                    </ul>
                @endcan
                @can('gym.manage_attendance')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'attendance') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\AttendanceController::class, 'index']) }}">@lang('gym::lang.attendance')</a>
                        </li>
                    </ul>
                @endcan
                @can('gym.manage_package')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'gym-packages') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\PackageController::class, 'index']) }}">@lang('gym::lang.packages')</a>
                        </li>
                    </ul>
                @endcan
                @can('gym.manage_class')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'classes') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\ClassController::class, 'index']) }}">@lang('gym::lang.classes')</a>
                        </li>
                    </ul>
                @endcan
                @can('gym.manage_setting')
                    <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'gym' && request()->segment(2) == 'settings') class="active" @endif><a
                                href="{{ action([Modules\Gym\Http\Controllers\SettingController::class, 'index']) }}">@lang('gym::lang.settings')</a>
                        </li>
                    </ul>
                @endcan
                
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
