@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">{{ $stats['total_olts'] }}</h4>
                                <p class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.total_olts') }}</p>
                            </div>
                            <div>
                                <i class="fa fa-server fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">{{ $stats['online_olts'] }}</h4>
                                <p class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.online_olts') }}</p>
                            </div>
                            <div>
                                <i class="fa fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">{{ $stats['total_onus'] }}</h4>
                                <p class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.total_onus') }}</p>
                            </div>
                            <div>
                                <i class="fa fa-wifi fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">{{ $stats['online_onus'] }}</h4>
                                <p class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.online_onus') }}</p>
                            </div>
                            <div>
                                <i class="fa fa-signal fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.performance_overview') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="performance-chart" 
						 data-labels='{!! json_encode($performance["labels"] ?? []) !!}'
						 data-cpu='{!! json_encode($performance["cpu"] ?? []) !!}'
						 data-memory='{!! json_encode($performance["memory"] ?? []) !!}'
						 data-cpu-label='{{ trans("plugins/fiberhome-olt-manager::dashboard.cpu_usage") }}'
						 data-memory-label='{{ trans("plugins/fiberhome-olt-manager::dashboard.memory_usage") }}'
						width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.bandwidth_profiles') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>{{ trans('plugins/fiberhome-olt-manager::dashboard.total_profiles') }}</span>
                            <strong>{{ $stats['total_profiles'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>{{ trans('plugins/fiberhome-olt-manager::dashboard.active_profiles') }}</span>
                            <strong>{{ $stats['active_profiles'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.recent_alerts') }}</h5>
                    </div>
                    <div class="card-body">
                        @if(count($alerts) > 0)
                            <div class="list-group">
                                @foreach($alerts as $alert)
                                    <div class="list-group-item list-group-item-{{ $alert['type'] }}">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="{{ $alert['icon'] }}"></i> {{ $alert['title'] }}
                                            </h6>
                                            <small>{{ $alert['time']->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $alert['message'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                {{ trans('plugins/fiberhome-olt-manager::dashboard.no_alerts') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ trans('plugins/fiberhome-olt-manager::dashboard.quick_actions') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="{{ route('fiberhome.olt.index') }}" class="btn btn-primary">
                                <i class="fa fa-server"></i> {{ trans('plugins/fiberhome-olt-manager::dashboard.manage_olts') }}
                            </a>
                            <a href="{{ route('fiberhome.onu.index') }}" class="btn btn-info">
                                <i class="fa fa-wifi"></i> {{ trans('plugins/fiberhome-olt-manager::dashboard.manage_onus') }}
                            </a>
                            <a href="{{ route('fiberhome.bandwidth.index') }}" class="btn btn-warning">
                                <i class="fa fa-tachometer"></i> {{ trans('plugins/fiberhome-olt-manager::dashboard.manage_bandwidth') }}
                            </a>
                            <a href="{{ route('fiberhome.settings.index') }}" class="btn btn-secondary">
                                <i class="fa fa-cog"></i> {{ trans('plugins/fiberhome-olt-manager::dashboard.settings') }}
                            </a>
                            <a href="{{ route('fiberhome.topology.index') }}" class="btn btn-success">
                                <i class="fa fa-sitemap"></i> {{ trans('plugins/fiberhome-olt-manager::dashboard.network_topology') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	
@endsection
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
