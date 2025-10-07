<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="fiberhomeDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-server"></i> {{ trans('plugins/fiberhome-olt-manager::menu.title') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="fiberhomeDropdown">
        <a class="dropdown-item" href="{{ route('fiberhome.dashboard') }}">
            <i class="fa fa-dashboard"></i> {{ trans('plugins/fiberhome-olt-manager::menu.dashboard') }}
        </a>
        <a class="dropdown-item" href="{{ route('fiberhome.olt.index') }}">
            <i class="fa fa-server"></i> {{ trans('plugins/fiberhome-olt-manager::menu.olt_management') }}
        </a>
        <a class="dropdown-item" href="{{ route('fiberhome.onu.index') }}">
            <i class="fa fa-wifi"></i> {{ trans('plugins/fiberhome-olt-manager::menu.onu_management') }}
        </a>
        <a class="dropdown-item" href="{{ route('fiberhome.bandwidth.index') }}">
            <i class="fa fa-tachometer"></i> {{ trans('plugins/fiberhome-olt-manager::menu.bandwidth_profiles') }}
        </a>
        <a class="dropdown-item" href="{{ route('fiberhome.topology') }}">
            <i class="fa fa-sitemap"></i> {{ trans('plugins/fiberhome-olt-manager::menu.network_topology') }}
        </a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item" href="{{ route('fiberhome.settings.index') }}">
            <i class="fa fa-cog"></i> {{ trans('plugins/fiberhome-olt-manager::menu.settings') }}
        </a>
    </div>
</li>