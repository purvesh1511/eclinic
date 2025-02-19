<template>
  <form @submit="formSubmit">
    <div class="offcanvas offcanvas-end offcanvas-w-40" tabindex="-1" id="form-offcanvas" aria-labelledby="form-offcanvasLabel">
      <FormHeader :currentId="currentId" :editTitle="editTitle" :createTitle="createTitle"></FormHeader>
      <div class="offcanvas-body">
        <div class="row">
          <div class="col-12">
            <div class="form-group" v-if="!selectedPatient">
              <label class="form-label">{{ $t('clinic.lbl_select_patient') }} <span class="text-danger">*</span></label>
              <Multiselect id="patient_id" v-model="patient_id" :value="patient_id" :placeholder="$t('clinic.lbl_select_patient')" v-bind="singleSelectOption" :options="patientlist.options" class="form-group"></Multiselect>
              <span v-if="errorMessages['patient_id']">
                <ul class="text-danger">
                  <li v-for="err in errorMessages['patient_id']" :key="err">{{ err }}</li>
                </ul>
              </span>
              <span class="text-danger">{{ errors['patient_id'] }}</span>
            </div>

            <div class="user-block card rounded" v-else>
              <div class="card-body">
                <div class="d-flex align-items-start gap-4 mb-4">
                  <img :src="selectedPatient.avatar" alt="avatar" class="img-fluid avatar avatar-60 rounded-pill" />
                  <div class="flex-grow-1">
                    <div class="gap-2">
                      <h5>{{ selectedPatient.name }}</h5>
                      <p class="m-0">{{ $t('appointment.lbl_since') }} {{ moment(selectedPatient.created_at).format('MMMM YYYY') }}</p>
                    </div>
                  </div>
                  <button type="button" @click="removePatient()" class="text-danger bg-transparent border-0"><i class="ph ph-trash"></i></button>
                </div>
                <div class="row" v-if="selectedPatient.mobile">
                  <label class="col-3 col-xl-2 fw-500">
                    <strong class="fst-normal text-dark">{{ $t('booking.lbl_phone') }}</strong>
                  </label>
                  <span class="col-7 col-xl-8">{{ selectedPatient.mobile }}</span>
                </div>
                <div class="row" v-if="selectedPatient.email">
                  <label class="col-3 col-xl-2 fw-500">
                    <strong
                      ><span class="fst-normal text-dark">{{ $t('booking.lbl_e-mail') }}</span></strong
                    >
                  </label>
                  <span class="col-7 col-xl-8">{{ selectedPatient.email }}</span>
                </div>
              </div>
            </div>

            <div class="row" v-if="selectedPatient">
              <div class="col-md-6 form-group">
                <label class="form-label col-md-6">{{ $t('clinic.lbl_select_clinic') }} <span class="text-danger">*</span></label>
                <Multiselect id="clinic_id" v-model="clinic_id" :value="clinic_id" :placeholder="$t('clinic.lbl_select_clinic')" v-bind="singleSelectOption" :options="cliniclist.options" @select="getDoctorList" class="form-group"></Multiselect>
                <span v-if="errorMessages['clinic_id']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['clinic_id']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors['clinic_id'] }}</span>
              </div>

              <div v-if="!isDoctor" class="col-md-6 form-group">
                <label class="form-label">{{ $t('clinic.lbl_select_doctor') }} <span class="text-danger">*</span></label>
                <Multiselect id="doctor_id" v-model="doctor_id" :value="doctor_id" :placeholder="$t('clinic.lbl_select_doctor')" v-bind="singleSelectOption" :options="doctorlist.options" @select="getServiceList" class="form-group"></Multiselect>
                <span v-if="errorMessages['doctor_id']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['doctor_id']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors['doctor_id'] }}</span>
              </div>

              <div class="col-md-6 form-group">
                <label class="form-label">{{ $t('clinic.lbl_select_service') }} <span class="text-danger">*</span></label>
                <Multiselect id="service_id" v-model="service_id" :value="service_id" @select="checkTotalAmount" :placeholder="$t('clinic.lbl_select_service')" v-bind="singleSelectOption" :options="servicelist.options" class="form-group"></Multiselect>
                <span v-if="errorMessages['service_id']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['service_id']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors['service_id'] }}</span>
              </div>

              <div class="col-md-6 form-group appointment_date">
                <label class="form-label" for="appointment_date">{{ $t('clinic.lbl_appointment_date') }} <span class="text-danger">*</span></label>

                <flat-pickr :placeholder="$t('clinic.lbl_appointment_date')" id="appointment_date" class="form-control" v-model="appointment_date" :value="appointment_date" :config="config" @change="AppointmentDateChange"></flat-pickr>
                <span v-if="errorMessages['appointment_date']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['appointment_date']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.appointment_date }}</span>
              </div>

              <div class="col-md-12 form-group">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="form-label" for="availble_slot">{{ $t('clinic.lbl_availble_slots') }} <span class="text-danger">*</span></label>
                </div>

                <div>
                  <div v-if="available_slots.length > 0" class="d-flex flex-wrap align-items-center gap-3">
                    <div v-for="(slot, index) in available_slots" :key="index" class="avb-slot clickable-text">
                      <label class="clickable-text" :class="{ selected_slot: slot === selectedSlot }" @click="selectSlot(slot)">{{ slot }}</label>
                    </div>
                  </div>
                  <div v-if="available_slots.length == 0">
                    <h4 class="text-danger text-center form-control">{{ $t('clinic.lbl_Slot_not_Found') }}</h4>
                  </div>
                </div>
                <span class="text-danger" id="avaible_slot_error">{{ errors.availble_slot }}</span>
              </div>

              <div class="form-group col-md-12">
                <div class="d-flex justify-content-between align-items-center">
                  <label class="form-label" for="file_url">{{ $t('clinic.lbl_medical_report') }} </label>
                </div>
                <input type="file" class="form-control" id="file_url" name="file_url" ref="refInput" accept=".jpeg, .jpg, .png, .gif, .pdf" multiple @change="fileUpload" />
                <span v-if="errorMessages['file_url']">
                  <ul class="text-danger">
                    <li v-for="err in errorMessages['file_url']" :key="err">{{ err }}</li>
                  </ul>
                </span>
                <span class="text-danger">{{ errors.file_url }}</span>
              </div>

              <div v-for="field in customefield" :key="field.id">
                <FormElement v-model="custom_fields_data" :name="field.name" :label="field.label" :type="field.type" :required="field.required" :options="field.value" :field_id="field.id"></FormElement>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="offcanvas-footer">
        <div class="p-3 bg-primary-subtle border border-primary rounded">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-normal">
              {{ $t('report.lbl_service_amount') }}
              <button  data-bs-toggle="modal" data-bs-target="#exampleModalCenter" class="btn btn-link ms-2 p-0"></button>
              :
            </span>
            <span class="fw-bold">{{ formatCurrencyVue(serviceAmount) }}</span>
          </div>
          <!-- <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-normal">{{ $t('appointment.discount_amount') }}:</span>
            <span class="fw-bold text-danger">-{{ formatCurrencyVue(discountAmount) }}</span>
          </div> -->
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <span class="fw-normal">{{ $t('appointment.total_tax') }} <i class="ph ph-info btn btn-link ms-2 p-0" data-bs-toggle="modal" data-bs-target="#exampleModalCenter"></i> :</span>

              <!-- Replace with the desired icon class -->
            </div>
            <div>
              <span class="fw-bold text-danger">{{ formatCurrencyVue(totalTaxAmount) }}</span>
            </div>
          </div>
          <hr class="my-2" />
          <div class="d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ $t('appointment.total') }}:</span>
            <span class="fw-bold text-success">{{ formatCurrencyVue(totalAmount) }}</span>
          </div>
        </div>
        <div class="d-grid d-sm-flex justify-content-sm-end gap-3 p-3">
          <button class="btn btn-white d-block" type="button" data-bs-dismiss="offcanvas">
            {{ $t('messages.close') }}
          </button>
          <button :disabled="isDisabled" class="btn btn-secondary" name="submit">
            <template v-if="IS_SUBMITED">
              <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
              {{ $t('appointment.loading') }}
            </template>
            <template v-else> {{ $t('messages.save') }}</template>
          </button>
        </div>
      </div>
    </div>
  </form>

  <div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Applied Taxes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div>
            <div v-for="tax in apliedTax" :key="tax.name" class="d-flex justify-content-between align-items-center">
              {{ tax.name }}:
              <span v-if="tax.type === 'percent'">{{ tax.amount.toFixed(2) }} ({{ tax.value }}%)</span>
              <span v-else>{{ formatCurrencyVue(tax.amount) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import moment from 'moment'
import * as yup from 'yup'
import { useField, useForm } from 'vee-validate'
import { EDIT_URL, STORE_URL, UPDATE_URL, CLINIC_LIST, DOCTOR_LIST, SERVICE_LIST, PATIENT_LIST, SERVICE_PRICE, TAX_DATA, GET_AVALABLE_SLOT, SAVE_PAYMENT } from '../constant/clinic-appointment'
import { useModuleId, useRequest, useOnOffcanvasHide, useOnOffcanvasShow } from '@/helpers/hooks/useCrudOpration'
import { readFile } from '@/helpers/utilities'
import { useSelect } from '@/helpers/hooks/useSelect'
import FormHeader from '@/vue/components/form-elements/FormHeader.vue'
import FlatPickr from 'vue-flatpickr-component'

// props
const props = defineProps({
  createTitle: { type: String, default: '' },
  editTitle: { type: String, default: '' },
  customefield: { type: Array, default: () => [] },
  defaultImage: { type: String, default: 'https://dummyimage.com/600x300/cfcfcf/000000.png' },
  role: { type: String, default: '' },
  userId: { type: Number, default: '' }
})

// flatepicker

const config = ref({
  dateFormat: 'Y-m-d',
  static: true,
  minDate: 'today'
})
const role = () => {
  return window.auth_role[0]
}

const isReceptionist = computed(() => role() === 'receptionist')
const isDoctor = computed(() => role() == 'doctor')
const ImageViewer = ref(null)
const profileInputRef = ref(null)
const fileUpload = (e) => {
  let files = e.target.files
  file_url.value = []
  for (let i = 0; i < files.length; i++) {
    let file = files[i]
    readFile(file, (fileB64) => {
      ImageViewer.value = fileB64
    })
    file_url.value.push(file)
  }
  profileInputRef.value.value = ''
}

const formatCurrencyVue = (value) => {
  if (window.currencyFormat !== undefined) {
    return window.currencyFormat(value)
  }
  return value
}

const { getRequest, storeRequest, updateRequest, listingRequest } = useRequest()

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})

// Edit Form Or Create Form
const currentId = useModuleId(() => {
  if (currentId.value > 0) {
    getRequest({ url: EDIT_URL, id: currentId.value }).then((res) => {
      if (res.status) {
        setFormData(res.data)
      }
    })
  } else {
    setFormData(defaultData())
  }
})

// Validations
const validationSchema = yup.object({
  clinic_id: yup.string().required('Clinic name is required'),
  service_id: yup.string().required('Service is required'),
  doctor_id: yup.string().required('Doctor is required'),
  appointment_date: yup.string().required('Appointment Date is required'),
  patient_id: yup.string().required('Patient is required')
})

const { handleSubmit, errors, resetForm } = useForm({
  validationSchema
})

const { value: clinic_id } = useField('clinic_id')
const { value: service_id } = useField('service_id')
const { value: doctor_id } = useField('doctor_id')
const { value: appointment_date } = useField('appointment_date')
const { value: patient_id } = useField('patient_id')
const { value: file_url } = useField('file_url')

const errorMessages = ref({})

onMounted(() => {
  setFormData(defaultData())
  getClinic()
  getPatient()
  gettaxData()
})

// Default FORM DATA
const defaultData = () => {
  totalAmount.value = 0
  totalTaxAmount.value = 0
  available_slots.value = []
  errorMessages.value = {}
  ImageViewer.value = props.defaultImage
  if (isDoctor) {
    doctor_id.value = props.userId
    getServiceList(doctor_id.value)
  }
  return {
    clinic_id: '',
    service_id: '',
    doctor_id: '',
    appointment_date: '',
    patient_id: '',
    file_url: []
  }
}
const patientlist = ref({ options: [], list: [] })

const getPatient = () => {
  useSelect({ url: PATIENT_LIST }, { value: 'id', label: 'name' }).then((data) => (patientlist.value = data))
}

const selectedPatient = computed(() => patientlist.value.list.find((patient) => patient.id == patient_id.value) ?? null)

const removePatient = () => {
  patient_id.value = null
  setFormData(defaultData())
  selectedSlot.value = ''
}
const IS_SUBMITED = ref(false)

const tax_data = ref([])

const tax_data_value = ref([])

const gettaxData = () => {
  const module_type = 'services'

  listingRequest({ url: TAX_DATA, data: module_type }).then((res) => {
    tax_data.value = res
    tax_data_value.value = res
  })
}
const discountAmount = ref(0)
const totalAmount = ref(0)
const totalTaxAmount = ref(0)
const apliedTax = ref([])

const taxCalculation = (amount) => {

  apliedTax.value = []
  let tax_amount = 0
  serviceAmount.value = 0
  serviceAmount.value = amount
  totalTaxAmount.value = 0

  if (tax_data.value) {
    tax_data.value.forEach((item) => {
      let taxValue = item.value
      console.log('taxValue', taxValue, ' item.value', item.value)
      if (item.type === 'fixed') {

          tax_amount += taxValue
          apliedTax.value.push({ name: item.title, amount: taxValue, type: item.type })

      } else if (item.type === 'percent') {

          let temp_tax_amount = amount * (taxValue / 100)
          tax_amount += temp_tax_amount
          apliedTax.value.push({ name: item.title, amount: temp_tax_amount, value: taxValue, type: item.type })

      }
    })
  }

  // Subtract inclusive taxes from the base amount

  serviceAmount.value = amount
  console.log('tax_amount', tax_amount)
  totalTaxAmount.value = tax_amount
  const total_amount = serviceAmount.value + totalTaxAmount.value

  return total_amount
}

const isDisabled = computed(() => {
  return IS_SUBMITED.value || totalAmount.value < 1 || selectedSlot.value === ''
})

const cliniclist = ref({ options: [], list: [] })

const getClinic = () => {
  useSelect({ url: CLINIC_LIST }, { value: 'id', label: 'clinic_name' }).then((data) => {
    cliniclist.value = data
    if (isReceptionist.value && cliniclist.value.list.length > 0) {
      clinic_id.value = [cliniclist.value.list[0].id]
      getDoctorList(clinic_id.value)
    }
  })
}

const doctorlist = ref({ options: [], list: [] })

const getDoctorList = (value) => {
  ;(doctor_id.value = ''), (service_id.value = ''), useSelect({ url: DOCTOR_LIST, data: value }, { value: 'doctor_id', label: 'doctor_name' }).then((data) => (doctorlist.value = data))
}

const servicelist = ref({ options: [], list: [] })

const getServiceList = (value) => {
  ;(service_id.value = ''), useSelect({ url: SERVICE_LIST, data: { doctor_id: value, clinic_id: clinic_id.value } }, { value: 'id', label: 'name' }).then((data) => (servicelist.value = data))
  AppointmentDateChange()
}

const serviceAmount = ref(0)

const checkTotalAmount = (value) => {
  if (isDoctor.value) {
    doctor_id.value = props.userId
  }
  listingRequest({ url: SERVICE_PRICE, data: { ServiceId: service_id.value, DoctorId: doctor_id.value } }).then((res) => {
    if (res) {
      if (res > 0) {
        let amount = res
        serviceAmount.value = res
        console.log(serviceAmount.value)
        let total_amount = taxCalculation(amount)

        totalAmount.value = total_amount
      }
    }
  })
  AppointmentDateChange()
}

const available_slots = ref([])

const AppointmentDateChange = () => {
  if (isDoctor.value) {
    doctor_id.value = props.userId
  }
  if (appointment_date.value != '' && doctor_id.value != '' && clinic_id.value != '' && service_id.value != '') {
    listingRequest({ url: GET_AVALABLE_SLOT, data: { Appointment_date: appointment_date.value, DoctorId: doctor_id.value, ClinicId: clinic_id.value, serviceId: service_id.value } }).then((res) => {
      if (res) {
        available_slots.value = res.availableSlot
      }
    })
  }
}

const selectedSlot = ref('')

const selectSlot = (value) => {
  selectedSlot.value = value
}

//  Reset Form
const setFormData = (data) => {
  ImageViewer.value = data.file_url || props.defaultImage
  selectedSlot.value = ''

  resetForm({
    values: {
      clinic_id: data.clinic_id ?? '',
      service_id: data.service_id ?? '',
      doctor_id: data.doctor_id ?? '',
      appointment_date: data.appointment_date ?? '',
      patient_id: data.patient_id ?? '',
      file_url: data.file_url ?? ''
    }
  })
}

const reset_datatable_close_offcanvas = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
    renderedDataTable.ajax.reload(null, false)
    bootstrap.Offcanvas.getInstance('#form-offcanvas').hide()
    setFormData(defaultData())
  } else {
    window.errorSnackbar(res.message)
    errorMessages.value = res.all_message
  }
}

const formSubmit = handleSubmit((values) => {
  IS_SUBMITED.value = true
  console.log(values)
  if (props.customefield > 0) {
    values.custom_fields_data = JSON.stringify(values.custom_fields_data)
  }

  // Check if slot is selected
  if (selectedSlot.value === '') {
    document.querySelector(`#avaible_slot_error`).textContent = 'Appointment Slot Is Required'
    IS_SUBMITED.value = false // Stop the loader when no slot is selected
    return // Prevent form submission
  } else {
    document.querySelector(`#avaible_slot_error`).textContent = '' // Clear the error message
  }

  values.status = 'confirmed'
  values.user_id = patient_id.value
  values.appointment_time = selectedSlot.value
  values.tax = JSON.stringify(tax_data_value.value)

  if (values.file_url && values.file_url.length > 0) {
    values.file_url.forEach((fileUrl, index) => {
      values[`file_url[${index}]`] = fileUrl
    })
  }
  console.log(values)
  if (currentId.value > 0) {
    updateRequest({ url: UPDATE_URL, id: currentId.value, body: values, type: 'file' }).then((res) => reset_datatable_close_offcanvas(res))
  } else {
    storeRequest({ url: STORE_URL, body: values, type: 'file' }).then((res) => {
      if (res.status == true) {
        storeRequest({ url: SAVE_PAYMENT, body: res.data }).then((data) => reset_datatable_close_offcanvas(data))
      } else {
        reset_datatable_close_offcanvas(res)
      }
    })
  }
})
useOnOffcanvasHide('form-offcanvas', () => {
  IS_SUBMITED.value = false
  setFormData(defaultData())
})
useOnOffcanvasShow('form-offcanvas', () => {
  IS_SUBMITED.value = false
})
</script>
<style>
.multiselect-clear {
  display: none !important;
}
.clickable-text {
  display: inline-block;
  cursor: pointer;
}
.background_colour {
  background-color: #50494917 !important;
  cursor: not-allowed;
}
.copy-icon {
  color: gray;
}
</style>
