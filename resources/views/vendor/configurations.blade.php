@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.title') }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('fiberhome-olt.vendor.create-configuration') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.add') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($configurations->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.empty') }}
                        </div>
                    @else
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.vendor') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.model') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.capabilities') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.configurations.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($configurations as $config)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ $config->vendor }}</span>
                                    </td>
                                    <td>{{ $config->model }}</td>
                                    <td>
                                        @if(isset($config->capabilities['max_onus']))
                                            <small>Max ONUs: {{ $config->capabilities['max_onus'] }}</small><br>
                                        @endif
                                        @if(isset($config->capabilities['max_distance']))
                                            <small>Max Distance: {{ $config->capabilities['max_distance'] }}m</small><br>
                                        @endif
                                        @if(isset($config->capabilities['supports_qinq']) && $config->capabilities['supports_qinq'])
                                            <span class="badge badge-success">QinQ</span>
                                        @endif
                                        @if(isset($config->capabilities['supports_vlan']) && $config->capabilities['supports_vlan'])
                                            <span class="badge badge-success">VLAN</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info" onclick="viewConfiguration({{ $config->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="#" class="btn btn-sm btn-warning" onclick="editConfiguration({{ $config->id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteConfiguration({{ $config->id }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewConfiguration(id) {
            // Implementation for viewing configuration details
            alert('Configuration viewer will be implemented in v1.5.0');
        }

        function editConfiguration(id) {
            // Implementation for editing configuration
            alert('Configuration editor will be implemented in v1.5.0');
        }

        function deleteConfiguration(id) {
            if (confirm('Are you sure you want to delete this configuration?')) {
                // Implementation for deleting configuration
                alert('Configuration deletion will be implemented in v1.5.0');
            }
        }
    </script>
@endsection