@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h4 class="title">{{ trans('plugins/fiberhome-olt-manager::bandwidth.bandwidth_profiles') }}</h4>
                </div>
            </div>
            
            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="bandwidth-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.id') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.name') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_speed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_speed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.download_guaranteed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.upload_guaranteed') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.priority') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.created_at') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::bandwidth.actions') }}</th>
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

    @include('plugins/fiberhome-olt-manager::bandwidth-profile.modals.create')
    @include('plugins/fiberhome-olt-manager::bandwidth-profile.modals.edit')
    @include('plugins/fiberhome-olt-manager::bandwidth-profile.modals.assign')
@endsection

@push('footer')
    <script>
        $(document).ready(function() {
            $('#bandwidth-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("fiberhome.bandwidth.datatable") }}',
                    type: 'GET'
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'download_speed', name: 'download_speed' },
                    { data: 'upload_speed', name: 'upload_speed' },
                    { data: 'download_guaranteed', name: 'download_guaranteed' },
                    { data: 'upload_guaranteed', name: 'upload_guaranteed' },
                    { data: 'priority', name: 'priority' },
                    { data: 'status', name: 'status' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                responsive: true,
                order: [[0, 'desc']]
            });
        });
    </script>
@endpush