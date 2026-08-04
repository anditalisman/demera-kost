/**
 * Build a wa.me deep link from a local/international phone number.
 * Accepts formats like "08123456789", "+62 812-3456-789", "62812...".
 */
export function waLink(phone: string, message?: string): string {
    let digits = phone.replace(/[^0-9]/g, '');

    if (digits.startsWith('0')) {
        digits = '62' + digits.slice(1);
    }

    const query = message ? `?text=${encodeURIComponent(message)}` : '';

    return `https://wa.me/${digits}${query}`;
}
