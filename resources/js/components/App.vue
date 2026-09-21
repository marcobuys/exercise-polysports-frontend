<script>
import MiniCart from './MiniCart.vue';
import CustomerSelect from './CustomerSelect.vue';
import ProductList from './ProductList.vue';
import ProductDetail from './ProductDetail.vue';
import Checkout from './Checkout.vue';
import { REGION } from '../config.js';

export default {
    name: 'App',
    components: { MiniCart, CustomerSelect, ProductList, ProductDetail, Checkout },
    data() {
        return {
            region: REGION,
            view: 'select-customer',
            selectedProduct: null,
        };
    },
    computed: {
        customer() {
            return this.$store.getters['session/customer'];
        },
    },
    methods: {
        selectCustomer(customer) {
            this.$store.dispatch('session/selectCustomer', customer);
            this.view = 'plp';
        },
        changeCustomer() {
            // Start a fresh order for a different account.
            this.$store.state.cart.lines = {};
            this.$store.dispatch('session/clear');
            this.selectedProduct = null;
            this.view = 'select-customer';
        },
        openProduct(product) {
            this.selectedProduct = product;
            this.view = 'pdp';
        },
    },
};
</script>

<template>
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-bold tracking-tight text-slate-900">Poly<span class="text-indigo-600">Sports</span></span>
                    <span class="text-xs uppercase tracking-wide text-slate-400">{{ region }} wholesale back-office</span>
                </div>
                <button
                    v-if="customer"
                    class="text-sm text-slate-500 hover:text-slate-800"
                    @click="changeCustomer"
                >
                    {{ customer.company_name }} ({{ customer.tier }}) · change
                </button>
            </div>
        </header>

        <MiniCart
            v-if="customer"
            @checkout="view = 'checkout'"
        />

        <main class="mx-auto max-w-5xl px-4 py-6">
            <CustomerSelect
                v-if="view === 'select-customer'"
                @select="selectCustomer"
            />

            <ProductList
                v-else-if="view === 'plp'"
                :customer="customer"
                @open="openProduct"
            />

            <ProductDetail
                v-else-if="view === 'pdp'"
                :customer="customer"
                :product="selectedProduct"
                @back="view = 'plp'"
            />

            <Checkout
                v-else-if="view === 'checkout'"
                :customer="customer"
                @placed="view = 'plp'"
                @back="view = 'plp'"
            />
        </main>
    </div>
</template>
