export type RoomStatusValue = 'available' | 'held' | 'awaiting_payment' | 'occupied' | 'maintenance' | 'inactive';

export const ROOM_STATUS_LABEL: Record<RoomStatusValue, string> = {
    available: 'Tersedia',
    held: 'Ditahan Sementara',
    awaiting_payment: 'Menunggu Pembayaran',
    occupied: 'Terisi',
    maintenance: 'Dalam Perawatan',
    inactive: 'Tidak Aktif',
};

export const ROOM_STATUS_CLASS: Record<RoomStatusValue, string> = {
    available: 'bg-green-50 text-green-700',
    held: 'bg-amber-50 text-amber-700',
    awaiting_payment: 'bg-amber-50 text-amber-700',
    occupied: 'bg-charcoal-100 text-charcoal-600',
    maintenance: 'bg-red-50 text-red-700',
    inactive: 'bg-charcoal-100 text-charcoal-400',
};

export function formatIdr(value: string | number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value));
}
