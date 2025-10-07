@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1>{{ trans('plugins/fiberhome-olt-manager::olt.edit') }}</h1>
                        <p class="text-muted">{{ trans('plugins/fiberhome-olt-manager::olt.edit_description') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('fiberhome.olt.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ trans('plugins/fiberhome-olt-manager::olt.back') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('fiberhome.olt.update', $olt->id) }}" method="POST" id="olt-form">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::olt.basic_info') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="required">{{ trans('plugins/fiberhome-olt-manager::olt.name') }}</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $olt->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="model" class="required">{{ trans('plugins/fiberhome-olt-manager::olt.model') }}</label>
                                    <select class="form-control @error('model') is-invalid @enderror" 
                                            id="model" 
                                            name="model" 
                                            required>
                                        <option value="">{{ trans('plugins/fiberhome-olt-manager::olt.select_model') }}</option>
                                        <option value="AN5516-01" {{ old('model', $olt->model) === 'AN5516-01' ? 'selected' : '' }}>AN5516-01</option>
                                        <option value="AN5516-04" {{ old('model', $olt->model) === 'AN5516-04' ? 'selected' : '' }}>AN5516-04</option>
                                        <option value="AN5516-06" {{ old('model', $olt->model) === 'AN5516-06' ? 'selected' : '' }}>AN5516-06</option>
                                        <option value="AN6000-17" {{ old('model', $olt->model) === 'AN6000-17' ? 'selected' : '' }}>AN6000-17</option>
                                    </select>
                                    @error('model')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ip_address" class="required">{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }}</label>
                                    <input type="text" 
                                           class="form-control @error('ip_address') is-invalid @enderror" 
                                           id="ip_address" 
                                           name="ip_address" 
                                           value="{{ old('ip_address', $olt->ip_address) }}" 
                                           placeholder="192.168.1.1"
                                           required>
                                    @error('ip_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="snmp_port">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_port') }}</label>
                                    <input type="number" 
                                           class="form-control @error('snmp_port') is-invalid @enderror" 
                                           id="snmp_port" 
                                           name="snmp_port" 
                                           value="{{ old('snmp_port', $olt->snmp_port) }}" 
                                           min="1" 
                                           max="65535">
                                    @error('snmp_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="snmp_version" class="required">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_version') }}</label>
                                    <select class="form-control @error('snmp_version') is-invalid @enderror" 
                                            id="snmp_version" 
                                            name="snmp_version" 
                                            required>
                                        <option value="2c" {{ old('snmp_version', $olt->snmp_version) === '2c' ? 'selected' : '' }}>v2c</option>
                                        <option value="3" {{ old('snmp_version', $olt->snmp_version) === '3' ? 'selected' : '' }}>v3</option>
                                    </select>
                                    @error('snmp_version')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="snmp_community" class="required">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_community') }}</label>
                                    <input type="text" 
                                           class="form-control @error('snmp_community') is-invalid @enderror" 
                                           id="snmp_community" 
                                           name="snmp_community" 
                                           value="{{ old('snmp_community', $olt->snmp_community) }}" 
                                           required>
                                    @error('snmp_community')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">{{ trans('plugins/fiberhome-olt-manager::olt.description') }}</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3">{{ old('description', $olt->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::olt.location_info') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">{{ trans('plugins/fiberhome-olt-manager::olt.location') }}</label>
                                    <input type="text" 
                                           class="form-control @error('location') is-invalid @enderror" 
                                           id="location" 
                                           name="location" 
                                           value="{{ old('location', $olt->location) }}">
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="latitude">{{ trans('plugins/fiberhome-olt-manager::olt.latitude') }}</label>
                                    <input type="text" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude', $olt->latitude) }}" 
                                           placeholder="41.3275">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="longitude">{{ trans('plugins/fiberhome-olt-manager::olt.longitude') }}</label>
                                    <input type="text" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude', $olt->longitude) }}" 
                                           placeholder="19.8187">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::olt.configuration') }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_distance">{{ trans('plugins/fiberhome-olt-manager::olt.max_distance') }}</label>
                                    <input type="number" 
                                           class="form-control @error('max_distance') is-invalid @enderror" 
                                           id="max_distance" 
                                           name="max_distance" 
                                           value="{{ old('max_distance', $olt->max_distance) }}" 
                                           min="1" 
                                           max="100">
                                    <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::olt.max_distance_help') }}</small>
                                    @error('max_distance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="polling_interval">{{ trans('plugins/fiberhome-olt-manager::olt.polling_interval') }}</label>
                                    <input type="number" 
                                           class="form-control @error('polling_interval') is-invalid @enderror" 
                                           id="polling_interval" 
                                           name="polling_interval" 
                                           value="{{ old('polling_interval', $olt->polling_interval) }}" 
                                           min="60" 
                                           max="3600">
                                    <small class="form-text text-muted">{{ trans('plugins/fiberhome-olt-manager::olt.polling_interval_help') }}</small>
                                    @error('polling_interval')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">{{ trans('plugins/fiberhome-olt-manager::olt.status') }}</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status">
                                        <option value="online" {{ old('status', $olt->status) === 'online' ? 'selected' : '' }}>
                                            {{ trans('plugins/fiberhome-olt-manager::olt.online') }}
                                        </option>
                                        <option value="offline" {{ old('status', $olt->status) === 'offline' ? 'selected' : '' }}>
                                            {{ trans('plugins/fiberhome-olt-manager::olt.offline') }}
                                        </option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', $olt->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        {{ trans('plugins/fiberhome-olt-manager::olt.is_active') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('fiberhome.olt.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> {{ trans('plugins/fiberhome-olt-manager::olt.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ trans('plugins/fiberhome-olt-manager::olt.update') }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Form validation
        $('#olt-form').on('submit', function(e) {
            const ipAddress = $('#ip_address').val();
            const ipPattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            
            if (!ipPattern.test(ipAddress)) {
                e.preventDefault();
                toastr.error('{{ trans('plugins/fiberhome-olt-manager::olt.invalid_ip') }}');
                $('#ip_address').addClass('is-invalid');
                return false;
            }
        });
    });
</script>
@endpush