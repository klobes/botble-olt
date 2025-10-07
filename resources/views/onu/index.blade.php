@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h4 class="title">{{ trans('plugins/fiberhome-olt-manager::onu.onu_management') }}</h4>
                </div>
            </div>
            
            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="onu-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.id') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.serial_number') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.olt') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.slot') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.port') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.onu_id') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.rx_power') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.tx_power') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.distance') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.last_seen') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::onu.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('plugins/fiberhome-olt-manager::onu.modals.view-details')
    @include('plugins/fiberhome-olt-manager::onu.modals.edit-onu')
    @include('plugins/fiberhome-olt-manager::onu.modals.configure-onu')
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            $('#onu-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("fiberhome.onu.datatable") }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'serial_number', name: 'serial_number' },
                    { data: 'olt_name', name: 'olt.name' },
                    { data: 'slot', name: 'slot' },
                    { data: 'port', name: 'port' },
                    { data: 'onu_id', name: 'onu_id' },
                    { data: 'status', name: 'status' },
                    { data: 'rx_power', name: 'rx_power' },
                    { data: 'tx_power', name: 'tx_power' },
                    { data: 'distance', name: 'distance' },
                    { data: 'last_seen', name: 'last_seen' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[0, 'desc']]
            });
        });
    </script>
@endpush