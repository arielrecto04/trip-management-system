<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import ToggleSwitch from 'primevue/toggleswitch';
import Dialog from 'primevue/dialog';
import Image from 'primevue/image';
import FileUpload from 'primevue/fileupload';

import { useGlobalToast } from '@/Utils/toast';

const { success, error, info } = useGlobalToast();
const { props } = usePage();

const vehicles = ref(props.vehicles);

const showDocsDialog = ref(false);
const selectedVehicleDocs = ref([]);
const selectedVehicle = ref(null);
const selectedVehicleImages = ref([]);

const openDocsDialog = (vehicle) => {
    selectedVehicle.value = vehicle;
    selectedVehicleDocs.value = vehicle.compliance_docs ?? [];
    selectedVehicleImages.value = vehicle.attachments ?? [];
    showDocsDialog.value = true;


    console.log(selectedVehicleImages.value);
}


console.log(vehicles);

const activeFilter = ref([]);

const filteredVehicles = computed(() => {
    if (activeFilter.value.length === 0) return vehicles.value;

    return vehicles.value.filter(v => activeFilter.value.includes(v.is_active));
});

const toggleActive = (vehicle, newValue) => {
    const newState = newValue ? 1 : 0;

    axios.patch(`/vehicles/${vehicle.id}/toggle-active`, {
        is_active: newState,
    })
    .then(() => {
        vehicle.is_active = newState;
        info(`Vehicle ${vehicle.id} ${newState ? 'activated' : 'deactivated'}`);
    })
    .catch(() => {
        error('Failed to update vehicle');
    });   
};

const deleteVehicle = (id) => {
    if (!confirm('Delete this vehicle?')) return;

    router.delete(`/vehicles/${id}`, {
        onSuccess: () => {
            vehicles.value = vehicles.value.filter(v => v.id !== id);
            success('Vehicle deleted');
        },
        onError: () => error('Failed to delete vehicle')
    });
};

const activeStatusLabel = (state) => {
    return state ? "Active" : "Inactive";
};

const activeStatusClass = (state) => {
    return state
        ? "bg-green-500 text-white"
        : "bg-red-500 text-white";
};
</script>

<template>
    <Head title="Vehicle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Vehicles</h2>
        </template>

        <div class="py-4 px-6">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <!-- Toolbar -->
                <Toolbar class="mb-4">
                    <template #start>
                        <MultiSelect 
                            v-model="activeFilter"
                            :options="[
                                { label: 'Active', value: 1 },
                                { label: 'Inactive', value: 0 },
                            ]"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Filter by status"
                            class="w-full md:w-80"
                        />
                    </template>

                    <template #end>
                        <Link :href="route('vehicles.create')">
                            <Button label="Add Vehicle" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <DataTable 
                    :value="filteredVehicles"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >

                    <Column field="vin" header="VIN" sortable />

                    <Column header="Vehicle">
                        <template #body="{ data }">
                            <div class="font-semibold">{{ data.brand }} {{ data.model }}</div>
                        </template>
                    </Column>

                    <Column field="license_plate" header="Plate" sortable />

                    <Column header="Capacity">
                        <template #body="{ data }">
                            <div>{{ data.capacity_kg }} kg</div>
                            <div>{{ data.capacity_m3 }} m³</div>
                        </template>
                    </Column>

                    <Column header="Status">
                        <template #body="{ data }">
                            <ToggleSwitch
                                :modelValue="Boolean(data.is_active)"
                                @update:modelValue="(val) => toggleActive(data, val)"
                                class="w-32"
                            />
                        </template>
                    </Column>

                    <Column header="Current Trip">
                        <template #body="{ data }">
                            <span v-if="data.current_trip">
                                Trip #{{ data.current_trip.id }}
                            </span>
                            <span v-else class="text-gray-500">
                                None
                            </span>
                        </template>
                    </Column>

                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('vehicles.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="info" rounded />
                                </Link>

                                <Button 
                                    icon="pi pi-file"
                                    severity="help"
                                    rounded
                                    @click="openDocsDialog(data)"
                                />

                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    rounded 
                                    @click="deleteVehicle(data.id)"
                                />
                            </div>
                        </template>
                    </Column>

                </DataTable>
                <Dialog
                    v-model:visible="showDocsDialog"
                    :header="`Documents for Vehicle (${selectedVehicle?.vin ?? ''})`"
                    :modal="true"
                    :style="{ width: '60vw' }"
                    :closable="true"
                >
                    <div class="space-y-10 p-4">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Vehicle Images</h3>

                            <div 
                                v-if="selectedVehicleImages.length"
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6"
                            >
                                <div 
                                    v-for="img in selectedVehicleImages"
                                    :key="img.id"
                                    class="rounded-xl shadow-md overflow-hidden bg-white dark:bg-zinc-800 
                                        border border-gray-200 dark:border-zinc-700 flex flex-col"
                                >
                                    <div class="w-full h-48 bg-gray-100 dark:bg-zinc-700 flex items-center justify-center">
                                        <Image
                                            preview
                                            :src="`/storage/${img.url}`"
                                            alt="Vehicle Image"
                                            class="max-h-full max-w-full object-contain rounded-md"
                                        />
                                    </div>

                                    <div class="p-3 text-sm text-gray-600 dark:text-gray-300 truncate">
                                        {{ img.name ?? 'Image' }}
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-gray-500 text-sm">No images uploaded for this vehicle.</div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">Compliance Documents</h3>

                            <div 
                                v-if="selectedVehicleDocs.length"
                                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6"
                            >
                                <div 
                                    v-for="doc in selectedVehicleDocs" 
                                    :key="doc.id"
                                    class="rounded-xl shadow-md overflow-hidden bg-white dark:bg-zinc-800 
                                        border border-gray-200 dark:border-zinc-700 flex flex-col"
                                >
                                    <div class="px-4 py-2 text-blue-700 dark:text-blue-200 font-semibold text-sm">
                                        {{ doc.doc_type }}
                                    </div>

                                    <div class="w-full h-56 bg-gray-100 dark:bg-zinc-700 flex items-center justify-center">
                                        <Image
                                            preview
                                            v-if="doc.attachments?.length"
                                            :src="`/storage/${doc.attachments[0].url}`"
                                            alt="Document Image"
                                            class="max-h-full max-w-full object-contain rounded-md"
                                        />
                                        <span v-else class="text-gray-400 dark:text-gray-300 text-sm">
                                            No Image
                                        </span>
                                    </div>

                                    <div class="p-4">
                                        <span class="text-sm text-gray-600 dark:text-gray-300 truncate block">
                                            {{ doc.attachments?.[0]?.name ?? 'Unknown File' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="text-gray-500 text-sm">No compliance documents found.</div>
                        </div>

                    </div>
                </Dialog>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
