<div class="modal fade" id="add-cable-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="add-cable-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('plugins/fiberhome-olt-manager::topology.add_cable') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cable-name">{{ trans('plugins/fiberhome-olt-manager::topology.cable_name') }}</label>
                        <input type="text" class="form-control" id="cable-name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from-device-type">{{ trans('plugins/fiberhome-olt-manager::topology.from_device') }}</label>
                                <select class="form-control" id="from-device-type" name="from_device_type" required>
                                    <option value="olt">{{ trans('plugins/fiberhome-olt-manager::topology.olt') }}</option>
                                    <option value="onu">{{ trans('plugins/fiberhome-olt-manager::topology.onu') }}</option>
                                    <option value="junction_box">{{ trans('plugins/fiberhome-olt-manager::topology.junction_box') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from-device-id">{{ trans('plugins/fiberhome-olt-manager::topology.from_device_id') }}</label>
                                <select class="form-control" id="from-device-id" name="from_device_id" required>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from-port">{{ trans('plugins/fiberhome-olt-manager::topology.from_port') }}</label>
                                <input type="number" class="form-control" id="from-port" name="from_port" min="1" max="128">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to-device-type">{{ trans('plugins/fiberhome-olt-manager::topology.to_device') }}</label>
                                <select class="form-control" id="to-device-type" name="to_device_type" required>
                                    <option value="olt">{{ trans('plugins/fiberhome-olt-manager::topology.olt') }}</option>
                                    <option value="onu">{{ trans('plugins/fiberhome-olt-manager::topology.onu') }}</option>
                                    <option value="junction_box">{{ trans('plugins/fiberhome-olt-manager::topology.junction_box') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to-device-id">{{ trans('plugins/fiberhome-olt-manager::topology.to_device_id') }}</label>
                                <select class="form-control" id="to-device-id" name="to_device_id" required>
                                    <!-- Options will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to-port">{{ trans('plugins/fiberhome-olt-manager::topology.to_port') }}</label>
                                <input type="number" class="form-control" id="to-port" name="to_port" min="1" max="128">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cable-length">{{ trans('plugins/fiberhome-olt-manager::topology.length_m') }}</label>
                                <input type="number" class="form-control" id="cable-length" name="length" min="0" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cable-fiber-count">{{ trans('plugins/fiberhome-olt-manager::topology.fiber_count') }}</label>
                                <input type="number" class="form-control" id="cable-fiber-count" name="fiber_count" min="1" max="144" value="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cable-color">{{ trans('plugins/fiberhome-olt-manager::topology.color') }}</label>
                        <select class="form-control" id="cable-color" name="color">
                            <option value="yellow">Yellow</option>
                            <option value="orange">Orange</option>
                            <option value="aqua">Aqua</option>
                            <option value="blue">Blue</option>
                            <option value="green">Green</option>
                            <option value="red">Red</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cable-status">{{ trans('plugins/fiberhome-olt-manager::topology.status') }}</label>
                        <select class="form-control" id="cable-status" name="status">
                            <option value="active">{{ trans('plugins/fiberhome-olt-manager::topology.active') }}</option>
                            <option value="inactive">{{ trans('plugins/fiberhome-olt-manager::topology.inactive') }}</option>
                            <option value="maintenance">{{ trans('plugins/fiberhome-olt-manager::topology.maintenance') }}</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cable-notes">{{ trans('plugins/fiberhome-olt-manager::topology.notes') }}</label>
                        <textarea class="form-control" id="cable-notes" name="notes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="cable-splicing-points">{{ trans('plugins/fiberhome-olt-manager::topology.splicing_points') }}</label>
                        <input type="number" class="form-control" id="cable-splicing-points" name="splicing_points" min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('plugins/fiberhome-olt-manager::topology.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ trans('plugins/fiberhome-olt-manager::topology.add_cable') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('add-cable-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch('{{ route("fiberhome.topology.update-cable") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            Botble.showSuccess(result.message);
            $('#add-cable-modal').modal('hide');
            window.topologyManager.loadTopology();
            $('#add-cable-form')[0].reset();
        } else {
            Botble.showError(result.message);
        }
    } catch (error) {
        Botble.showError('{{ trans("plugins/fiberhome-olt-manager::topology.add_cable_error") }}');
    }
});

// Dynamic population of device dropdowns
document.getElementById('from-device-type').addEventListener('change', async function() {
    const deviceType = this.value;
    const select = document.getElementById('from-device-id');
    
    try {
        const response = await fetch(`{{ route("fiberhome.topology.devices") }}?type=${deviceType}`);
        const data = await response.json();
        
        select.innerHTML = '';
        data.data.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = device.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading devices:', error);
    }
});

document.getElementById('to-device-type').addEventListener('change', async function() {
    const deviceType = this.value;
    const select = document.getElementById('to-device-id');
    
    try {
        const response = await fetch(`{{ route("fiberhome.topology.devices") }}?type=${deviceType}`);
        const data = await response.json();
        
        select.innerHTML = '';
        data.data.forEach(device => {
            const option = document.createElement('option');
            option.value = device.id;
            option.textContent = device.name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading devices:', error);
    }
});
</script>