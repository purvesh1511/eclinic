<template>
  <form @submit="formSubmit">
    <div class="col-md-12 d-flex justify-content-between">
      <CardTitle :title="role() == 'doctor' ? $t('setting_sidebar.holiday') : $t('setting_sidebar.lbl_doctorholiday')" icon="fa fa-calendar"></CardTitle>

    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label class="form-label">{{ $t('setting_sidebar.lbl_doctorholiday') }} <span class="text-danger">*</span></label>
          <Multiselect id="doctor_id" v-model="doctor_id" :value="doctor_id" :placeholder="$t('setting_sidebar.lbl_doctorholiday')" v-bind="singleSelectOption" :options="doctorlist.options" @select="getSelectDoctor" class="form-group" :disabled="role() == 'doctor'"> </Multiselect>
          <span class="text-danger">{{ errors.doctor_id }}</span>
        </div>
      </div>
    </div>
    <div v-for="(picker, index) in pickers" :key="index" class="row align-items-end">
      <div class="col-md-4">
        <div class="form-group mb-0">
          <label class="form-label">{{$t('setting_sidebar.select_date')}} <span class="text-danger">*</span></label>
          <flat-pickr :placeholder="$t('setting_sidebar.select_date')" v-model="picker.date" @change="SelectDate" :config="dateconfig" class="form-control"></flat-pickr>
          <span class="text-danger date_err" :id="`date_err_${index}`"></span>
        </div>
      </div>
      <div class="col-md-3">
          <label class="form-label">{{$t('setting_sidebar.Select_StartTime')}} <span class="text-danger">*</span></label>
          <flat-pickr :placeholder="$t('setting_sidebar.Select_StartTime')" v-model="picker.start_time" :config="timeconfig" class="form-control"></flat-pickr>
          <span class="text-danger start_time_err" :id="`start_time_err_${index}`"></span>
      </div>
      <div class="col-md-3">
          <label class="form-label">{{$t('setting_sidebar.Select_EndTime')}} <span class="text-danger">*</span></label>
          <flat-pickr :placeholder="$t('setting_sidebar.Select_EndTime')" v-model="picker.end_time" :config="timeconfig" class="form-control"></flat-pickr>
          <span class="text-danger end_time_err" :id="`end_time_err_${index}`"></span>
      </div>
      <div class="col-md-2 text-end">
        <button @click="destroyData(picker.id, 'Are you sure you want to delete it?', $event)" class="btn btn-secondary"
          v-show="pickers.length > 0"><i class="ph ph-trash align-middle"></i></button>
      </div>
    </div>
    <div class="mt-3">
      <button type="button" @click="addPicker" class="btn btn-success">{{$t('setting_sidebar.lbl_add_field')}}</button>
    </div>
    <div class="row py-4">
      <SubmitButton :IS_SUBMITED="IS_SUBMITED"></SubmitButton>
    </div>
  </form>
</template>
<script setup>
import { ref, onMounted,computed } from 'vue'
import { useModuleId, useRequest } from '@/helpers/hooks/useCrudOpration'
import CardTitle from '@/Setting/Components/CardTitle.vue'
import { useField, useForm } from 'vee-validate'
import SubmitButton from './Forms/SubmitButton.vue'
import FlatPickr from 'vue-flatpickr-component'
import { confirmSwal,confirmcancleSwal } from '@/helpers/utilities'
import { useSelect } from '@/helpers/hooks/useSelect'
import { STORE_DOCTORHOLIDAY, DELETE_DOCTORHOLIDAY, DOCTOR_LIST, DOCTORHOLIDAY_LIST } from '@/vue/constants/setting'
import * as yup from 'yup'

const singleSelectOption = ref({
  closeOnSelect: true,
  searchable: true
})
const { getRequest, storeRequest, listingRequest,deleteRequest } = useRequest()
const validationSchema = yup.object({
  doctor_id: yup.number().required('Doctor is Required'),
})

const { handleSubmit,errors } = useForm({ validationSchema })

const { value: doctor_id } = useField('doctor_id')

const role = () => {
    return window.auth_role[0];
}
const auth_id = () => {
    return window.id;
}

const dateconfig = {
  minDate: 'today',
  dateFormat: 'Y-m-d',
}
const timeconfig = {
  noCalendar: true,
  enableTime: true,
  dateFormat: 'h:i K'
}

const doctorlist = ref({ options: [], list: [] })
const pickers = ref([{ date: null, start_time: null, end_time: null }])

// const addPicker = () => {
//   pickers.value.push({ date: null, start_time: null, end_time: null })
// }

const isAddQualificationBtnDisabled = computed(() => {
  return  pickers.value.some((input) => !input.date || !input.start_time || !input.end_time)
})

const addPicker = () => {
  const newIndex = pickers.value.length
  if (!isAddQualificationBtnDisabled.value) {
    pickers.value.push({
      index: newIndex,
      date: null,
      start_time: null,
      end_time: null
    })

    document.querySelector(`#date_err_${newIndex - 1}`).textContent = ''
    document.querySelector(`#start_time_err_${newIndex - 1}`).textContent = ''
    document.querySelector(`#end_time_err_${newIndex - 1}`).textContent = ''
  } else {
    const dateErr = 'Date is required'
    const startTimeErr = 'Start time is required'
    const endTimeErr = 'End time is required'
    document.querySelector(`#date_err_${newIndex - 1}`).textContent = dateErr
    document.querySelector(`#start_time_err_${newIndex - 1}`).textContent = startTimeErr
    document.querySelector(`#end_time_err_${newIndex - 1}`).textContent = endTimeErr
  }
}


const destroyData = (id, message, event, index) => {
  event.preventDefault();

  confirmcancleSwal({ title: message }).then(async (result) => {
    if (!result.isConfirmed) return;

    if (id === undefined) {
      pickers.value.splice(index, 1);
      return;
    }

    try {
      const res = await deleteRequest({ url: DELETE_DOCTORHOLIDAY, id: { id } });
      if (res.status) {
        const response = await getRequest({ url: DOCTORHOLIDAY_LIST, id: { doctor_id: doctor_id.value } });
        pickers.value = response.data.length > 0 ? response.data : [{ date: null, start_time: null, end_time: null }];
        
        Swal.fire({
          title: 'Deleted',
          text: res.message,
          icon: 'success',
          showClass: {
            popup: 'animate__animated animate__zoomIn'
          },
          hideClass: {
            popup: 'animate__animated animate__zoomOut'
          }
        });
      }
    } catch (error) {
      console.error("Error deleting data:", error);
    }
  });
}
const getSelectDoctor = async (e) => {
  const doctorId = doctor_id.value;
  const currentDate = new Date();
  const response = await getRequest({ url: DOCTORHOLIDAY_LIST, id: { doctor_id: doctorId } });

  if (response.data !== '') {
    const futureHolidays = response.data.filter((holiday) => {
      const holidayDate = new Date(holiday.date);
      const isTodayOrFuture = holidayDate >= currentDate || holidayDate.getDate() === currentDate.getDate();
      return isTodayOrFuture;
    });

    pickers.value = futureHolidays.length > 0 ? futureHolidays : [{ date: null, start_time: null, end_time: null }];
  } else {
    pickers.value = [{ date: null, start_time: null, end_time: null }];
  }
};

const getDoctor = () => {
  useSelect({ url: DOCTOR_LIST }, { value: 'doctor_id', label: 'doctor_name' }).then((data) => (doctorlist.value = data))
}
onMounted(() => {
  getDoctor()
  if(role() == 'doctor'){
    doctor_id.value = auth_id()
    getSelectDoctor()
  }
})
const display_submit_message = (res) => {
  IS_SUBMITED.value = false
  if (res.status) {
    window.successSnackbar(res.message)
  } else {
    window.errorSnackbar(res.message)
  }
}
const IS_SUBMITED = ref(false)
const formSubmit = handleSubmit(async (values) => {
  const pickersValue = pickers.value;
  const newPickers = Array.isArray(pickersValue) ? pickersValue.filter((picker) => !picker.id) : [];
  const hasMissingDateTime = newPickers.some((picker) => !picker.date || !picker.start_time || !picker.end_time);

  if (hasMissingDateTime) {
    window.errorSnackbar('Please select both date and time for all entries.');
    return;
  }

  const holidays = newPickers.map((picker) => ({
    date: picker.date,
    start_time: picker.start_time,
    end_time: picker.end_time
  }));

  const pickersWithId = pickersValue.filter((picker) => picker.id !== undefined);
  const isDuplicate = pickersWithId.some((existingPicker) => {
    return holidays.some((newPicker) => newPicker.date == existingPicker.date && newPicker.start_time == existingPicker.start_time && newPicker.end_time == existingPicker.end_time);
  });

  if (isDuplicate) {
    window.errorSnackbar('Duplicate entry detected. Please choose a different date and time.');
    return;
  }

  const formData = {
    doctor_id: values.doctor_id,
    holidays: holidays,
    updates: pickersWithId.map((picker) => ({
      id: picker.id,
      date: picker.date,
      start_time: picker.start_time,
      end_time: picker.end_time
    }))
  };

  IS_SUBMITED.value = true;

  try {
    const res = await storeRequest({ url: STORE_DOCTORHOLIDAY, body: formData });
    IS_SUBMITED.value = false;
    res.status = true;

    const response = await getRequest({ url: DOCTORHOLIDAY_LIST, id: { doctor_id: doctor_id.value } });
    pickers.value = response.data.length > 0 ? response.data : [{ date: null, start_time: null, end_time: null }];

    display_submit_message(res);
    
  } catch (error) {
    IS_SUBMITED.value = false;
    console.error("Error submitting data:", error);
  }
});
</script>
