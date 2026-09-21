<script>
import { getCustomers } from '../api.js';

const TIER_BADGE = {
    gold: 'bg-amber-100 text-amber-800',
    silver: 'bg-slate-200 text-slate-700',
    bronze: 'bg-orange-100 text-orange-800',
};

export default {
    name: 'CustomerSelect',
    emits: ['select'],
    data() {
        return {
            customers: [],
            loading: true,
            error: null,
            tierBadge: TIER_BADGE,
        };
    },
    computed: {
        // Quick headline so the account manager can see how many of their
        // top-tier (gold) accounts are available to order for.
        goldCount: () => {
            const list = this?.customers ?? [];
            return list.filter((c) => c.tier === 'gold').length;
        },
    },
    async mounted() {
        try {
            const { data } = await getCustomers();
            this.customers = data;
        } catch (e) {
            this.error = e.message;
        } finally {
            this.loading = false;
        }
    },
};
</script>

<template>
    <section>
        <h1 class="mb-1 text-lg font-semibold text-slate-900">Which customer are you ordering for?</h1>
        <p class="mb-4 text-sm text-slate-500">
            Pick the business account before browsing the catalogue.
            <span v-if="!loading && !error" class="text-slate-400">· {{ goldCount }} gold-tier account(s)</span>
        </p>

        <p v-if="loading" class="text-sm text-slate-500">Loading customers…</p>
        <p v-else-if="error" class="text-sm text-red-600">{{ error }}</p>

        <ul v-else class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <li v-for="c in customers" :key="c.id">
                <button
                    class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-slate-50"
                    @click="$emit('select', c)"
                >
                    <span>
                        <span class="font-medium text-slate-900">{{ c.company_name }}</span>
                        <span class="ml-2 text-xs text-slate-400">{{ c.email }}</span>
                    </span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium capitalize" :class="tierBadge[c.tier]">
                        {{ c.tier }}
                    </span>
                </button>
            </li>
        </ul>
    </section>
</template>
