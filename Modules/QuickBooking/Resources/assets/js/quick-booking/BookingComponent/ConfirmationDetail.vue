<template>
    <div class="card-list-data" v-if="booking">
        
        <div class="row">
            <div class="col-sm-6">
                <div class="confirmation-info-section mb-3">
                    <h6 class="text-primary text-uppercase fw-bold mb-2">Clinic Details</h6>
                    <div class="iq-card bg-primary-subtle text-body p-2">
                        <div class="iq-card-body">
                            <table class="iq-table-border mb-0" style="border:0;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6 style="width: 15em;" class="mb-2">{{ booking.clinic_name??'-' }}</h6>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <p style="width: 15em;" class="mb-2">{{booking.clinic_address??'-' }}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                </div>
                <div class="confirmation-info-section mb-3">
                    <h6 class="text-primary text-uppercase fw-bold mb-2">Patient info</h6>
                    <div class="iq-card bg-primary-subtle text-body p-2">
                        <div class="iq-card-body">
                            <table class="iq-table-border mb-0" style="border:0;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Name:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.user_name }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Contact Number:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.user_phone }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Email:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.user_email }}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                </div>
                <div class="confirmation-info-section">
                    <h6 class="text-primary text-uppercase fw-bold mb-2">Doctor Details</h6>
                    <div class="iq-card bg-primary-subtle text-body p-2">
                        <div class="iq-card-body">
                            <table class="iq-table-border mb-0" style="border:0;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Name:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.doctor_name }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Contact Number:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.doctor_phone }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <h6 class="mb-2">Email:</h6>
                                        </td>
                                        <td>
                                            <p class="mb-2">{{ booking.doctor_email }}</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>                        
                    </div>
                </div>
            </div>
        <div class="col-sm-6">
                <h6 class="text-primary text-uppercase fw-bold mb-3">Appointment summary</h6>
                <div class="iq-card iq-card-border p-3">

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h6>Date</h6>
                        <h6 class="text-primary services-total">{{moment(booking.appointment_date).format('MMMM DD, YYYY')  }} {{booking.appointment_time }}</h6>
                    </div>
                  
                   <div class="bg-primary-subtle text-body  mt-4 mb-0 shadow-none p-2">
                      
                         <h6 class="text-primary text-uppercase fw-bold mb-3">Service Detail</h6> 
                            <div class="services_list">
                                <div class="d-flex justify-content-between align-items-center mt-2 mb-3" >
                                    <h6 class="m-0"> {{ booking.service_name }}</h6>

                                    <h6 class="m-0">{{ formatCurrencyVue(booking.service_price) }}</h6>
                                    
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 mb-3" >
                                    <span v-if="booking.discount_type && booking.discount_value !== null">
    <span v-if="booking.discount_type == 'percentage'">
        ({{ booking.discount_value ?? 0 }} %)
    </span>
    <span v-else>
        ({{ formatCurrencyVue(serviceItem?.discount_value ?? 0) }})
    </span>
</span>

                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 mb-3" >
                                    <h6 class="m-0">Sub Total</h6>

                                    <h6 class="m-0">{{ formatCurrencyVue(booking.service_amount) }}</h6>
                                    
                                </div>

                            </div>
                        
                            <h6 class="text-primary text-uppercase fw-bold mb-3">Taxes</h6> 
                            <template v-if="booking.tax">
                                <div class="d-flex align-items-center justify-content-between mb-1" v-for="(tax, index) in booking.tax_data" :key="index">
                                    <template v-if="tax.tax_type == 'percent'">
                                        <h6>{{ tax.title }} ({{  tax.tax_value+'%' }}) : </h6>
                                        <h6>+  {{formatCurrencyVue( tax.amount) }}</h6>
                                    </template>
                                    <template v-else>
                                        <p>{{ tax.title }} ( {{ currency_symbol }} {{  tax.tax_value}} ) : </p>
                                        <h6>+ {{ formatCurrencyVue(tax.amount) }}</h6>
                                    </template>
                                </div>
                            </template>
                                      
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <h5>Total Price</h5>
                            <h5 class="text-primary services-total"> {{ formatCurrencyVue(booking.total_amount)}}/-</h5>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </div>
    <div class="card-footer non-printable">
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            <button type="button" class="btn btn-primary d-flex gap-3" @click="reset">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Book More Appointments</span>
            </button>
            <button type="button" class="btn btn-secondary d-flex gap-3" @click="print">
                <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
                <span>Print to PDF</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import {computed, watch, ref} from 'vue'
 import {useQuickBooking} from '../../store/quick-booking'
 import moment from 'moment'
const props = defineProps({
  wizardNext: {
    default: '',
    type: [String, Number]
  },
  wizardPrev: {
    default: '',
    type: [String, Number]
  },
})
const emit = defineEmits(['tab-change','onReset'])
const reset = () => {
  emit('onReset')
}
const print = () => {
    window.print()
}

const store = useQuickBooking()
const booking = computed(() => store.bookingResponse)

console.log(booking)

const formatCurrencyVue = (value) => {
  if(window.currencyFormat !== undefined && value) {
    return window.currencyFormat(value)
  }
  return value
}
const calculatePercentAmount = (percent) => {
  const percentAmount = (SUB_TOTAL_SERVICE_AMOUNT.value * percent) / 100;

  return percentAmount
}
const SUB_TOTAL_SERVICE_AMOUNT = ref(0)
const currency_symbol = computed(() => {
  return window.defaultCurrencySymbol || '';
});
const taxAmount = computed(() => {
   
   let totalTaxAmount = 0;
 
   if(booking.value.tax){
       for (const tax of booking.value.tax) {
         if (tax.type === 'percent') { 
           totalTaxAmount += (SUB_TOTAL_SERVICE_AMOUNT.value * tax.percent) / 100;
         } else {  
           totalTaxAmount += tax.tax_amount;
         }
       }
   }
 
   return totalTaxAmount.toFixed(2);
 });
const finalAmount = computed(() => {
    console.log(taxAmount.value);
    return Number(SUB_TOTAL_SERVICE_AMOUNT.value) + Number(taxAmount.value);
});
watch(() => booking, () => {
    // if(store.bookingResponse) {
    //     SUB_TOTAL_SERVICE_AMOUNT.value = store.bookingResponse.services.reduce((total, service) => total + service.service_price, 0)
    // } else {
    //     SUB_TOTAL_SERVICE_AMOUNT.value = 0
    // }
}, {deep: true})
</script>
<style scoped>
  .card-list-data {
    margin: 1px;
  }

  .iq-card {
    border: 1px solid #ccc;
    border-radius: 4px;
  }

  .iq-card-body {
    padding: 10px;
  }

  .iq-table-border {
    width: 100%;
  }

  .services-total {
    font-weight: bold;
  }

  @media print {
    body {
      margin: 1px; /* Adjust as needed */
      font-size: 14pt; /* Adjust font size as needed */
    }

    .card-list-data {
      font-size: 12pt; /* Adjust the size of elements as needed */
    }

    /* You can add more styles to adjust other elements */
  }
</style>