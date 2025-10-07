@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.title') }}</h4>
                    <div class="card-tools">
                        <a href="{{ route('fiberhome-olt.vendor.create-onu-type') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.add') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($onuTypes->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            {{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.empty') }}
                        </div>
                    @else
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.vendor') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.model') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.type_name') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.ports') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.capabilities') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::fiberhome-olt.vendor.onu_types.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($onuTypes as $type)
                                <tr>
                                    <td>
                                        <span class="badge badge-primary">{{ $type->vendor }}</span>
                                    </td>
                                    <td>{{ $type->model }}</td>
                                    <td>{{ $type->type_name }}</td>
                                    <td>
                                        <small>
                                            Ethernet: {{ $type->ethernet_ports }}<br>
                                            POTS: {{ $type->pots_ports }}<br>
                                            CATV: {{ $type->catv_ports }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($type->wifi_support)
                                            <span class="badge badge-success">WiFi</span>
                                        @endif
                                        @if(isset($type->capabilities['supports_voice']) && $type->capabilities['supports_voice'])
                                            <span class="badge badge-info">Voice</span>
                                        @endif
                                        @if(isset($type->capabilities['supports_data']) && $type->capabilities['supports_data'])
                                            <span class="badge badge-primary">Data</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info" onclick="viewOnuType({{ $type->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="#" class="btn btn-sm btn-warning" onclick="editOnuType({{ $type->id }})">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteOnuType({{ $type->id }})">
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
        function viewOnuType(id) {
            // Implementation for viewing ONU type details
            alert('ONU type viewer will be implemented in v1.5.0');
        }

        function editOnuType(id) {
            // Implementation for editing ONU type
            alert('ONU type editor will be implemented in v1.5.0');
        }

        function deleteOnuType(id) {
            if (confirm('Are you sure you want to delete this ONU type?')) {
                // Implementation for deleting ONU type
                alert('ONU type deletion will be implemented in v1.5.0');
            }
        }
    </script>
@endsection