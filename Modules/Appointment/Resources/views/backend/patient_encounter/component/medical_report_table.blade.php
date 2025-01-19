<div class="table-responsive rounded mb-0">
    <table class="table table-lg m-0" id="medical_report_table">
        <thead>

            <tr class="text-white">
                <th scope="col">{{ __('appointment.name') }}</th>
                <th scope="col">{{ __('appointment.date') }}</th>

                <th scope="col">{{ __('appointment.action') }}</th>

            </tr>
        </thead>
        <tbody>

            @foreach ($data['medicalReport'] as $index => $medicalreport)
                <tr>
                    <td>
                        <span>
                            {{ $medicalreport['name'] }}
                        </span>

                    </td>
                    <td>
                        {{ $medicalreport['date'] }}
                    </td>

                        <td class="action">

                            @if ($data['status'] == 1)
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn text-primary p-0 fs-5 me-2" data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    onclick="editMedicalreport({{ $medicalreport['id'] }})"
                                    aria-controls="form-offcanvas">
                                    <i class="ph ph-pencil-simple-line"></i>
                                </button>

                                @endif


                                <a href="{{ $medicalreport['file_url'] }}" class="btn text-primary p-0 fs-5"
                                    target="_blank">
                                    <i class="ph ph-eye align-middle"></i>
                                </a>

                                @if ($data['status'] == 1)

                                <button type="button" class="btn text-danger p-0 fs-5"
                                onclick="deletemedicalreport({{ $medicalreport['id'] }}, 'Are you sure you want to delete it?')"
                                data-bs-toggle="tooltip">
                                <i class="ph ph-trash"></i>
                              </button>
                            @endif



                            </div>
                        </td>

                </tr>
            @endforeach


            @if (count($data['medicalReport']) <= 0)
                <tr>
                    <td colspan="5">
                        <div class="my-1 text-danger text-center">{{ __('appointment.no_medical_report_found') }}
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

</div>


@push('after-scripts')
    <script>
        var baseUrl = '{{ url('/') }}';

        function deletemedicalreport(id, message) {

            confirmDeleteSwal({
                message
            }).then((result) => {

                if (!result.isConfirmed) return;

                $.ajax({
                    url: baseUrl + '/app/encounter/delete-medical-report/' + id,
                    type: 'GET',
                    success: (response) => {
                        if (response.html) {

                            $('#medical_report_table').html(response.html);

                            Swal.fire({
                                title: 'Deleted',
                                text: response.message,
                                icon: 'success',
                                showClass: {
                                    popup: 'animate__animated animate__zoomIn'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__zoomOut'
                                }
                            });
                        } else {

                            Swal.fire({
                                title: 'Error',
                                text: response.message || 'Failed to delete the prescription.',
                                icon: 'error',
                                showClass: {
                                    popup: 'animate__animated animate__shakeX'
                                },
                                hideClass: {
                                    popup: 'animate__animated animate__fadeOut'
                                }
                            });
                        }
                    }
                });
            });
        }


        function editMedicalreport(id) {
            $.ajax({
                url: baseUrl + '/app/encounter/edit-medical-report/' + id,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {

                    if (response.status) {

                        $('#medical_name').val(response.data.name);
                        $('#date').val(response.data.date);
                        $('#medical_id').val(response.data.id);
                        $('#medical_user_id').val(response.data.user_id);
                        $('#medical_encounter_id').val(response.data.encounter_id);

                        $('#addMedicalreport').modal('show');
                    } else {
                        alert(response.message || 'Failed to load prescription details.');
                    }
                },
                error: (xhr, status, error) => {
                    console.error(error);
                    alert('An unexpected error occurred.');
                }
            });
        }



        function sendMedicalReport(button, id) {

            $(button).prop('disabled', true).html(`
                                 <div class="d-inline-flex align-items-center gap-1">
                                 <i class="ph ph-spinner ph-spin"></i>
                                 Sending...
                                 </div>
                                 `);

            $.ajax({
                url: baseUrl + '/app/encounter/send-medical-report?id=' + id,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: (response) => {
                    if (response.status) {
                        window.successSnackbar(response.message);
                    } else {
                        window.errorSnackbar('Something went wrong! Please check.');
                    }
                    // Re-enable the button and reset its text
                    $(button).prop('disabled', false).html(`
                              <div class="d-inline-flex align-items-center gap-1">
                                  <i class="ph ph-paper-plane-tilt"></i>
                                  {{ __('appointment.email') }}
                              </div>
                          `);
                  },
                error: (xhr, status, error) => {
                    console.error(error);
                    window.errorSnackbar('Something went wrong! Please check.');
                    // Re-enable the button and reset its text
                    $(button).prop('disabled', false).html(`
                         <div class="d-inline-flex align-items-center gap-1">
                             <i class="ph ph-paper-plane-tilt"></i>
                             {{ __('appointment.email') }}
                         </div>
                   `);
                  }
            });
        }
    </script>
@endpush
