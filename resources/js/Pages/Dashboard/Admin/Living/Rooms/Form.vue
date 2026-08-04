<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ROOM_STATUS_CLASS, ROOM_STATUS_LABEL, RoomStatusValue, formatIdr } from '@/lib/roomStatus';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface FloorOption {
    id: number;
    name: string;
    level: number;
}
interface BuildingOption {
    id: number;
    name: string;
    floors: FloorOption[];
}
interface PropertyOption {
    id: number;
    name: string;
    buildings: BuildingOption[];
}
interface RoomTypeOption {
    id: number;
    name: string;
    property_id: number;
}
interface FacilityOption {
    id: number;
    name: string;
    type: 'room' | 'shared';
}
interface RoomImageItem {
    id: number;
    url: string;
    thumbnail_url: string | null;
    caption: string | null;
    is_primary: boolean;
    sort_order: number;
}
interface StatusHistoryItem {
    id: number;
    from_status: RoomStatusValue | null;
    to_status: RoomStatusValue;
    reason: string | null;
    created_at: string;
    changed_by: { name: string } | null;
}
interface RoomDetail {
    id: number;
    property_id: number;
    building_id: number;
    floor_id: number;
    room_type_id: number;
    room_number: string;
    name: string | null;
    status: RoomStatusValue;
    size_sqm: string | null;
    capacity: number;
    monthly_price: string;
    deposit_amount: string;
    description: string | null;
    available_from: string | null;
    is_active: boolean;
    images: RoomImageItem[];
    facilities: { id: number }[];
    status_histories: StatusHistoryItem[];
}

const props = defineProps<{
    room: RoomDetail | null;
    properties: PropertyOption[];
    roomTypes: RoomTypeOption[];
    facilities: FacilityOption[];
    statuses?: { value: string; label: string }[];
}>();

const isEdit = props.room !== null;

const form = useForm({
    property_id: props.room?.property_id ?? ('' as number | string),
    building_id: props.room?.building_id ?? ('' as number | string),
    floor_id: props.room?.floor_id ?? ('' as number | string),
    room_type_id: props.room?.room_type_id ?? ('' as number | string),
    room_number: props.room?.room_number ?? '',
    name: props.room?.name ?? '',
    size_sqm: props.room?.size_sqm ?? '',
    capacity: props.room?.capacity ?? 1,
    monthly_price: props.room?.monthly_price ?? '',
    deposit_amount: props.room?.deposit_amount ?? '',
    description: props.room?.description ?? '',
    available_from: props.room?.available_from ?? '',
    is_active: props.room?.is_active ?? true,
});

const buildingsForProperty = computed<BuildingOption[]>(() => {
    const property = props.properties.find((p) => p.id === Number(form.property_id));
    return property?.buildings ?? [];
});
const floorsForBuilding = computed<FloorOption[]>(() => {
    const building = buildingsForProperty.value.find((b) => b.id === Number(form.building_id));
    return building?.floors ?? [];
});
const roomTypesForProperty = computed<RoomTypeOption[]>(() => {
    return props.roomTypes.filter((rt) => rt.property_id === Number(form.property_id));
});

function submit() {
    if (isEdit) {
        form.put(route('admin.rooms.update', props.room!.id));
    } else {
        form.post(route('admin.rooms.store'));
    }
}

/* Photo upload */
const photoForm = useForm({ image: null as File | null, caption: '' });
const draggingId = ref<number | null>(null);

function uploadPhoto() {
    photoForm.post(route('admin.rooms.photos.store', props.room!.id), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => photoForm.reset(),
    });
}
function setPrimary(imageId: number) {
    router.put(route('admin.rooms.photos.primary', [props.room!.id, imageId]), {}, { preserveScroll: true });
}
function destroyPhoto(imageId: number) {
    if (confirm('Hapus foto ini?')) {
        router.delete(route('admin.rooms.photos.destroy', [props.room!.id, imageId]), { preserveScroll: true });
    }
}
function onDragStart(id: number) {
    draggingId.value = id;
}
function onDrop(targetId: number) {
    if (draggingId.value === null || draggingId.value === targetId || !props.room) return;
    const ids = props.room.images.map((i) => i.id);
    const fromIndex = ids.indexOf(draggingId.value);
    const toIndex = ids.indexOf(targetId);
    ids.splice(toIndex, 0, ids.splice(fromIndex, 1)[0]);
    router.put(route('admin.rooms.photos.reorder', props.room.id), { ids }, { preserveScroll: true });
    draggingId.value = null;
}

/* Facilities */
const selectedFacilityIds = ref<number[]>(props.room?.facilities.map((f) => f.id) ?? []);
function submitFacilities() {
    router.put(
        route('admin.rooms.facilities.update', props.room!.id),
        { facility_ids: selectedFacilityIds.value },
        { preserveScroll: true },
    );
}

/* Status */
const statusForm = useForm({ status: props.room?.status ?? '', reason: '' });
function submitStatus() {
    statusForm.put(route('admin.rooms.status.update', props.room!.id), {
        preserveScroll: true,
        onSuccess: () => (statusForm.reason = ''),
    });
}
</script>

<template>
    <Head :title="isEdit ? 'Kelola Kamar' : 'Tambah Kamar'" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">
                {{ isEdit ? `Kelola Kamar ${room?.room_number}` : 'Tambah Kamar' }}
            </h1>
            <Link :href="route('admin.rooms.index')" class="text-sm font-medium text-charcoal-500 hover:underline">Kembali ke daftar</Link>
        </div>

        <div class="mt-6 rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
            <h2 class="font-display text-lg font-semibold text-charcoal-800">Detail Kamar</h2>
            <form class="mt-4 grid gap-4" @submit.prevent="submit">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="Properti" />
                        <select v-model="form.property_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option value="" disabled>Pilih properti</option>
                            <option v-for="p in properties" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.property_id" />
                    </div>
                    <div>
                        <InputLabel value="Gedung" />
                        <select v-model="form.building_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option value="" disabled>Pilih gedung</option>
                            <option v-for="b in buildingsForProperty" :key="b.id" :value="b.id">{{ b.name }}</option>
                        </select>
                        <InputError :message="form.errors.building_id" />
                    </div>
                    <div>
                        <InputLabel value="Lantai" />
                        <select v-model="form.floor_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option value="" disabled>Pilih lantai</option>
                            <option v-for="f in floorsForBuilding" :key="f.id" :value="f.id">{{ f.name }}</option>
                        </select>
                        <InputError :message="form.errors.floor_id" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <InputLabel value="Tipe Kamar" />
                        <select v-model="form.room_type_id" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option value="" disabled>Pilih tipe kamar</option>
                            <option v-for="rt in roomTypesForProperty" :key="rt.id" :value="rt.id">{{ rt.name }}</option>
                        </select>
                        <InputError :message="form.errors.room_type_id" />
                    </div>
                    <div>
                        <InputLabel value="Nomor Kamar" />
                        <TextInput v-model="form.room_number" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.room_number" />
                    </div>
                    <div>
                        <InputLabel value="Nama Kamar (opsional)" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <div>
                        <InputLabel value="Luas (m²)" />
                        <TextInput v-model="form.size_sqm" type="number" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Kapasitas" />
                        <TextInput v-model.number="form.capacity" type="number" min="1" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <InputLabel value="Harga/Bulan (Rp)" />
                        <TextInput v-model="form.monthly_price" type="number" min="0" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.monthly_price" />
                    </div>
                    <div>
                        <InputLabel value="Deposit (Rp)" />
                        <TextInput v-model="form.deposit_amount" type="number" min="0" class="mt-1 block w-full" required />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Tersedia Mulai" />
                        <TextInput v-model="form.available_from" type="date" class="mt-1 block w-full" />
                    </div>
                    <label class="mt-6 flex items-center gap-2">
                        <Checkbox v-model:checked="form.is_active" />
                        <span class="text-sm text-charcoal-600">Aktif (tampil di katalog publik)</span>
                    </label>
                </div>

                <div>
                    <InputLabel value="Deskripsi" />
                    <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :disabled="form.processing">{{ isEdit ? 'Simpan Perubahan' : 'Tambah Kamar' }}</PrimaryButton>
                </div>
            </form>
        </div>

        <template v-if="isEdit && room">
            <!-- Photos -->
            <div class="mt-6 rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Foto Kamar</h2>
                <p class="mt-1 text-sm text-charcoal-400">Seret (drag) kartu foto untuk mengubah urutan. Foto pertama otomatis menjadi foto utama.</p>

                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div
                        v-for="image in room.images"
                        :key="image.id"
                        class="group relative cursor-move overflow-hidden rounded-xl border border-beige-200"
                        draggable="true"
                        @dragstart="onDragStart(image.id)"
                        @dragover.prevent
                        @drop="onDrop(image.id)"
                    >
                        <img :src="image.thumbnail_url ?? image.url" class="h-28 w-full object-cover" :alt="image.caption ?? ''" />
                        <span v-if="image.is_primary" class="absolute left-2 top-2 rounded-full bg-terracotta-500 px-2 py-0.5 text-xs font-semibold text-white">Utama</span>
                        <div class="flex items-center justify-between bg-white p-2">
                            <button v-if="!image.is_primary" class="text-xs text-terracotta-600 hover:underline" @click="setPrimary(image.id)">Jadikan Utama</button>
                            <span v-else class="text-xs text-charcoal-300">&nbsp;</span>
                            <button class="text-xs text-red-600 hover:underline" @click="destroyPhoto(image.id)">Hapus</button>
                        </div>
                    </div>
                    <p v-if="room.images.length === 0" class="col-span-full text-sm text-charcoal-400">Belum ada foto.</p>
                </div>

                <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="uploadPhoto">
                    <div>
                        <InputLabel value="Unggah Foto Baru" />
                        <input type="file" accept="image/*" required class="mt-1 block text-sm" @change="photoForm.image = ($event.target as HTMLInputElement).files?.[0] ?? null" />
                        <InputError :message="photoForm.errors.image" />
                    </div>
                    <div>
                        <InputLabel value="Keterangan (opsional)" />
                        <TextInput v-model="photoForm.caption" class="mt-1 block w-full" />
                    </div>
                    <PrimaryButton :disabled="photoForm.processing">Unggah</PrimaryButton>
                </form>
            </div>

            <!-- Facilities -->
            <div class="mt-6 rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Fasilitas Kamar</h2>
                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    <label v-for="fac in facilities" :key="fac.id" class="flex items-center gap-2 text-sm text-charcoal-600">
                        <input v-model="selectedFacilityIds" type="checkbox" :value="fac.id" class="rounded border-beige-300 text-terracotta-500 focus:ring-terracotta-400" />
                        {{ fac.name }}
                    </label>
                </div>
                <div class="mt-4 flex justify-end">
                    <PrimaryButton @click="submitFacilities">Simpan Fasilitas</PrimaryButton>
                </div>
            </div>

            <!-- Status -->
            <div class="mt-6 rounded-xl border border-beige-200 bg-white p-6 shadow-soft">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">Status Kamar</h2>
                <p class="mt-1 text-sm">
                    Status saat ini:
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium" :class="ROOM_STATUS_CLASS[room.status]">{{ ROOM_STATUS_LABEL[room.status] }}</span>
                </p>
                <form class="mt-4 flex flex-wrap items-end gap-3" @submit.prevent="submitStatus">
                    <div>
                        <InputLabel value="Ubah Status Menjadi" />
                        <select v-model="statusForm.status" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400">
                            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <InputLabel value="Alasan (opsional)" />
                        <TextInput v-model="statusForm.reason" class="mt-1 block w-full" />
                    </div>
                    <PrimaryButton :disabled="statusForm.processing">Perbarui Status</PrimaryButton>
                </form>

                <h3 class="mt-6 text-sm font-semibold text-charcoal-700">Riwayat Perubahan Status</h3>
                <ul class="mt-2 space-y-2">
                    <li v-for="h in room.status_histories" :key="h.id" class="flex items-center justify-between rounded-lg bg-cream-50 px-3 py-2 text-xs text-charcoal-600">
                        <span>
                            <template v-if="h.from_status">{{ ROOM_STATUS_LABEL[h.from_status] }} &rarr; </template>{{ ROOM_STATUS_LABEL[h.to_status] }}
                            <template v-if="h.reason"> &mdash; {{ h.reason }}</template>
                            <template v-if="h.changed_by"> (oleh {{ h.changed_by.name }})</template>
                        </span>
                        <span class="text-charcoal-400">{{ h.created_at }}</span>
                    </li>
                    <li v-if="room.status_histories.length === 0" class="text-xs text-charcoal-400">Belum ada riwayat perubahan.</li>
                </ul>
            </div>
        </template>
    </AdminLayout>
</template>
