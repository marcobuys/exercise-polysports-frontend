import { describe, expect, it } from 'vitest';
import { cartTotalCents, formatEuros, lineSubtotalCents } from '../money.js';

describe('money helpers', () => {
    it('formats cents as euros', () => {
        expect(formatEuros(9900)).toContain('99');
        expect(formatEuros(0)).toContain('0');
    });

    it('computes a line subtotal in cents', () => {
        expect(lineSubtotalCents({ unit_price_cents: 1000, quantity: 3 })).toBe(3000);
    });

    it('sums a cart in cents', () => {
        const lines = [
            { unit_price_cents: 1000, quantity: 2 },
            { unit_price_cents: 500, quantity: 3 },
        ];
        expect(cartTotalCents(lines)).toBe(3500);
    });
});
