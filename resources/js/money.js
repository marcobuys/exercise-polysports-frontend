export function formatEuros(cents) {
    return (cents / 100).toLocaleString('en-IE', {
        style: 'currency',
        currency: 'EUR',
    });
}

export function lineSubtotalCents(line) {
    return line.unit_price_cents * line.quantity;
}

export function cartTotalCents(lines) {
    return lines.reduce((sum, line) => sum + lineSubtotalCents(line), 0);
}
