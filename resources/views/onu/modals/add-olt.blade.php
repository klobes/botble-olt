<div class="modal fade" id="add-olt-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="add-olt-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-plus"></i> {{ trans('plugins/fiberhome-olt-manager::olt.add_olt') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Connection Test Status -->
                    <div id="connection-status" class="alert" style="display: none;">
                        <i class="fa fa-info-circle"></i>
                        <span id="connection-message"></span>
                    </div>
                    
                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fa fa-info-circle"></i> Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-name">{{ trans('plugins/fiberhome-olt-manager::olt.name') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add-name" name="name" required placeholder="e.g., OLT-Main-01">
                                        <small class="form-text text-muted">Unique name for this OLT device</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-ip">{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add-ip" name="ip_address" required placeholder="192.168.1.100">
                                        <small class="form-text text-muted">IP address of the OLT device</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-location">{{ trans('plugins/fiberhome-olt-manager::olt.location') }}</label>
                                        <input type="text" class="form-control" id="add-location" name="location" placeholder="e.g., Main Office">
                                        <small class="form-text text-muted">Physical location of the device</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-description">{{ trans('plugins/fiberhome-olt-manager::olt.description') }}</label>
                                        <input type="text" class="form-control" id="add-description" name="description" placeholder="Optional description">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vendor & Model Selection -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fa fa-cog"></i> Device Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-vendor">{{ trans('plugins/fiberhome-olt-manager::olt.vendor') }} <span class="text-danger">*</span></label>
                                        <select class="form-control" id="add-vendor" name="vendor" required>
                                            <option value="">{{ trans('plugins/fiberhome-olt-manager::olt.select_vendor') }}</option>
                                            <option value="fiberhome">FiberHome</option>
                                            <option value="huawei">Huawei</option>
                                            <option value="zte">ZTE</option>
                                        </select>
                                        <small class="form-text text-muted">Select the OLT manufacturer</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="add-model">{{ trans('plugins/fiberhome-olt-manager::olt.model') }} <span class="text-danger">*</span></label>
                                        <select class="form-control" id="add-model" name="model" required disabled>
                                            <option value="">{{ trans('plugins/fiberhome-olt-manager::olt.select_vendor_first') }}</option>
                                        </select>
                                        <small class="form-text text-muted">Select the OLT model</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Model Details (shown after model selection) -->
                            <div id="model-details" class="alert alert-info" style="display: none;">
                                <h6><i class="fa fa-info-circle"></i> Model Information</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Description:</strong><br>
                                        <span id="model-description">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Max Ports:</strong><br>
                                        <span id="model-max-ports">-</span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Max ONUs:</strong><br>
                                        <span id="model-max-onus">-</span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <strong>Technology:</strong><br>
                                    <span id="model-technology">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- SNMP Configuration -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fa fa-network-wired"></i> SNMP Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="add-snmp-community">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_community') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="add-snmp-community" name="snmp_community" value="public" required>
                                        <small class="form-text text-muted">SNMP community string</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="add-snmp-version">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_version') }} <span class="text-danger">*</span></label>
                                        <select class="form-control" id="add-snmp-version" name="snmp_version" required>
                                            <option value="1">v1</option>
                                            <option value="2c" selected>v2c</option>
                                            <option value="3">v3</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="add-snmp-port">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_port') }}</label>
                                        <input type="number" class="form-control" id="add-snmp-port" name="snmp_port" value="161" min="1" max="65535">
                                        <small class="form-text text-muted">Default: 161</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-info btn-block" id="test-connection-btn">
                                        <i class="fa fa-plug"></i> Test Connection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> {{ trans('core/base::forms.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="save-olt-btn">
                        <i class="fa fa-save"></i> {{ trans('core/base::forms.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

