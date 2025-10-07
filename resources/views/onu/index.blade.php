@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="page-content">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fas fa-server"></i>
                    <span class="caption-subject bold uppercase">OLT Devices</span>
                </div>
                <div class="actions">
                    <a href="{{ route('fiberhome.olt-devices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add OLT Device
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Brand</th>
                                <th>Model</th>
                                <th>IP Address</th>
                                <th>SNMP</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($olts as $olt)
                                <tr>
                                    <td><strong>{{ $olt['name'] }}</strong></td>
                                    <td>{{ ucfirst($olt['brand']) }}</td>
                                    <td>{{ $olt['model'] }}</td>
                                    <td><code>{{ $olt['ip_address'] }}</code></td>
                                    <td>{{ strtoupper($olt['snmp_version']) }}</td>
                                    <td>{{ $olt['location'] ?? '-' }}</td>
                                    <td>
                                        @if($olt['status'] === 'online')
                                            <span class="label label-success">Online</span>
                                        @elseif($olt['status'] === 'offline')
                                            <span class="label label-danger">Offline</span>
                                        @else
                                            <span class="label label-warning">{{ ucfirst($olt['status']) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('fiberhome.olt-devices.show', $olt['id']) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('fiberhome.olt-devices.edit', $olt['id']) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('fiberhome.olt-devices.destroy', $olt['id']) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No OLT devices found. <a href="{{ route('fiberhome.olt-devices.create') }}">Add your first OLT</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection