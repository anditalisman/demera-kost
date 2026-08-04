<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface FloorItem {
    id: number;
    name: string;
    level: number;
    description: string | null;
}

interface BuildingItem {
    id: number;
    name: string;
    code: string | null;
    description: string | null;
    floors: FloorItem[];
}

interface PropertyItem {
    id: number;
    name: string;
    address: string;
    city: string;
    province: string;
    postal_code: string | null;
    latitude: number | null;
    longitude: number | null;
    description: string | null;
    house_rules: string | null;
    contact_phone: string | null;
    contact_whatsapp: string | null;
    is_active: boolean;
    buildings: BuildingItem[];
}

defineProps<{ properties: PropertyItem[] }>();

const expanded = ref<Record<number, boolean>>({});
function toggle(id: number) {
    expanded.value[id] = !expanded.value[id];
}

/* Property modal */
const showPropertyModal = ref(false);
const editingProperty = ref<PropertyItem | null>(null);
const propertyForm = useForm({
    name: '',
    address: '',
    city: '',
    province: '',
    postal_code: '',
    latitude: '' as string | number,
    longitude: '' as string | number,
    description: '',
    house_rules: '',
    contact_phone: '',
    contact_whatsapp: '',
    is_active: true,
});

function openCreateProperty() {
    editingProperty.value = null;
    propertyForm.reset();
    showPropertyModal.value = true;
}
function openEditProperty(item: PropertyItem) {
    editingProperty.value = item;
    propertyForm.name = item.name;
    propertyForm.address = item.address;
    propertyForm.city = item.city;
    propertyForm.province = item.province;
    propertyForm.postal_code = item.postal_code ?? '';
    propertyForm.latitude = item.latitude ?? '';
    propertyForm.longitude = item.longitude ?? '';
    propertyForm.description = item.description ?? '';
    propertyForm.house_rules = item.house_rules ?? '';
    propertyForm.contact_phone = item.contact_phone ?? '';
    propertyForm.contact_whatsapp = item.contact_whatsapp ?? '';
    propertyForm.is_active = item.is_active;
    showPropertyModal.value = true;
}
function submitProperty() {
    const options = { onSuccess: () => (showPropertyModal.value = false) };
    if (editingProperty.value) {
        propertyForm.put(route('admin.properties.update', editingProperty.value.id), options);
    } else {
        propertyForm.post(route('admin.properties.store'), options);
    }
}
function destroyProperty(item: PropertyItem) {
    if (confirm(`Hapus properti "${item.name}"? Seluruh gedung, lantai, dan kamar di dalamnya juga akan dihapus.`)) {
        router.delete(route('admin.properties.destroy', item.id));
    }
}

/* Building modal */
const showBuildingModal = ref(false);
const editingBuilding = ref<BuildingItem | null>(null);
const buildingPropertyId = ref<number | null>(null);
const buildingForm = useForm({ name: '', code: '', description: '' });

function openCreateBuilding(propertyId: number) {
    editingBuilding.value = null;
    buildingPropertyId.value = propertyId;
    buildingForm.reset();
    showBuildingModal.value = true;
}
function openEditBuilding(item: BuildingItem) {
    editingBuilding.value = item;
    buildingForm.name = item.name;
    buildingForm.code = item.code ?? '';
    buildingForm.description = item.description ?? '';
    showBuildingModal.value = true;
}
function submitBuilding() {
    const options = { onSuccess: () => (showBuildingModal.value = false) };
    if (editingBuilding.value) {
        buildingForm.put(route('admin.buildings.update', editingBuilding.value.id), options);
    } else {
        buildingForm.post(route('admin.buildings.store', buildingPropertyId.value!), options);
    }
}
function destroyBuilding(item: BuildingItem) {
    if (confirm(`Hapus gedung "${item.name}"?`)) {
        router.delete(route('admin.buildings.destroy', item.id));
    }
}

/* Floor modal */
const showFloorModal = ref(false);
const editingFloor = ref<FloorItem | null>(null);
const floorBuildingId = ref<number | null>(null);
const floorForm = useForm({ name: '', level: 0, description: '' });

function openCreateFloor(buildingId: number) {
    editingFloor.value = null;
    floorBuildingId.value = buildingId;
    floorForm.reset();
    showFloorModal.value = true;
}
function openEditFloor(item: FloorItem) {
    editingFloor.value = item;
    floorForm.name = item.name;
    floorForm.level = item.level;
    floorForm.description = item.description ?? '';
    showFloorModal.value = true;
}
function submitFloor() {
    const options = { onSuccess: () => (showFloorModal.value = false) };
    if (editingFloor.value) {
        floorForm.put(route('admin.floors.update', editingFloor.value.id), options);
    } else {
        floorForm.post(route('admin.floors.store', floorBuildingId.value!), options);
    }
}
function destroyFloor(item: FloorItem) {
    if (confirm(`Hapus lantai "${item.name}"?`)) {
        router.delete(route('admin.floors.destroy', item.id));
    }
}
</script>

<template>
    <Head title="Struktur Properti" />

    <AdminLayout>
        <div class="flex items-center justify-between">
            <h1 class="font-display text-2xl font-semibold text-charcoal-800">Struktur Properti</h1>
            <PrimaryButton @click="openCreateProperty">+ Tambah Properti</PrimaryButton>
        </div>

        <div class="mt-6 space-y-4">
            <div v-for="property in properties" :key="property.id" class="rounded-xl border border-beige-200 bg-white shadow-soft">
                <div class="flex items-start justify-between gap-4 p-5">
                    <div class="flex-1 cursor-pointer" @click="toggle(property.id)">
                        <div class="flex items-center gap-2">
                            <h2 class="font-display text-lg font-semibold text-charcoal-800">{{ property.name }}</h2>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="property.is_active ? 'bg-green-50 text-green-700' : 'bg-charcoal-100 text-charcoal-500'"
                            >
                                {{ property.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-charcoal-500">{{ property.address }}, {{ property.city }}, {{ property.province }}</p>
                        <p class="mt-1 text-xs text-charcoal-400">{{ property.buildings.length }} gedung</p>
                    </div>
                    <div class="flex shrink-0 gap-3">
                        <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEditProperty(property)">Edit</button>
                        <button class="text-xs font-medium text-red-600 hover:underline" @click="destroyProperty(property)">Hapus</button>
                    </div>
                </div>

                <div v-if="expanded[property.id]" class="border-t border-beige-100 p-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-charcoal-700">Gedung</h3>
                        <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openCreateBuilding(property.id)">+ Tambah Gedung</button>
                    </div>

                    <div class="mt-3 space-y-3">
                        <div v-for="building in property.buildings" :key="building.id" class="rounded-lg bg-cream-50 p-4">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-charcoal-800">
                                    {{ building.name }} <span v-if="building.code" class="text-charcoal-400">({{ building.code }})</span>
                                </p>
                                <div class="flex gap-3">
                                    <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEditBuilding(building)">Edit</button>
                                    <button class="text-xs font-medium text-red-600 hover:underline" @click="destroyBuilding(building)">Hapus</button>
                                </div>
                            </div>

                            <div class="mt-2 flex items-center justify-between">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-charcoal-500">Lantai</h4>
                                <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openCreateFloor(building.id)">+ Tambah Lantai</button>
                            </div>
                            <ul class="mt-2 space-y-1">
                                <li v-for="floor in building.floors" :key="floor.id" class="flex items-center justify-between rounded bg-white px-3 py-2 text-sm">
                                    <span>{{ floor.name }} (level {{ floor.level }})</span>
                                    <span class="flex gap-3">
                                        <button class="text-xs font-medium text-terracotta-600 hover:underline" @click="openEditFloor(floor)">Edit</button>
                                        <button class="text-xs font-medium text-red-600 hover:underline" @click="destroyFloor(floor)">Hapus</button>
                                    </span>
                                </li>
                                <li v-if="building.floors.length === 0" class="text-xs text-charcoal-400">Belum ada lantai.</li>
                            </ul>
                        </div>
                        <p v-if="property.buildings.length === 0" class="text-xs text-charcoal-400">Belum ada gedung.</p>
                    </div>
                </div>
            </div>

            <p v-if="properties.length === 0" class="text-sm text-charcoal-400">Belum ada properti.</p>
        </div>

        <!-- Property Modal -->
        <Modal :show="showPropertyModal" @close="showPropertyModal = false">
            <form class="max-h-[80vh] overflow-y-auto p-6" @submit.prevent="submitProperty">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editingProperty ? 'Edit Properti' : 'Tambah Properti' }}
                </h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Nama Properti" />
                        <TextInput v-model="propertyForm.name" class="mt-1 block w-full" required />
                        <InputError :message="propertyForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Alamat" />
                        <textarea v-model="propertyForm.address" rows="2" required class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                        <InputError :message="propertyForm.errors.address" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Kota" />
                            <TextInput v-model="propertyForm.city" class="mt-1 block w-full" required />
                            <InputError :message="propertyForm.errors.city" />
                        </div>
                        <div>
                            <InputLabel value="Provinsi" />
                            <TextInput v-model="propertyForm.province" class="mt-1 block w-full" required />
                            <InputError :message="propertyForm.errors.province" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel value="Kode Pos" />
                            <TextInput v-model="propertyForm.postal_code" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Latitude" />
                            <TextInput v-model="propertyForm.latitude" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Longitude" />
                            <TextInput v-model="propertyForm.longitude" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Telepon" />
                            <TextInput v-model="propertyForm.contact_phone" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="WhatsApp" />
                            <TextInput v-model="propertyForm.contact_whatsapp" class="mt-1 block w-full" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Deskripsi" />
                        <textarea v-model="propertyForm.description" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                    <div>
                        <InputLabel value="Peraturan Kost" />
                        <textarea v-model="propertyForm.house_rules" rows="3" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                    <label class="flex items-center gap-2">
                        <Checkbox v-model:checked="propertyForm.is_active" />
                        <span class="text-sm text-charcoal-600">Aktif (tampil di katalog publik)</span>
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showPropertyModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="propertyForm.processing">Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Building Modal -->
        <Modal :show="showBuildingModal" @close="showBuildingModal = false" max-width="md">
            <form class="p-6" @submit.prevent="submitBuilding">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editingBuilding ? 'Edit Gedung' : 'Tambah Gedung' }}
                </h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Nama Gedung" />
                        <TextInput v-model="buildingForm.name" class="mt-1 block w-full" required />
                        <InputError :message="buildingForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Kode (opsional)" />
                        <TextInput v-model="buildingForm.code" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Deskripsi" />
                        <textarea v-model="buildingForm.description" rows="2" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showBuildingModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="buildingForm.processing">Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Floor Modal -->
        <Modal :show="showFloorModal" @close="showFloorModal = false" max-width="md">
            <form class="p-6" @submit.prevent="submitFloor">
                <h2 class="font-display text-lg font-semibold text-charcoal-800">
                    {{ editingFloor ? 'Edit Lantai' : 'Tambah Lantai' }}
                </h2>
                <div class="mt-4 grid gap-4">
                    <div>
                        <InputLabel value="Nama Lantai" />
                        <TextInput v-model="floorForm.name" class="mt-1 block w-full" required />
                        <InputError :message="floorForm.errors.name" />
                    </div>
                    <div>
                        <InputLabel value="Level (angka urut)" />
                        <TextInput v-model.number="floorForm.level" type="number" class="mt-1 block w-full" required />
                        <InputError :message="floorForm.errors.level" />
                    </div>
                    <div>
                        <InputLabel value="Deskripsi" />
                        <textarea v-model="floorForm.description" rows="2" class="mt-1 block w-full rounded-lg border-beige-300 focus:border-terracotta-400 focus:ring-terracotta-400" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton type="button" @click="showFloorModal = false">Batal</SecondaryButton>
                    <PrimaryButton :disabled="floorForm.processing">Simpan</PrimaryButton>
                </div>
            </form>
        </Modal>
    </AdminLayout>
</template>
