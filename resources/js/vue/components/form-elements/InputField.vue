<template>
  <!-- <div class="form-group">
    <label class="form-label" :for="label"> {{ label }} <i class="fa-solid fa-circle-info" v-if="infoTip" data-bs-toggle="tooltip" :title="infoTip"></i> <span v-if="isRequired" class="text-danger">*</span> </label>
    <textarea v-if="type == 'textarea'" class="form-control" :rows="textareaRows" @input="updateModelValue" :value="modelValue" :readonly="isReadOnly">{{ modelValue }}</textarea>
    <input v-else-if="type == 'number'" :type="type" class="form-control" :id="label" :value="modelValue" @input="updateModelValue" :placeholder="placeholder" :step="step" :min="min" :max="max" :readonly="isReadOnly"/>
    <input v-else :type="type" class="form-control" :id="label" :value="modelValue" @input="updateModelValue" :placeholder="placeholder" :readonly="isReadOnly"/>
    <slot></slot>
    <span class="text-danger">{{ errorMessage }}</span>
    <ul class="m-0">
      <li class="text-danger" v-for="msg in errorMessages" :key="msg">{{ msg }}</li>
    </ul>
  </div> -->

  <div class="mb-3">
    <div class="input-group">
      <textarea v-if="type == 'textarea'" class="form-control" :rows="textareaRows" @input="updateModelValue" :value="modelValue" :readonly="isReadOnly">{{ modelValue }}</textarea>    
      <input v-else-if="type == 'number'" :type="type" class="form-control" :id="label" :value="modelValue" @input="updateModelValue" :placeholder="placeholder" :step="step" :min="min" :max="max" :readonly="isReadOnly"/>
      <input v-else :type="type" class="form-control" :id="label" :value="modelValue" @input="updateModelValue" :placeholder="placeholder" :readonly="isReadOnly" data-last-char="*"/>
      <!-- <span class="input-group-text" v-html="icon"></span> -->
    </div>
      <span class="text-danger">{{ errorMessage }}</span>
      <ul class="m-0">
        <li class="text-danger" v-for="msg in errorMessages" :key="msg">{{ msg }}</li>
      </ul>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  isLabel: { type: Boolean, default: true },
  isRequired: { type: Boolean, default: false },
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  modelValue: '',
  placeholder: { type: String, default: '' },
  errorMessage: { type: String, default: '' },
  errorMessages: { type: Array, default: () => [] },
  infoTip: { type: String, default: null },
  editorHeight: {type: Number, default: 300},
  textareaRows: { type: Number, default: 6 },
  step: {type: Number, default: null},
  min: {type: Number, default: null},
  max: {type: Number, default: null},
  isReadOnly: { type: Boolean, default: false },
  icon: { type: String, default: '' },

})

// const placeholder = computed(() => {
//   return props.isRequired ? props.label + '*' : props.label;
// });

const emit = defineEmits(['update:modelValue'])

const updateModelValue = (e) => {
  emit('update:modelValue', e.target.value)
}
</script>
