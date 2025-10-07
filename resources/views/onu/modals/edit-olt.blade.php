<div class="modal fade" id="edit-olt-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="edit-olt-form">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit-id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::olt.edit_olt') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-name">{{ trans('plugins/fiberhome-olt-manager::olt.name') }}</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-ip">{{ trans('plugins/fiberhome-olt-manager::olt.ip_address') }}</label>
                        <input type="text" class="form-control" id="edit-ip" name="ip_address" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-model">{{ trans('plugins/fiberhome-olt-manager::olt.model') }}</label>
                        <select class="form-control" id="edit-model" name="model" required>
                            <option value="">{{ trans('plugins/fiberhome-olt-manager::olt.select_model') }}</option>
                            <option value="AN5516-01">AN5516-01</option>
                            <option value="AN5516-02">AN5516-02</option>
                            <option value="AN5516-04">AN5516-04</option>
                            <option value="AN5516-06">AN5516-06</option>
                            <option value="AN5516-10">AN5516-10</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-snmp-community">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_community') }}</label>
                        <input type="text" class="form-control" id="edit-snmp-community" name="snmp_community">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-snmp-version">{{ trans('plugins/fiberhome-olt-manager::olt.snmp_version') }}</label>
                        <select class="form-control" id="edit-snmp-version" name="snmp_version">
                            <option value="2c">SNMPv2c</option>
                            <option value="3">SNMPv3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-description">{{ trans('plugins/fiberhome-olt-manager::olt.description') }}</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::olt.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::olt.update_olt') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
