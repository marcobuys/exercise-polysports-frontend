<script>
import { formatEuros } from '../money.js';

export default {
    name: 'MiniCart',
    data() {
        return {
            expanded: false,
        };
    },
    computed: {
        lines() {
            return this.$store.getters['cart/lines'];
        },
        count() {
            return this.$store.getters['cart/count'];
        },
        subtotalCents() {
            return this.$store.getters['cart/subtotalCents'];
        },
    },
    methods: {
        formatEuros,
        removeLine(variantId) {
            this.$store.dispatch('cart/removeLine', variantId);
        },
    },
};
</script>

<template>
    <div class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-2">
            <button class="flex items-center gap-3 text-sm" @click="expanded = !expanded">
                <span class="font-medium text-slate-700">🛒 {{ count }} line{{ count === 1 ? '' : 's' }}</span>
                <span class="font-semibold text-slate-900">{{ formatEuros(subtotalCents) }}</span>
                <span class="text-xs text-slate-400">{{ expanded ? 'hide' : 'view' }}</span>
            </button>
            <button
                class="rounded bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300"
                :disabled="count === 0"
                @click="$emit('checkout')"
            >
                Checkout
            </button>
        </div>

        <div v-if="expanded && count" class="mx-auto max-w-5xl px-4 pb-3">
            <ul class="divide-y divide-slate-100 rounded border border-slate-200">
                <li
                    v-for="line in lines"
                    :key="line.product_variant_id"
                    class="flex items-center justify-between px-3 py-2 text-sm"
                >
                    <span class="text-slate-700">
                        {{ line.product_name }} — EU {{ line.eu_size }} × {{ line.quantity }}
                    </span>
                    <span class="flex items-center gap-3">
                        <span class="text-slate-600">{{ formatEuros(line.unit_price_cents * line.quantity) }}</span>
                        <span
                            class="cursor-pointer text-xs text-red-500 hover:text-red-700"
                            @click="removeLine(line.product_variant_id)"
                        >
                            remove
                        </span>
                    </span>
                </li>
            </ul>
        </div>
    </div>
</template>
