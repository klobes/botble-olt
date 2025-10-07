<div class="modal fade" id="view-olt-details-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::olt.view_details') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::olt.basic_info') }}</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.name') }}:</strong></td>
                                <td id="detail-name"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }}:</strong></td>
                                <td id="detail-ip"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.model') }}:</strong></td>
                                <td id="detail-model"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.firmware') }}:</strong></td>
                                <td id="detail-firmware"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.uptime') }}:</strong></td>
                                <td id="detail-uptime"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::olt.performance_metrics') }}</strong></h6>
                        <table class="table table-sm">
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.cpu_usage') }}:</strong></td>
                                <td id="detail-cpu"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.memory_usage') }}:</strong></td>
                                <td id="detail-memory"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.temperature') }}:</strong></td>
                                <td id="detail-temperature"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.status') }}:</strong></td>
                                <td id="detail-status"></td>
                            </tr>
                            <tr>
                                <td><strong>{{ trans('plugins/fiberhome-olt-manager::olt.last_polled') }}:</strong></td>
                                <td id="detail-polled"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6><strong>{{ trans('plugins/fiberhome-olt-manager::olt.port_statistics') }}</strong></h6>
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.slot') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.port') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.status') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.onu_count') }}</th>
                                    <th>{{ trans('plugins/fiberhome-olt-manager::olt.bandwidth_usage') }}</th>
                                </tr>
                            </thead>
                            <tbody id="port-statistics">
                                <!-- Port statistics will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    {{ trans('plugins/fiberhome-olt-manager::olt.close') }}
                </button>
            </div>
        </div>
    </div>
</div>

