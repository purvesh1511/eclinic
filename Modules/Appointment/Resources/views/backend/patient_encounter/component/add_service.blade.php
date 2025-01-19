<div class="card bg-body">
    <div class="card-body border-top">
        <div class="row">

            <input type="hidden" value={{ $encounter_id }} id="billing_encounter_id" name="billing_encounter_id">

            <div class=" form-group col-md-3">

                <label class="form-label m-0" for="category-discount">{{ __('appointment.lbl_service') }}
                    <span class="text-danger">*</span></label>
                    <div class="">
                        <select id="service_id" name="service_id" class="form-control select2"
                            placeholder="{{ __('appointment.select_service') }}" data-filter="select">
                            <option value="">{{ __('appointment.select_service') }}</option>

                        </select>
                    </div>



            </div>

            <div class="form-group col-md-3">
                <label class="form-label" for="clinic_id">
                    {{ __('clinic.price') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">

                    <input type="number" name="charges" id="charges" class="form-control"
                        placeholder="{{ __('clinic.price') }}" value="{{ old('charges') }}">
                </div>
                @if ($errors->has('charges'))
                    <span class="text-danger">{{ $errors->first('charges') }}</span>
                @endif
            </div>

            <div class="form-group col-md-3">
                <label class="form-label">
                    {{ __('product.quantity') }} <span class="text-danger">*</span>
                </label>
                <input type="number" name="quantity" id="quantity" class="form-control"
                    placeholder="{{ __('product.quantity') }}" value="{{ old('quantity') }}" min="1">
                @if ($errors->has('quantity'))
                    <span class="text-danger">{{ $errors->first('quantity') }}</span>
                @endif
            </div>

            <div class="form-group col-md-3">
                <label class="form-label">
                    {{ __('appointment.total') }} <span class="text-danger">*</span>
                </label>
                <input type="text" name="total" id="total" class="form-control"
                    placeholder="{{ __('appointment.total') }}" value="{{ old('total') }}" readonly>
                @if ($errors->has('total'))
                    <span class="text-danger">{{ $errors->first('total') }}</span>
                @endif
            </div>
            <input type="hidden" id="discount_value" name="discount_value">
            <input type="hidden" id="discount_type" name="discount_type">
            <input type="hidden" id="billing_id" name="billing_id" value="{{ $billing_id }}">



        </div>
    </div>
    <div class="card-footer border-top">
        <div class="d-flex align-items-center justify-content-end gap-3">

            <button class="btn btn-primary" type="button" id="saveServiceForm">
                {{ __('appointment.save') }}
            </button>

        </div>
    </div>
</div>

@push('after-scripts')
    <script>
        $(document).ready(function() {
            // Get encounter_id from hidden input
            var encounterId = $('#billing_encounter_id').val();
            var baseUrl = '{{ url('/') }}';



            // Fetch the service list using AJAX
            $.ajax({
                url: baseUrl + '/app/services/index_list?encounter_id=' + encounterId,
                method: 'GET',

                success: function(response) {


                    if (response) {
                        // Populate the dropdown
                        var serviceOptions =
                            '<option value="">{{ __('appointment.select_service') }}</option>';
                        response.forEach(function(service) {
                            serviceOptions +=
                                `<option value="${service.id}">${service.name}</option>`;
                        });
                        $('#service_id').html(serviceOptions);
                    } else {

                        console.error('Failed to fetch services.');
                    }
                },
                error: function(error) {
                    console.error('Error fetching services:', error);
                }
            });
            $('#service_id').on('change', function() {
                var encounterId = $('#billing_encounter_id').val();
                var selectedServiceId = $(this).val();
                if (selectedServiceId) {
                    // Make an API call when the service is selected
                    $.ajax({
                        url: baseUrl + '/app/services/service-details?service_id=' +
                            selectedServiceId + '&encounter_id=' + encounterId,
                        method: 'GET',
                        success: function(serviceDetails) {
                            if (serviceDetails) {
                                if (serviceDetails && serviceDetails.data.doctor_service &&
                                    Array.isArray(serviceDetails.data.doctor_service) &&
                                    serviceDetails.data.doctor_service.length > 0) {
                                    console.log(serviceDetails);
                                    // Extract the first doctor_service item
                                    var doctorService = serviceDetails.data.doctor_service[0];
                                    console.log(doctorService);
                                    // Update fields dynamically based on the service
                                    $('#charges').val(doctorService.charges); // Set charges
                                    $('#quantity').val(1); // Reset quantity to 1
                                    var total = doctorService.charges * 1; // Calculate total
                                    $('#total').val(total.toFixed(2)); // Update total field
                                    $('#discount_value').val(serviceDetails.data
                                    .discount_value);
                                    $('#discount_type').val(serviceDetails.data.discount_type);
                                } else if (serviceDetails && serviceDetails.data.charges) {
                                    // Fallback to use `charges` if `doctor_service` is not available
                                    $('#charges').val(serviceDetails.data.charges);
                                    $('#quantity').val(1);
                                    var total = serviceDetails.data.charges * 1;
                                    $('#total').val(total.toFixed(2));
                                    $('#discount_value').val(serviceDetails.data
                                    .discount_value);
                                    $('#discount_type').val(serviceDetails.data.discount_type);
                                } else {
                                    // Handle case where data is insufficient
                                    $('#charges').val('');
                                    $('#quantity').val('');
                                    $('#total').val('');
                                    $('#discount_value').val('');
                                    $('#discount_type').val('');
                                    console.error('No doctor_service or charges data found.');
                                }
                            } else {
                                console.error('Failed to fetch service details.');
                            }
                        },
                        error: function(error) {
                            console.error('Error fetching service details:', error);
                        }
                    });
                } else {
                    // Reset fields if no service is selected
                    $('#charges').val('');
                    $('#quantity').val('');
                    $('#total').val('');
                    $('#discount_value').val('');
                    $('#discount_type').val('');

                }
            });

            $('#quantity, #charges').on('input', function() {
                var quantity = parseFloat($('#quantity').val()) || 0;
                var charges = parseFloat($('#charges').val()) || 0;
                var total = quantity * charges;
                $('#total').val(total.toFixed(2));
            });

            $('#saveServiceForm').on('click', function() {
                var encounterId = $('#billing_encounter_id').val();
                var serviceId = $('#service_id').val();
                var charges = $('#charges').val();
                var quantity = $('#quantity').val();
                var total = $('#total').val();
                var discount_value = $('#discount_value').val();
                var discount_type = $('#discount_type').val();
                var billing_id = $('#billing_id').val();



                // Perform basic validation
                if (!serviceId || !charges || !quantity || !total) {
                    alert('{{ __('appointment.fill_required_fields') }}');
                    return;
                }

                // Prepare the data for the API call
                var formData = {
                    encounter_id: encounterId,
                    item_id: serviceId,
                    service_amount: charges,
                    quantity: quantity,
                    discount_value: discount_value,
                    discount_type: discount_type,
                    billing_id: billing_id,
                    total_amount: total,
                    type: 'encounter_details',

                    _token: '{{ csrf_token() }}' // Include CSRF token for security
                };

                // Make the API call
                $.ajax({
                    url: baseUrl + '/app/billing-record/save-billing-items',
                    method: 'post',
                    data: formData,
                    success: function(response) {
                        if (response) {

                            document.getElementById('Service_list').innerHTML = ''

                            document.getElementById('Service_list').innerHTML = response.html;

                            const button = document.getElementById('toggleButton');
                            const collapse = document.getElementById('collapseExample');

                            const bootstrapCollapse = new bootstrap.Collapse(collapse);
                            bootstrapCollapse.hide();

                            $('#service_id').val(null).trigger('change');
                            $('#charges').val('');
                            $('#quantity').val('');
                            $('#total').val('');
                            $('#discount_value').val('');
                            $('#discount_type').val('');
                            $('#service_amount').text(currencyFormat(response.service_details
                                .service_total));
                            $('#tax_amount').text(currencyFormat(response.service_details
                                .total_tax));
                            $('#total_payable_amount').text(currencyFormat(response
                                .service_details.total_amount));
                            $('#total_service_amount').val(response.service_details
                                .service_total);
                            $('#total_tax_amount').val(response.service_details.total_tax);
                            $('#total_amount').val(response.service_details.total_amount);

                            $('#discount_amount').text(currencyFormat(response.service_details.final_discount_amount));


                        } else {

                        }
                    },
                    error: function(error) {
                        console.error('Error saving billing details:', error);
                        alert('{{ __('appointment.saving_failed') }}');
                    }
                });
            });

        });
    </script>
@endpush
