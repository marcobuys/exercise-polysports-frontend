<script>
import BaseQtyInput from './base/BaseQtyInput.vue';

export default {
    name: 'OrderGrid',
    components: { BaseQtyInput },
    props: {
        product: { type: Object, required: true },
    },
    data() {
        // Per-size draft quantities, keyed by variant id. Pre-seed every key so
        // Vue 2 tracks them reactively for v-model.
        const quantities = {};
        for (const slot of this.product.availability || []) {
            quantities[slot.variant_id] = 0;
        }
        return { quantities, bulkQty: 0 };
    },
    computed: {
        slots() {
            return this.product.availability || [];
        },
    },
    methods: {
        applyToAll() {
            const qty = Number(this.bulkQty) || 0;
            for (const slot of this.slots) {
                if (slot.stock > 0) {
                    this.quantities[slot.variant_id] = qty;
                }
            }
        },
        addToCart() {
            const lines = [];
            for (const slot of this.slots) {
                const qty = Number(this.quantities[slot.variant_id]) || 0;
                if (qty > 0) {
                    lines.push({
                        product_variant_id: slot.variant_id,
                        product_id: this.product.id,
                        product_name: this.product.name,
                        eu_size: slot.eu_size,
                        unit_price_cents: this.product.price_cents,
                        quantity: qty,
                    });
                }
            }

            if (lines.length) {
                this.$store.dispatch('cart/addLines', lines);
                for (const slot of this.slots) {
                    this.quantities[slot.variant_id] = 0;
                }
            }
        },
    },
};
</script>

<template>
    <div>
        <div class="mb-2 flex items-center gap-2 text-xs text-slate-500">
            <span>Set all in-stock sizes to</span>
            <BaseQtyInput v-model="bulkQty" :min="0" label="Quantity for all sizes" />
            <button class="rounded border border-slate-300 px-2 py-1 hover:bg-slate-50" @click="applyToAll">
                Apply
            </button>
        </div>

        <div class="flex flex-wrap gap-1.5">
            <label v-for="slot in slots" :key="slot.variant_id" class="flex flex-col items-center">
                <span class="text-[11px] text-slate-500">{{ slot.eu_size }}</span>
                <input
                    v-model="quantities[slot.variant_id]"
                    type="number"
                    min="0"
                    :disabled="slot.stock === 0"
                    :placeholder="slot.stock === 0 ? '–' : '0'"
                    :aria-label="`EU size ${slot.eu_size} quantity`"
                    class="h-9 w-10 rounded border border-slate-300 text-center text-sm disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-300"
                />
            </label>
        </div>
        <button
            class="mt-3 rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
            @click="addToCart"
        >
            Add to cart
        </button>
    </div>
</template>
