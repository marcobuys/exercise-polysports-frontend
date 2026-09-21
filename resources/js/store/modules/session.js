// Session module — who we're ordering for right now.
const state = () => ({
    customer: null,
});

const getters = {
    customer: (state) => state.customer,
    tier: (state) => state.customer?.tier ?? null,
};

const mutations = {
    SET_CUSTOMER(state, customer) {
        state.customer = customer;
    },
    CLEAR(state) {
        state.customer = null;
    },
};

const actions = {
    selectCustomer({ commit }, customer) {
        commit('SET_CUSTOMER', customer);
    },
    clear({ commit }) {
        commit('CLEAR');
    },
};

export default {
    namespaced: true,
    state,
    getters,
    mutations,
    actions,
};
