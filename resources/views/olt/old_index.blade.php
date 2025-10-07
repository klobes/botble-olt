@extends('core/base::layouts.master')

@section('content')
    <div class="max-width-1200">
        <div class="flexbox-annotated-section">
            <div class="flexbox-annotated-section-annotation">
                <div class="annotated-section-title pd-all-20">
                    <h4 class="title">{{ trans('plugins/fiberhome-olt-manager::olt.olt_management') }}</h4>
                </div>
            </div>
            
            <div class="flexbox-annotated-section-content">
                <div class="wrapper-content pd-all-20">
                    <!-- Add OLT Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-olt-modal">
                            <i class="fa fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::olt.add_olt') }}
                        </button>
                        <button type="button" class="btn btn-info" id="refresh-table">
                            <i class="fa fa-refresh"></i> {{ trans('plugins/fiberhome-olt-manager::olt.refresh') }}
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="olt-table">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.id') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.name') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.model') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.vendor') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.onu_count') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.actions') }}</th>
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

    @include('plugins/fiberhome-olt-manager::olt.modals.add-olt')
    @include('plugins/fiberhome-olt-manager::olt.modals.edit-olt')
    @include('plugins/fiberhome-olt-manager::olt.modals.view-details')
@endsection

