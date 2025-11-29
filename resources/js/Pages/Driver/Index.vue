<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';

import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();
const { props } = usePage();

const drivers = ref(props.drivers);

// Extract vehicle list options for filtering
const vehicleOptions = ref(
    props.vehicles?.map(v => ({
        id: v.id,
        plate: v.plate_number,
    })) ?? []
);

const selectedVehicles = ref([]);

// Filter by selected vehicle assigned in history
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

                <!-- Toolbar -->
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

                <!-- Table -->
                <DataTable
                    :value="filteredDrivers"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >

                    <!-- Avatar -->
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

                    <!-- Name -->
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

                    <!-- License -->
                    <Column header="License">
                        <template #body="{ data }">
                            <div class="text-sm">
                                <div><b>No:</b> {{ data.license_number }}</div>
                                <div><b>Restriction:</b> {{ data.license_restriction }}</div>
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
                                    icon="pi pi-trash"
                                    severity="danger"
                                    rounded
                                    @click="deleteDriver(data.id)"
                                />
                            </div>
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
