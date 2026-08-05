<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatIdr } from '@/lib/roomStatus';
import { formatDate } from '@/lib/date';
import { Head, Link } from '@inertiajs/vue3';

interface LeaseRow {
    id: number;
    lease_number: string;
    status: string;
    start_date: string;
    end_date: string;
    monthly_price: string;
    room: { name: string | null; room_number: string } | null;
}
interface InvoiceRow {
    id: number;
    invoice_number: string;
    status: string;
    total_amount: string;
    due_date: string;
}
interface MaintenanceRow {
    id: number;
    title: string;
    status: string;
    created_at: string;
}
interface DocumentRow {
    id: number;
    document_type: string;
    original_filename: string | null;
}
interface TenantDetail {
    id: number;
    status: string;
    joined_at: string | null;
    moved_out_at: string | null;
    emergency_contact_name: string | null;
    emergency_contact_phone: string | null;
    user: { name: string; email: string; whatsapp_number: string | null };
    room: { name: string | null; room_number: string; property: { name: string } } | null;
    booking: { booking_code: string; documents: DocumentRow[] } | null;
    leases: LeaseRow[];
    invoices: InvoiceRow[];
    maintenance_requests: MaintenanceRow[];
}

defineProps<{ tenant: TenantDetail }>();

const STATUS_LABEL: Record<string, string> = { prospective: 'Calon Penyewa', active: 'Penyewa Aktif', inactive: 'Tidak Aktif' };
const LEASE_STATUS_LABEL: Record<string, string> = {
    draft: 'Draft', pending_approval: 'Menunggu Persetujuan', active: 'Aktif', ending_soon: 'Akan Berakhir',
    completed: 'Selesai', cancelled: 'Dibatalkan', extended: 'Diperpanjang',
};
</script>

<template>
    <Head :title="`Penyewa — ${tenant.user.name}`" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">{{ tenant.user.name }}</h1>
            <Link :href="route('admin.tenants.index')" class="text-sm text-charcoal-500 hover:underline">Kembali</Link>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Info Penyewa</h2>
                <dl class="mt-3 space-y-2 text-sm">
                    <div><dt class="text-charcoal-400">Status</dt><dd class="font-medium text-charcoal-800">{{ STATUS_LABEL[tenant.status] }}</dd></div>
                    <div><dt class="text-charcoal-400">Email</dt><dd>{{ tenant.user.email }}</dd></div>
                    <div><dt class="text-charcoal-400">WhatsApp</dt><dd>{{ tenant.user.whatsapp_number ?? '-' }}</dd></div>
                    <div><dt class="text-charcoal-400">Kamar Saat Ini</dt><dd>{{ tenant.room ? (tenant.room.name ?? `Kamar ${tenant.room.room_number}`) + ' — ' + tenant.room.property.name : '-' }}</dd></div>
                    <div><dt class="text-charcoal-400">Bergabung</dt><dd>{{ formatDate(tenant.joined_at) }}</dd></div>
                    <div v-if="tenant.moved_out_at"><dt class="text-charcoal-400">Keluar</dt><dd>{{ formatDate(tenant.moved_out_at) }}</dd></div>
                    <div><dt class="text-charcoal-400">Kontak Darurat</dt><dd>{{ tenant.emergency_contact_name ?? '-' }} ({{ tenant.emergency_contact_phone ?? '-' }})</dd></div>
                </dl>

                <div v-if="tenant.booking" class="mt-4 border-t border-beige-100 pt-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Dokumen Identitas</h3>
                    <ul class="mt-2 space-y-1 text-sm text-charcoal-600">
                        <li v-for="doc in tenant.booking.documents" :key="doc.id">{{ doc.document_type }} — {{ doc.original_filename }}</li>
                        <li v-if="tenant.booking.documents.length === 0" class="text-xs text-charcoal-400">Tidak ada dokumen.</li>
                    </ul>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Riwayat Kontrak</h2>
                    <ul class="mt-3 space-y-2">
                        <li v-for="lease in tenant.leases" :key="lease.id" class="flex items-center justify-between rounded-lg bg-cream-50 px-3 py-2 text-sm">
                            <span>
                                {{ lease.lease_number }} — {{ lease.room?.name ?? `Kamar ${lease.room?.room_number}` }}
                                <span class="text-charcoal-400">({{ lease.start_date }} s.d. {{ lease.end_date }})</span>
                            </span>
                            <span class="flex items-center gap-3">
                                <span class="text-xs font-medium text-charcoal-600">{{ LEASE_STATUS_LABEL[lease.status] }}</span>
                                <Link :href="route('admin.leases.show', lease.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Kelola</Link>
                            </span>
                        </li>
                        <li v-if="tenant.leases.length === 0" class="text-xs text-charcoal-400">Belum ada kontrak.</li>
                    </ul>
                </div>

                <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Tagihan</h2>
                    <ul class="mt-3 space-y-2">
                        <li v-for="invoice in tenant.invoices" :key="invoice.id" class="flex items-center justify-between rounded-lg bg-cream-50 px-3 py-2 text-sm">
                            <span>{{ invoice.invoice_number }} <span class="text-charcoal-400">(jatuh tempo {{ invoice.due_date }})</span></span>
                            <span class="flex items-center gap-3">
                                <span class="font-medium text-charcoal-800">{{ formatIdr(invoice.total_amount) }}</span>
                                <Link :href="route('admin.invoices.show', invoice.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                            </span>
                        </li>
                        <li v-if="tenant.invoices.length === 0" class="text-xs text-charcoal-400">Belum ada tagihan.</li>
                    </ul>
                </div>

                <div class="rounded-xl border border-beige-200 bg-white p-5 shadow-soft">
                    <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-charcoal-500">Keluhan &amp; Perawatan</h2>
                    <ul class="mt-3 space-y-2">
                        <li v-for="mr in tenant.maintenance_requests" :key="mr.id" class="flex items-center justify-between rounded-lg bg-cream-50 px-3 py-2 text-sm">
                            <span>{{ mr.title }}</span>
                            <Link :href="route('admin.maintenance-requests.show', mr.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Detail</Link>
                        </li>
                        <li v-if="tenant.maintenance_requests.length === 0" class="text-xs text-charcoal-400">Belum ada keluhan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
