@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h4 class="title">{{ trans('plugins/fiberhome-olt-manager::settings.title') }}</h4>
                </div>
            </div>
            
            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <form method="POST" action="{{ route('fiberhome.settings.update') }}" class="setting-form">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="snmp_timeout">{{ trans('plugins/fiberhome-olt-manager::settings.snmp_timeout') }}</label>
                            <input type="number" class="form-control" id="snmp_timeout" name="snmp_timeout" 
                                   value="{{ setting('fiberhome_snmp_timeout', 3000) }}" min="1000" max="30000">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.snmp_timeout_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="snmp_retries">{{ trans('plugins/fiberhome-olt-manager::settings.snmp_retries') }}</label>
                            <input type="number" class="form-control" id="snmp_retries" name="snmp_retries" 
                                   value="{{ setting('fiberhome_snmp_retries', 3) }}" min="1" max="10">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.snmp_retries_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="polling_interval">{{ trans('plugins/fiberhome-olt-manager::settings.polling_interval') }}</label>
                            <input type="number" class="form-control" id="polling_interval" name="polling_interval" 
                                   value="{{ setting('fiberhome_polling_interval', 300) }}" min="60" max="3600">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.polling_interval_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="alert_threshold_cpu">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_cpu') }}</label>
                            <input type="number" class="form-control" id="alert_threshold_cpu" name="alert_threshold_cpu" 
                                   value="{{ setting('fiberhome_alert_threshold_cpu', 80) }}" min="50" max="100">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_cpu_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="alert_threshold_memory">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_memory') }}</label>
                            <input type="number" class="form-control" id="alert_threshold_memory" name="alert_threshold_memory" 
                                   value="{{ setting('fiberhome_alert_threshold_memory', 85) }}" min="50" max="100">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_memory_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="alert_threshold_temperature">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_temperature') }}</label>
                            <input type="number" class="form-control" id="alert_threshold_temperature" name="alert_threshold_temperature" 
                                   value="{{ setting('fiberhome_alert_threshold_temperature', 70) }}" min="40" max="100">
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.alert_threshold_temperature_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_auto_discovery" name="enable_auto_discovery" 
                                       {{ setting('fiberhome_enable_auto_discovery', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_auto_discovery">
                                    {{ trans('plugins/fiberhome-olt-manager::settings.enable_auto_discovery') }}
                                </label>
                            </div>
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.enable_auto_discovery_help') }}</small>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable_alerts" name="enable_alerts" 
                                       {{ setting('fiberhome_enable_alerts', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_alerts">
                                    {{ trans('plugins/fiberhome-olt-manager::settings.enable_alerts') }}
                                </label>
                            </div>
                            <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::settings.enable_alerts_help') }}</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ trans('plugins/fiberhome-olt-manager::settings.save_settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

    <script>
        $(document).ready(function() {
            $('.setting-form').on('submit', function(e) {
                e.preventDefault();
                
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            Botble.showSuccess(response.message);
                        } else {
                            Botble.showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        Botble.showError('trans("plugins/fiberhome-olt-manager::settings.save_error")');
                    }
                });
            });
        });
    </script>
