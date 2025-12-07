<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import Dialog from 'primevue/dialog';
import Image from 'primevue/image';

import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();
const { props } = usePage();

const drivers = ref(props.drivers);

const vehicleOptions = ref(
    props.vehicles?.map(v => ({
        id: v.id,
        plate: v.plate_number,
    })) ?? []
);

const selectedVehicles = ref([]);

const showDocsDialog = ref(false);
const selectedDriver = ref(null);
const selectedDocs = ref([]);

const openDocsDialog = (driver) => {
    selectedDriver.value = driver;    
    selectedDocs.value = driver.compliance_docs
    ?.flatMap(doc => doc.attachments?.flatMap(inner => inner || []) ?? [])
    ?? [];

    showDocsDialog.value = true;
};

const filteredDrivers = computed(() => {
    if (selectedVehicles.value.length === 0) return drivers.value;

    return drivers.value.filter(driver =>
        driver.vehicle_history.some(v => selectedVehicles.value.includes(v.vehicle_id))
    );
});

const deleteDriver = (id) => {
    if (!confirm('Are you sure you want to delete this driver?')) return;

    router.delete(`/drivers/${id}`, {
        onSuccess: () => {
            drivers.value = drivers.value.filter(d => d.id !== id);
            success('Driver deleted successfully');
        },
        onError: () => {
            error('Failed to delete driver');
        }
    });
};
</script>

<template>
    <Head title="Drivers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Drivers</h2>
        </template>

        <div class="py-4 px-6">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

                <Toolbar class="mb-4 bg-navcolor-light dark:bg-navcolor-dark">
                    <template #start>
                        <MultiSelect
                            v-model="selectedVehicles"
                            :options="vehicleOptions"
                            optionLabel="plate"
                            optionValue="id"
                            filter
                            filterBy="plate"
                            placeholder="Filter by Vehicle"
                            :maxSelectedLabels="3"
                            class="w-full md:w-80"
                        />
                    </template>

                    <template #end>
                        <Link :href="route('drivers.create')">
                            <Button label="Add Driver" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <DataTable
                    :value="filteredDrivers"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >

                    <Column header="Avatar" class="w-20 text-center">
                        <template #body="{ data }">
                            <img
                                :src="data.user.profile_picture?.url 
                                    ? `/storage/${data.user.profile_picture.url}` 
                                    : '/images/default-avatar.png'"
                                class="w-12 h-12 rounded-full object-cover mx-auto"
                            />
                        </template>
                    </Column>

                    <Column header="Driver Name">
                        <template #body="{ data }">
                            {{ data.user.name }}
                        </template>
                    </Column>

                    <Column header="Email">
                        <template #body="{ data }">
                            {{ data.user.email }}
                        </template>
                    </Column>

                    <Column header="License">
                        <template #body="{ data }">
                            <div class="text-sm">
                                <div><b>No:</b> {{ data.license_number }}</div>
                                <div>
                                    <b>Restriction: </b>
                                    <span v-if="data.license_restrictions && data.license_restrictions.length">
                                        {{ data.license_restrictions.map(r => r.code).join(', ') }}
                                    </span>
                                    <span v-else>
                                        None
                                    </span>
                                </div>
                                <div><b>Expires:</b> {{ data.license_expiration }}</div>
                            </div>
                        </template>
                    </Column>

                    <!-- Vehicle History -->
                    <Column header="Vehicle History">
                        <template #body="{ data }">
                            <span
                                v-for="vehicle in data.vehicle_history"
                                :key="vehicle.id"
                                class="inline-block bg-blue-600 text-white text-xs px-2 py-1 rounded mr-2"
                            >
                                {{ vehicle.vehicle?.plate_number ?? 'Unknown' }}
                            </span>
                        </template>
                    </Column>

                    <!-- Action buttons -->
                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('drivers.edit', data.id)">
                                    <Button icon="pi pi-user-edit" severity="info" rounded />
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
                                    @click="deleteDriver(data.id)"
                                />
                            </div>
                        </template>
                    </Column>

                </DataTable>
                <Dialog
                    v-model:visible="showDocsDialog"
                    :header="`Documents for ${selectedDriver?.user?.name ?? ''}`"
                    :modal="true"
                    :style="{ width: '60vw' }"
                    class="dark:bg-zinc-900"
                >
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                        <!-- No Docs Case -->
                        <div 
                            v-if="selectedDocs.length === 0"
                            class="text-center text-gray-500 dark:text-gray-300 col-span-full"
                        >
                            No attached documents.
                        </div>

                        <!-- Document Cards -->
                        <div
                            v-for="doc in selectedDocs"
                            :key="doc.id"
                            class="rounded-xl shadow-md overflow-hidden bg-white dark:bg-zinc-800 
                                border border-gray-200 dark:border-zinc-700 flex flex-col"
                        >
                            <!-- Header Badge -->
                            <div class="px-4 py-2 text-blue-700 dark:text-blue-200 font-semibold text-sm uppercase">
                                {{ doc.type ?? 'Document' }}
                            </div>

                            <!-- Image Preview -->
                            <div class="w-full h-56 bg-gray-100 dark:bg-zinc-700 flex items-center justify-center">
                                <Image
                                    preview
                                    v-if="doc.url"
                                    :src="`/storage/${doc.url}`"
                                    alt="Document"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span v-else class="text-gray-500 dark:text-gray-300 text-sm">No Image</span>
                            </div>

                            <!-- File Info -->
                            <div class="p-4 flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-300 truncate">
                                    {{ doc.name ?? 'Unknown File' }}
                                </span>
                            </div>
                        </div>

                    </div>
                </Dialog>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
