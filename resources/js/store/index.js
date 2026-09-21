import Vue from 'vue';
import Vuex from 'vuex';
import cart from './modules/cart';
import session from './modules/session';

Vue.use(Vuex);

const store = new Vuex.Store({
    // Strict mode catches state that is mutated outside a mutation handler.
    // Leave it on in the exercise — it's how we keep the store trustworthy.
    strict: true,
    modules: {
        cart,
        session,
    },
});

export default store;
