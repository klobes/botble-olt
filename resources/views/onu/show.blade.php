@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>{{ $olt->name }}</h1>
                        <p class="text-muted">{{ trans('plugins/fiberhome-olt::olt.show_description') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('fiberhome-olt.olt.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ trans('plugins/fiberhome-olt::olt.back') }}
                        </a>
                        <a href="{{ route('fiberhome-olt.olt.edit', $olt->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> {{ trans('plugins/fiberhome-olt::olt.edit') }}
                        </a>
                        <button type="button" class="btn btn-success" id="poll-olt">
                            <i class="fas fa-sync"></i> {{ trans('plugins/fiberhome-olt::olt.poll_now') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-{{ $olt->status === 'online' ? 'success' : 'danger' }}">
                                <i class="fas fa-circle"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">{{ trans('plugins/fiberhome-olt::olt.status') }}</p>
                                <h4 class="card-title">{{ ucfirst($olt->status) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-info">
                                <i class="fas fa-network-wired"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">{{ trans('plugins/fiberhome-olt::olt.total_onus') }}</p>
                                <h4 class="card-title">{{ $olt->onus->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">{{ trans('plugins/fiberhome-olt::olt.online_onus') }}</p>
                                <h4 class="card-title">{{ $olt->onus->where('status', 'online')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center icon-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">{{ trans('plugins/fiberhome-olt::olt.active_alerts') }}</p>
                                <h4 class="card-title">{{ $olt->alerts()->whereNull('acknowledged_at')->count() }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OLT Information -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.device_info') }}</h4>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">{{ trans('plugins/fiberhome-olt::olt.name') }}:</th>
                            <td>{{ $olt->name }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.model') }}:</th>
                            <td><span class="badge badge-primary">{{ $olt->model }}</span></td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.ip_address') }}:</th>
                            <td><code>{{ $olt->ip_address }}</code></td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.snmp_version') }}:</th>
                            <td>v{{ $olt->snmp_version }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.snmp_port') }}:</th>
                            <td>{{ $olt->snmp_port }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.location') }}:</th>
                            <td>{{ $olt->location ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.coordinates') }}:</th>
                            <td>
                                @if($olt->latitude && $olt->longitude)
                                    {{ $olt->latitude }}, {{ $olt->longitude }}
                                    <a href="https://www.google.com/maps?q={{ $olt->latitude }},{{ $olt->longitude }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info ml-2">
                                        <i class="fas fa-map-marker-alt"></i> {{ trans('plugins/fiberhome-olt::olt.view_map') }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.description') }}:</th>
                            <td>{{ $olt->description ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.performance_metrics') }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ trans('plugins/fiberhome-olt::olt.cpu_usage') }}</span>
                            <span class="font-weight-bold">{{ $olt->cpu_usage ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $olt->cpu_usage > 80 ? 'danger' : ($olt->cpu_usage > 60 ? 'warning' : 'success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $olt->cpu_usage ?? 0 }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ trans('plugins/fiberhome-olt::olt.memory_usage') }}</span>
                            <span class="font-weight-bold">{{ $olt->memory_usage ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $olt->memory_usage > 80 ? 'danger' : ($olt->memory_usage > 60 ? 'warning' : 'success') }}" 
                                 role="progressbar" 
                                 style="width: {{ $olt->memory_usage ?? 0 }}%">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ trans('plugins/fiberhome-olt::olt.temperature') }}</span>
                            <span class="font-weight-bold">{{ $olt->temperature ?? 0 }}°C</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $olt->temperature > 70 ? 'danger' : ($olt->temperature > 50 ? 'warning' : 'info') }}" 
                                 role="progressbar" 
                                 style="width: {{ min(($olt->temperature ?? 0) * 1.25, 100) }}%">
                            </div>
                        </div>
                    </div>

                    <hr>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.uptime') }}:</th>
                            <td>{{ $olt->uptime ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.last_seen') }}:</th>
                            <td>{{ $olt->last_seen_at ? $olt->last_seen_at->diffForHumans() : '-' }}</td>
                        </tr>
                        <tr>
                            <th>{{ trans('plugins/fiberhome-olt::olt.polling_interval') }}:</th>
                            <td>{{ $olt->polling_interval }} {{ trans('plugins/fiberhome-olt::olt.seconds') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.performance_history') }}</h4>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- OLT Ports -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.ports') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.port_number') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.port_type') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.onus') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.rx_power') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.tx_power') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($olt->ports as $port)
                                    <tr>
                                        <td><strong>{{ $port->port_number }}</strong></td>
                                        <td><span class="badge badge-info">{{ $port->port_type }}</span></td>
                                        <td>
                                            <span class="badge badge-{{ $port->admin_status === 'up' ? 'success' : 'danger' }}">
                                                {{ $port->admin_status }}
                                            </span>
                                        </td>
                                        <td>{{ $port->onus_count ?? 0 }}</td>
                                        <td>{{ $port->rx_power ?? '-' }} dBm</td>
                                        <td>{{ $port->tx_power ?? '-' }} dBm</td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-port-details" data-port-id="{{ $port->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ trans('plugins/fiberhome-olt::olt.no_ports') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connected ONUs -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.connected_onus') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.serial_number') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.customer') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.port') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.rx_power') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.distance') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.bandwidth') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($olt->onus as $onu)
                                    <tr>
                                        <td><code>{{ $onu->serial_number }}</code></td>
                                        <td>{{ $onu->customer_name ?? '-' }}</td>
                                        <td>{{ $onu->pon_port }}/{{ $onu->onu_id }}</td>
                                        <td>
                                            <span class="badge badge-{{ $onu->status === 'online' ? 'success' : 'danger' }}">
                                                {{ $onu->status }}
                                            </span>
                                        </td>
                                        <td>{{ $onu->rx_power ?? '-' }} dBm</td>
                                        <td>{{ $onu->distance ?? '-' }} m</td>
                                        <td>
                                            @if($onu->bandwidthProfile)
                                                {{ $onu->bandwidthProfile->name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('fiberhome-olt.onu.show', $onu->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">{{ trans('plugins/fiberhome-olt::olt.no_onus') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Alerts -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt::olt.recent_alerts') }}</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.severity') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.type') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.message') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.time') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt::olt.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($olt->alerts()->latest()->limit(10)->get() as $alert)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $alert->severity_class }}">
                                                {{ $alert->severity }}
                                            </span>
                                        </td>
                                        <td>{{ $alert->type }}</td>
                                        <td>{{ Str::limit($alert->message, 60) }}</td>
                                        <td>{{ $alert->created_at->diffForHumans() }}</td>
                                        <td>
                                            @if($alert->acknowledged_at)
                                                <span class="badge badge-success">
                                                    {{ trans('plugins/fiberhome-olt::olt.acknowledged') }}
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    {{ trans('plugins/fiberhome-olt::olt.pending') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ trans('plugins/fiberhome-olt::olt.no_alerts') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(document).ready(function() {
        // Poll OLT
        $('#poll-olt').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> {{ trans('plugins/fiberhome-olt::olt.polling') }}');
            
            $.ajax({
                url: '{{ route('fiberhome-olt.olt.poll', $olt->id) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('{{ trans('plugins/fiberhome-olt::olt.poll_error') }}');
                },
                complete: function() {
                    btn.prop('disabled', false);
                    btn.html('<i class="fas fa-sync"></i> {{ trans('plugins/fiberhome-olt::olt.poll_now') }}');
                }
            });
        });

        // Performance Chart
        const ctx = document.getElementById('performanceChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($performanceData['labels'] ?? []) !!},
                    datasets: [
                        {
                            label: '{{ trans('plugins/fiberhome-olt::olt.cpu_usage') }}',
                            data: {!! json_encode($performanceData['cpu'] ?? []) !!},
                            borderColor: 'rgb(255, 99, 132)',
                            backgroundColor: 'rgba(255, 99, 132, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: '{{ trans('plugins/fiberhome-olt::olt.memory_usage') }}',
                            data: {!! json_encode($performanceData['memory'] ?? []) !!},
                            borderColor: 'rgb(54, 162, 235)',
                            backgroundColor: 'rgba(54, 162, 235, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: '{{ trans('plugins/fiberhome-olt::olt.temperature') }}',
                            data: {!! json_encode($performanceData['temperature'] ?? []) !!},
                            borderColor: 'rgb(255, 206, 86)',
                            backgroundColor: 'rgba(255, 206, 86, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    });
</script>
@endpush