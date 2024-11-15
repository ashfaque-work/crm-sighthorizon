@php
    // $logo=asset(Storage::url('logo/'));
    $logo=\App\Models\Utility::get_file('logo/');

    $setting = App\Models\Utility::settings();
    $mode_setting = \App\Models\Utility::mode_layout();
    $dark_logo=Utility::getValByName('dark_logo');
    $light_logo=Utility::getValByName('light_logo');

@endphp


{{-- <nav class="dash-sidebar light-sidebar transprent-bg"> --}}
    <nav class="dash-sidebar light-sidebar {{ isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on' ? 'transprent-bg' : '' }}">
    <div class="navbar-wrapper">

      <div class="m-header main-logo">

        @if (!empty($mode_setting['cust_darklayout']) && $mode_setting['cust_darklayout'] == 'on')
            <img src="{{ $logo.'/'.  $light_logo . '?' . time() }}" style="max-height: 50px; max-width: 152px;"
                alt="{{ config('app.name', 'Sight Horizon') }}" class="logo logo-lg">
        @else
            <img src="{{ $logo.'/'.  $dark_logo . '?' . time() }}" style="max-height: 50px; max-width: 152px;"
                alt="{{ config('app.name', 'Sight Horizon') }}" class="logo logo-lg">
        @endif


      </div>
      <div class="navbar-content">
        <ul class="dash-navbar">
            <li class="dash-item  {{ (Request::route()->getName() == 'home') ? 'active' : '' }}">
                <a href="{{route('home')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext">{{__('Dashboard')}}</span></a>
            </li>

            @if(Gate::check('Manage Users') || Gate::check('Manage Clients') || Gate::check('Manage Roles') || Gate::check('Manage Permissions'))
                @can('Manage Leads')
                    <li class="dash-item  {{ request()->is('leads*') ? 'active' : '' }}">
                        <a href="{{route('leads.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-3d-cube-sphere"></i></span><span class="dash-mtext">{{__('Leads')}}</span></a>
                    </li>
                @endcan
                @can('Manage Content Marketting')
                    <li class="dash-item  {{ request()->is('contentMarketting*') ? 'active' : '' }}">
                        <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-volume"></i></span><span class="dash-mtext">{{__('Content Marketting')}}</span></a>
                    </li>
                @endcan
                
            @endif
            
            @if (\Auth::user()->type == 'Owner')
                @can('Manage Lead Database')
                    <li class="dash-item  {{ request()->is('leadDatabase*') ? 'active' : '' }}">
                        <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-server"></i></span><span class="dash-mtext">{{__('Lead Database')}}</span></a>
                    </li>
                @endcan
                <li class="dash-item dash-hasmenu">
                <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-chart-dots"></i></span><span class="dash-mtext">{{ __('Revenue and Report') }}</span><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                <ul class="dash-submenu">
                    <li class="dash-item">
                        <a class="dash-link" href="{{ route('report.lead') }}">{{ __('Lead') }}</a>
                    </li>
                    <li class="dash-item">
                        <a class="dash-link" href="{{ route('report.deal') }}">{{ __('Deal') }}</a>
                    </li>
                    @if(Gate::check('Invoice Report') || Gate::check('Expense Report') || Gate::check('Income vs Expense Report'))
                        @can('Invoice Report')
                        <li class="dash-item {{ (Request::route()->getName() == 'report.invoice' ) ? ' active' : '' }}">
                            <a class="dash-link" href="{{ route('report.invoice') }}">{{ __('Invoice') }}</a>
                        </li>
                        @endcan
                        @can('Expense Report')
                        <li class="dash-item {{ (Request::route()->getName() == 'report.expense' ) ? ' active' : '' }}">
                            <a class="dash-link" href="{{ route('report.expenses') }}">{{ __('Expense') }}</a>
                        </li>
                        @endcan
                        @can('Income vs Expense Report')
                        <li class="dash-item {{ (Request::route()->getName() == 'report.income.vs.expense.summary' ) ? ' active' : '' }}">
                            <a class="dash-link" href="{{ route('report.income.vs.expense.summary') }}">{{ __('Income VS Expense') }}</a>
                        </li>
                        @endcan
                    @endif
                </ul>
            </li>
            @endif

            


            @if(Auth::user()->type != 'Super Admin' && Auth::user()->type != 'Client')
            @can('Manage Vendors and Contractor Details')
                <li class="dash-item  {{ request()->is('vendors_contractors*') ? 'active' : '' }}">
                    <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-users"></i></span><span class="dash-mtext">{{__('Vendors & Contra..')}}</span></a>
                </li>
            @endcan
            @endif

            

            @if(Gate::check('System Settings') || Gate::check('Manage Pipelines') || Gate::check('Manage Sources') || Gate::check('Manage Payments') || Gate::check('Manage Expense Categories') || Gate::check('Manage Stages') || Gate::check('Manage Labels') || Gate::check('Manage Custom Fields') || Gate::check('Manage Contract Types') || Gate::check('Manage Email Templates'))
                @if(Gate::check('Manage Pipelines'))
                    <li class="dash-item dash-hasmenu {{ (Request::route()->getName() == 'pipelines.index' || Request::route()->getName() == 'sources.index' || Request::route()->getName() == 'payments.index' || Request::route()->getName() == 'expense_categories.index' || Request::route()->getName() == 'stages.index' || Request::route()->getName() == 'labels.index' || Request::route()->getName() == 'custom_fields.index'  || Request::route()->getName() == 'contract_type.index'  || Request::route()->getName() == 'lead_stages.index' || Request::route()->getName()
                    == 'email_template.index' || Request::route()->getName() == 'mdf_status.index' || Request::route()->getName() == 'mdf_type.index' || Request::route()->getName() == 'mdf_sub_type.index') ? '' : '' }} ">
                    <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-layout-2"></i></span><span
                            class="dash-mtext">{{ __('Sales Pipeline')}}</span><span class="dash-arrow"><i
                                data-feather="chevron-right"></i></span></a>
                    <ul class="dash-submenu {{ (Request::route()->getName() == 'pipelines.index' || Request::route()->getName() == 'sources.index' || Request::route()->getName() == 'payments.index' || Request::route()->getName() == 'expense_categories.index' || Request::route()->getName() == 'stages.index' || Request::route()->getName() == 'labels.index' || Request::route()->getName() == 'custom_fields.index'  || Request::route()->getName() == 'contract_type.index'  || Request::route()->getName() == 'lead_stages.index' || Request::route()->getName()
                    == 'email_template.index' || Request::route()->getName() == 'mdf_status.index' || Request::route()->getName() == 'mdf_type.index' || Request::route()->getName() == 'mdf_sub_type.index') ? 'show' : '' }}">
                        @can('Manage Pipelines')
                            <li class="dash-item {{ (Request::route()->getName() == 'pipelines.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('pipelines.index')}}">{{__('Pipelines')}}</a>
                            </li>
                        @endcan
                        @can('Manage Stages')
                            <li class="dash-item {{ (Request::route()->getName() == 'stages.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('stages.index')}}">{{__('Deal Stages')}}</a>
                            </li>
                        @endcan
                        @can('Manage Lead Stages')
                            <li class="dash-item {{ (Request::route()->getName() == 'lead_stages.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('lead_stages.index')}}">{{__('Lead Stages')}}</a>
                            </li>
                        @endcan
                        @can('Manage Labels')
                            <li class="dash-item {{ (Request::route()->getName() == 'labels.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('labels.index')}}">{{__('Labels')}}</a>
                            </li>
                        @endcan
                        @can('Manage Sources')
                            <li class="dash-item {{ (Request::route()->getName() == 'sources.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('sources.index')}}">{{__('Sources')}}</a>
                            </li>
                        @endcan
                        @can('Manage Payments')
                            <li class="dash-item {{ (Request::route()->getName() == 'payments.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('payments.index')}}">{{__('Payment Methods')}}</a>
                            </li>
                        @endcan
                        @can('Manage Expense Categories')
                            <li class="dash-item {{ (Request::route()->getName() == 'expense_categories.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('expense_categories.index')}}">{{__('Expense Categories')}}</a>
                            </li>
                        @endcan
                        @can('Manage Contract Types')
                            <li class="dash-item {{ (Request::route()->getName() == 'contract_type.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('contract_type.index')}}">{{__('Contract Type')}}</a>
                            </li>
                        @endcan
                        @can('Manage Taxes')
                            <li class="dash-item {{ (Request::route()->getName() == 'taxes.index') ? 'active' : '' }}">
                                <a href="{{route('taxes.index')}}" class="dash-link">{{__('Tax Rates')}}</a>
                            </li>
                        @endcan
                        @can('Manage Custom Fields')
                            <li class="dash-item {{ (Request::route()->getName() == 'custom_fields.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('custom_fields.index')}}">{{__('Custom Fields')}}</a>
                            </li>
                        @endcan
                        @can('Manage MDF Status')
                            <li class="dash-item {{ (Request::route()->getName() == 'mdf_status.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('mdf_status.index')}}">{{__('MDF Status')}}</a>
                            </li>
                        @endcan
                        @can('Manage MDF Types')
                            <li class="dash-item {{ (Request::route()->getName() == 'mdf_type.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('mdf_type.index')}}">{{__('MDF Type')}}</a>
                            </li>
                        @endcan
                        @can('Manage MDF Sub Types')
                            <li class="dash-item {{ (Request::route()->getName() == 'mdf_sub_type.index') ? 'active' : '' }}">
                                <a class="dash-link" href="{{route('mdf_sub_type.index')}}">{{__('MDF Sub Type')}}</a>
                            </li>
                        @endcan
                    </ul>
                  </li>
                @endif
            @endif
            
            @can('Manage Customer Feedback')
                <li class="dash-item  {{ request()->is('customer_feedback*') ? 'active' : '' }}">
                    <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-headphones"></i></span><span class="dash-mtext">{{__('Customer Feedback')}}</span></a>
                </li>
            @endcan
            
            @if (\Auth::user()->type == 'Owner')
                @can('Manage Audit')
                    <li class="dash-item  {{ request()->is('audit*') ? 'active' : '' }}">
                        <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-files"></i></span><span class="dash-mtext">{{__('Audit')}}</span></a>
                    </li>
                @endcan
                @can('Manage Legal')
                    <li class="dash-item  {{ request()->is('legal*') ? 'active' : '' }}">
                        <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-briefcase"></i></span><span class="dash-mtext">{{__('Legal')}}</span></a>
                    </li>
                @endcan
                @can('Manage Team Members')
                    <li class="dash-item  {{ request()->is('team_members*') ? 'active' : '' }}">
                        <a href="#" class="dash-link"><span class="dash-micon"><i class="ti ti-id"></i></span><span class="dash-mtext">{{__('Team Members')}}</span></a>
                    </li>
                @endcan
            @endif
            
            @can('Manage Deals')
                <li class="dash-item  {{ request()->is('deals*') ? 'active' : '' }}">
                    <a href="{{route('deals.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="dash-mtext">{{__('Deals')}}</span></a>
                </li>
            @endcan
            
            @if(Gate::check('Manage Email Templates'))
                <li class="dash-item {{ (Request::route()->getName() == 'email_template.index' || Request::segment(1) == 'email_template_lang'  || Request::route()->getName() == 'manageemail.lang') ? 'active' : '' }}">
                    <a href="{{route('email_template.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-mail"></i></span><span class="dash-mtext">{{__('Email Templates')}}</span></a>
                </li>
            @endif
            
            {{-- @if(\Auth::user()->type == 'Owner')
                <li class="dash-item {{ (Request::route()->getName() == 'notification_template.index' || Request::segment(1) == 'notification_template_langs'  || Request::route()->getName() == 'notification-templates.index') ? 'active' : '' }}">
                    <a href="{{route('notification_template.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-notification"></i></span><span class="dash-mtext">{{__('Notification Template')}}</span></a>
                </li>
                @endif --}}
            @if (\Auth::user()->type == 'Owner')
                @can('Manage Roles')
                    <li class="dash-item  {{ (Request::route()->getName() == 'roles.index') ? 'active' : '' }}">
                        <a href="{{route('roles.index')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-user-x"></i></span><span class="dash-mtext">{{__('Roles')}}</span></a>
                    </li>
                @endcan
                <li class="dash-item {{ (Request::route()->getName() == 'notification-templates.index') ? 'active' : '' }}">
                    <a class="dash-link" href="{{route('notification-templates.index')}}">
                        <span class="dash-micon"><i class="ti ti-notification"></i></span><span class="dash-mtext">{{__('Notifications')}}</span>
                    </a>
                </li>
            @endif
                @if(\Auth::user()->type == 'Owner')
                @include('landingpage::menu.landingpage')
                    @endif
            @can('System Settings')
                <li class="dash-item {{ (Request::route()->getName() == 'settings') ? 'active' : '' }}">
                    <a href="{{route('settings')}}" class="dash-link"><span class="dash-micon"><i class="ti ti-settings"></i></span><span class="dash-mtext">{{__('System Settings')}}</span></a>
                </li>
            @endcan


        </ul>
    </div>


        </ul>
        <div class="card bg-primary">
          <div class="card-body">
            <h2 class="text-white">Need help?</h2>
            <p class="text-white"><i>Please check our docs.</i></p>
            <div class="d-grid">
              <button class="btn btn-light">Documentation</button>
            </div>
            <img
              src="{{asset('assets/images/sidebar-card.svg')}}"
              alt=""
              class="img-sidebar-card"
            />
          </div>
        </div>
      </div>
    </div>
  </nav>
