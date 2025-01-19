<div class="modal fade" id="addMedicalreport" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('clinic.add_medical_report') }}</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <form method="post" id="medical-report-submit" class="requires-validation" novalidate
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row" id="medical-report-model">


                        <input type="hidden" name="id" id="medical_id" >
                        <input type="hidden" name="user_id" id="medical_user_id" value="{{ $data['user_id'] }}">
                        <input type="hidden" name="encounter_id" id="medical_encounter_id" value="{{ $data['id'] }}">


                        <div class="form-group">
                            <label class="form-label col-md-12">
                                {{ __('clinic.lbl_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="medical_name"  class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_name') }}" required>
                            <div class="invalid-feedback">
                                {{ __('Name field is required.') }}
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label col-md-12">
                                {{ __('clinic.lbl_date') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="date" id="date" class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_date') }}" required>

                            <div class="invalid-feedback" id="date-error"> Date field is required</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label col-md-12">
                                {{ __('clinic.lbl_file') }}
                            </label>
                            <input type="file" name="file_url" id="file_url" class="form-control col-md-12"
                                placeholder="{{ __('clinic.lbl_file_url') }}" value="" >
                            <div class="invalid-feedback">
                                {{ __('file is a required field.') }}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

    @push('after-scripts')
        <script>
            $(document).ready(function() {

                flatpickr('#date', {
                    dateFormat: "Y-m-d",

                });

                var baseUrl = '{{ url('/') }}';

                $('#medical-report-submit').on('submit', function(event) {
                    event.preventDefault();

                    const dateField = $('#date');
                    const dateError = $('#date-error');

                    dateField.removeClass('is-invalid');
                    dateError.hide();

                    if (!dateField.val()) {
                        dateField.addClass('is-invalid');
                        dateError.show();
                    }
                    let form = $(this)[0];
                    if (form.checkValidity() === false || dateField.hasClass('is-invalid')) {
                        event.stopPropagation();
                        form.classList.add('was-validated');
                        return;
                    }

                    let formData = new FormData(this);

                    let hasId = formData.has('id') && formData.get('id') !== '';
                    let id = formData.get('id') || null;

                    let route = hasId ?
                        `${baseUrl}/app/encounter/update-medical-report/${id}` :
                        `${baseUrl}/app/encounter/save-medical-report`;

                    $.ajax({
                        url: route,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.html) {
                                document.getElementById('medical_report_table').innerHTML = response
                                    .html;
                                $('#addMedicalreport').modal('hide');
                                $('#medical-report-submit')[0].reset();
                                $('#medical_id').val('');
                                $('#medical-report-submit')[0].classList.remove('was-validated');
                                window.successSnackbar(
                                    `Medical report ${hasId ? 'updated' : 'added'} successfully`
                                    );
                            } else {
                                window.errorSnackbar('Something went wrong! Please check.');
                            }
                        },
                        error: function(xhr) {
                            alert('An error occurred: ' + xhr.responseText);
                        }
                    });
                });

                $('#addMedicalreport').on('hidden.bs.modal', function() {
                    $('#medical_id').val('');
                    $('#medical-report-submit')[0].reset();

                });
            });
        </script>
    @endpush
