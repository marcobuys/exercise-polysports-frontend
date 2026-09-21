<script>
import { createOrder } from '../api.js';
import { formatEuros } from '../money.js';

export default {
    name: 'Checkout',
    props: {
        customer: { type: Object, required: true },
    },
    data() {
        return {
            submitting: false,
            error: null,
            confirmation: null,
        };
    },
    computed: {
        lines() {
            return this.$store.getters['cart/lines'];
        },
        subtotalCents() {
            return this.$store.getters['cart/subtotalCents'];
        },
    },
    methods: {
        formatEuros,
        async submit() {
            this.submitting = true;
            this.error = null;

            try {
                const { data } = await createOrder({
                    customer_id: this.customer.id,
                    lines: this.lines.map((l) => ({
                        product_variant_id: l.product_variant_id,
                        quantity: l.quantity,
                    })),
                });
                this.confirmation = data;
                this.$store.dispatch('cart/clear');
                this.$emit('placed');
            } catch (e) {
                this.error = e.message;
            } finally {
                this.submitting = false;
            }
        },
    },
};
</script>

<template>
    <section>
        <button class="mb-4 text-sm text-slate-500 hover:text-slate-800" @click="$emit('back')">← Back to catalogue</button>

        <div v-if="confirmation" class="rounded-lg border border-green-200 bg-green-50 p-6 text-center">
            <p class="text-2xl">✅</p>
            <h1 class="mt-2 text-lg font-semibold text-slate-900">Order #{{ confirmation.id }} placed</h1>
            <p class="text-sm text-slate-600">
                {{ confirmation.item_count }} line(s) · {{ formatEuros(confirmation.subtotal_cents) }}
            </p>
        </div>

        <div v-else class="rounded-lg border border-slate-200 bg-white p-6">
            <h1 class="text-lg font-semibold text-slate-900">Review order</h1>
            <p class="mb-4 text-sm text-slate-500">
                For {{ customer.company_name }} ({{ customer.tier }} tier)
            </p>

            <p v-if="error" class="mb-3 rounded bg-red-50 px-3 py-2 text-sm text-red-700">{{ error }}</p>

            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-slate-500">
                        <th class="py-2">Product</th>
                        <th class="py-2">Size</th>
                        <th class="py-2 text-right">Unit</th>
                        <th class="py-2 text-right">Qty</th>
                        <th class="py-2 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="line in lines" :key="line.product_variant_id" class="border-b border-slate-100">
                        <td class="py-2 text-slate-800">{{ line.product_name }}</td>
                        <td class="py-2 text-slate-600">EU {{ line.eu_size }}</td>
                        <td class="py-2 text-right text-slate-600">{{ formatEuros(line.unit_price_cents) }}</td>
                        <td class="py-2 text-right text-slate-600">{{ line.quantity }}</td>
                        <td class="py-2 text-right text-slate-800">{{ formatEuros(line.unit_price_cents * line.quantity) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="font-semibold text-slate-900">
                        <td class="py-3" colspan="4">Grand total</td>
                        <td class="py-3 text-right">{{ formatEuros(subtotalCents) }}</td>
                    </tr>
                </tfoot>
            </table>

            <button
                class="mt-4 w-full rounded bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300"
                :disabled="submitting || lines.length === 0"
                @click="submit"
            >
                {{ submitting ? 'Submitting…' : 'Submit order' }}
            </button>
        </div>
    </section>
</template>
