// Cart module — the single source of truth for the current order.
//
// Lines are stored keyed by product-variant id so we can find/merge a size in
// O(1) as the account manager builds up a bulk order across many sizes.
const state = () => ({
    lines: {},
});

const getters = {
    lines: (state) => Object.values(state.lines),
    count: (state) => Object.keys(state.lines).length,
    subtotalCents: (state) =>
        Object.values(state.lines).reduce(
            (sum, line) => sum + line.unit_price_cents * line.quantity,
            0,
        ),
};

const mutations = {
    ADD_LINES(state, newLines) {
        for (const line of newLines) {
            const existing = state.lines[line.product_variant_id];
            if (existing) {
                existing.quantity += line.quantity;
            } else {
                state.lines[line.product_variant_id] = { ...line };
            }
        }
    },
    REMOVE_LINE(state, variantId) {
        delete state.lines[variantId];
    },
    CLEAR(state) {
        state.lines = {};
    },
};

const actions = {
    addLines({ commit }, lines) {
        commit('ADD_LINES', lines);
    },
    removeLine({ commit }, variantId) {
        commit('REMOVE_LINE', variantId);
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
