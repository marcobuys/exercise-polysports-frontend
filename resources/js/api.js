const BASE = '/api';

async function request(path, options = {}) {
    const res = await fetch(`${BASE}${path}`, {
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        ...options,
    });

    if (!res.ok) {
        const body = await res.json().catch(() => ({}));
        const error = new Error(body.message || `Request failed (${res.status})`);
        error.status = res.status;
        error.body = body;
        throw error;
    }

    return res.json();
}

export function getCustomers() {
    return request('/customers');
}

export function getProducts({ customerId, q, category, signal } = {}) {
    const params = new URLSearchParams();
    if (customerId) params.set('customer_id', customerId);
    if (q) params.set('q', q);
    if (category && category !== 'all') params.set('category', category);

    const query = params.toString();
    return request(`/products${query ? `?${query}` : ''}`, { signal });
}

export function getProduct(id, customerId) {
    const params = customerId ? `?customer_id=${customerId}` : '';
    return request(`/products/${id}${params}`);
}

export function createOrder(payload) {
    return request('/orders', {
        method: 'POST',
        body: JSON.stringify(payload),
    });
}
