<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ROOM_STATUS_CLASS, ROOM_STATUS_LABEL, RoomStatusValue, formatIdr } from '@/lib/roomStatus';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

interface RoomListItem {
    id: number;
    room_number: string;
    name: string | null;
    status: RoomStatusValue;
    monthly_price: string;
    property: { name: string } | null;
    building: { name: string } | null;
    floor: { name: string } | null;
    room_type: { name: string } | null;
    primary_image: { url: string } | null;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
}

const props = defineProps<{
    rooms: Paginated<RoomListItem>;
    properties: { id: number; name: string }[];
    roomTypes: { id: number; name: string; property_id: number }[];
    statuses: { value: string; label: string }[];
    filters: { search?: string; status?: string; property_id?: string; room_type_id?: string };
}>();

const filterForm = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    property_id: props.filters.property_id ?? '',
    room_type_id: props.filters.room_type_id ?? '',
});

function applyFilters() {
    router.get(route('admin.rooms.index'), { ...filterForm }, { preserveState: true, replace: true });
}

const selected = ref<number[]>([]);
function toggleSelect(id: number) {
    const idx = selected.value.indexOf(id);
    if (idx === -1) selected.value.push(id);
    else selected.value.splice(idx, 1);
}
function toggleSelectAll() {
    if (selected.value.length === props.rooms.data.length) {
        selected.value = [];
    } else {
        selected.value = props.rooms.data.map((r) => r.id);
    }
}

const bulkStatus = ref('');
function applyBulkStatus() {
    if (!bulkStatus.value || selected.value.length === 0) return;
    if (!confirm(`Ubah status ${selected.value.length} kamar terpilih menjadi "${ROOM_STATUS_LABEL[bulkStatus.value as RoomStatusValue]}"?`)) return;

    router.post(
        route('admin.rooms.bulk-status'),
        { room_ids: selected.value, status: bulkStatus.value },
        { onSuccess: () => { selected.value = []; bulkStatus.value = ''; } },
    );
}

function destroy(room: RoomListItem) {
    if (confirm(`Hapus kamar "${room.name ?? room.room_number}"? Seluruh foto dan riwayatnya juga akan dihapus.`)) {
        router.delete(route('admin.rooms.destroy', room.id));
    }
}
</script>

<template>
    <Head title="Kelola Kamar" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Kamar</h1>
            <Link :href="route('admin.rooms.create')">
                <PrimaryButton>+ Tambah Kamar</PrimaryButton>
            </Link>
        </div>

        <div class="mt-6 grid gap-3 rounded-xl border border-beige-200 bg-white p-4 shadow-soft sm:grid-cols-4">
            <input
                v-model="filterForm.search"
                type="text"
                placeholder="Cari nomor/nama kamar..."
                class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400"
                @keyup.enter="applyFilters"
            />
            <select v-model="filterForm.status" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                <option value="">Semua status</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <select v-model="filterForm.property_id" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                <option value="">Semua properti</option>
                <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
            <div class="flex gap-2">
                <select v-model="filterForm.room_type_id" class="w-full rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                    <option value="">Semua tipe</option>
                    <option v-for="rt in roomTypes" :key="rt.id" :value="rt.id">{{ rt.name }}</option>
                </select>
                <SecondaryButton @click="applyFilters">Cari</SecondaryButton>
            </div>
        </div>

        <div v-if="selected.length > 0" class="mt-4 flex items-center gap-3 rounded-xl bg-terracotta-50 p-4 text-sm">
            <span class="font-medium text-terracotta-700">{{ selected.length }} kamar dipilih</span>
            <select v-model="bulkStatus" class="rounded-lg border-beige-300 text-sm focus:border-terracotta-400 focus:ring-terracotta-400">
                <option value="" disabled>Ubah status menjadi...</option>
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
            </select>
            <PrimaryButton :disabled="!bulkStatus" @click="applyBulkStatus">Terapkan</PrimaryButton>
        </div>

        <div class="mt-4 overflow-x-auto rounded-xl border border-beige-200 bg-white shadow-soft">
            <table class="min-w-full divide-y divide-beige-100 text-sm">
                <thead class="bg-cream-50 text-xs uppercase tracking-wide text-charcoal-500">
                    <tr>
                        <th class="px-4 py-3 text-left">
                            <input type="checkbox" :checked="selected.length === rooms.data.length && rooms.data.length > 0" @change="toggleSelectAll" class="rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" />
                        </th>
                        <th class="px-4 py-3 text-left">Kamar</th>
                        <th class="px-4 py-3 text-left">Lokasi</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Harga</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-beige-100">
                    <tr v-for="room in rooms.data" :key="room.id">
                        <td class="px-4 py-3">
                            <input type="checkbox" :checked="selected.includes(room.id)" @change="toggleSelect(room.id)" class="rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-beige-200">
                                    <img v-if="room.primary_image" :src="room.primary_image.url" class="h-full w-full object-cover" />
                                </div>
                                <span class="font-medium text-charcoal-800">{{ room.name ?? `Kamar ${room.room_number}` }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-charcoal-500">
                            {{ room.property?.name }}<template v-if="room.building"> &middot; {{ room.building.name }}</template><template v-if="room.floor"> &middot; {{ room.floor.name }}</template>
                        </td>
                        <td class="px-4 py-3 text-charcoal-500">{{ room.room_type?.name }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="ROOM_STATUS_CLASS[room.status]">
                                {{ ROOM_STATUS_LABEL[room.status] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-charcoal-600">{{ formatIdr(room.monthly_price) }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.rooms.edit', room.id)" class="text-xs font-medium text-terracotta-600 hover:underline">Kelola</Link>
                            <button class="ml-3 text-xs font-medium text-red-600 hover:underline" @click="destroy(room)">Hapus</button>
                        </td>
                    </tr>
                    <tr v-if="rooms.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-charcoal-400">Belum ada kamar.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="rooms.links" />
    </AdminLayout>
</template>
