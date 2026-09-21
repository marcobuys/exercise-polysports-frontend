import { describe, expect, it } from 'vitest';
import Vue from 'vue';
import Vuex from 'vuex';
import cart from '../store/modules/cart.js';

Vue.use(Vuex);
Vue.config.productionTip = false;

function freshStore() {
    return new Vuex.Store({ modules: { cart } });
}

// The cart getters must update *reactively* when lines are added — that's what
// the mini-cart and checkout subscribe to. We assert reactivity (via store.watch),
// not just the value on a fresh read, because Vue 2 can silently fail to track a
// newly-added object key.
describe('cart store', () => {
    it('reactively reflects a newly added line', async () => {
        const store = freshStore();
        let count = store.getters['cart/count'];
        const unwatch = store.watch(
            (state, getters) => getters['cart/count'],
            (val) => {
                count = val;
            },
        );

        store.dispatch('cart/addLines', [
            {
                product_variant_id: 1,
                product_name: 'Vapor FG',
                eu_size: 42,
                unit_price_cents: 9000,
                quantity: 2,
            },
        ]);
        await Vue.nextTick();
        unwatch();

        expect(count).toBe(1);
    });

    it('reactively reflects the subtotal', async () => {
        const store = freshStore();
        let subtotal = store.getters['cart/subtotalCents'];
        const unwatch = store.watch(
            (state, getters) => getters['cart/subtotalCents'],
            (val) => {
                subtotal = val;
            },
        );

        store.dispatch('cart/addLines', [
            {
                product_variant_id: 7,
                product_name: 'Predator AG',
                eu_size: 44,
                unit_price_cents: 12000,
                quantity: 3,
            },
        ]);
        await Vue.nextTick();
        unwatch();

        expect(subtotal).toBe(36000);
    });
});
