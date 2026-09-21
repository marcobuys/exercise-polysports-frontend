<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { getProducts } from '../api.js';
import { formatEuros } from '../money.js';
import OrderGrid from './OrderGrid.vue';

const props = defineProps({
    customer: { type: Object, required: true },
});

const emit = defineEmits(['open']);

const products = ref([]);
const loading = ref(false);
const search = ref('');

const filters = reactive({ category: 'all' });

const categories = [
    { value: 'all', label: 'All' },
    { value: 'FG', label: 'Firm ground' },
    { value: 'AG', label: 'Artificial ground' },
    { value: 'IN', label: 'Indoor' },
];

// The search box hits the server (name/SKU search). The category is applied
// here on the client so switching it feels instant.
const visibleProducts = computed(() => {
    if (filters.category === 'all') {
        return products.value;
    }
    return products.value.filter((p) => p.category === filters.category);
});

async function fetchProducts() {
    loading.value = true;
    const data = await getProducts({
        customerId: props.customer.id,
        q: search.value,
    });
    products.value = data.data;
    loading.value = false;
}

watch(search, () => fetchProducts());

onMounted(fetchProducts);
</script>

<template>
    <section>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <input
                v-model="search"
                type="search"
                placeholder="Search by name or SKU…"
                class="h-10 flex-1 rounded-lg border border-slate-300 px-3 text-sm"
            />
            <select v-model="filters.category" class="h-10 rounded-lg border border-slate-300 px-3 text-sm">
                <option v-for="c in categories" :key="c.value" :value="c.value">{{ c.label }}</option>
            </select>
        </div>

        <p v-if="loading" class="text-sm text-slate-500">Loading products…</p>

        <div class="grid gap-4 sm:grid-cols-2">
            <article
                v-for="(product, index) in visibleProducts"
                :key="index"
                class="rounded-lg border border-slate-200 bg-white p-4"
            >
                <div class="flex gap-3">
                    <button
                        class="flex h-16 w-16 shrink-0 items-center justify-center rounded bg-slate-100 text-3xl"
                        title="View details"
                        @click="emit('open', product)"
                    >
                        👟
                    </button>
                    <div class="min-w-0 flex-1">
                        <button class="block text-left" @click="emit('open', product)">
                            <h3 class="truncate font-semibold text-slate-900">{{ product.name }}</h3>
                        </button>
                        <p class="text-xs text-slate-400">{{ product.sku }} · {{ product.colorway }}</p>
                        <p class="mt-1 text-sm">
                            <span class="font-semibold text-slate-900">{{ formatEuros(product.price_cents) }}</span>
                            <span class="ml-1 text-xs text-slate-400">{{ product.category }}</span>
                        </p>
                    </div>
                </div>

                <div class="mt-3 border-t border-slate-100 pt-3">
                    <OrderGrid :product="product" />
                </div>
            </article>
        </div>

        <p v-if="!loading && visibleProducts.length === 0" class="text-sm text-slate-500">No products found.</p>
    </section>
</template>
