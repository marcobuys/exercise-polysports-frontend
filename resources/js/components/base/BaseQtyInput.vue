<script setup>
// Shared design-system control: a numeric quantity input used across the app.
// Supports v-model so callers can bind a quantity directly.
defineProps({
    value: { type: [Number, String], default: 0 },
    min: { type: Number, default: 0 },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

function onInput(event) {
    emit('update:modelValue', event.target.value);
}
</script>

<template>
    <input
        :value="value"
        :min="min"
        :disabled="disabled"
        :aria-label="label"
        type="number"
        class="h-9 w-16 rounded border border-slate-300 text-center text-sm disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-300"
        @input="onInput"
    />
</template>
