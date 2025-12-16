<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import DatePicker from 'primevue/datepicker';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();
const trips = ref(props.trips ?? []);
const drivers = ref(props.drivers ?? []);
const vehicles = ref(props.vehicles ?? []);
const dipatchers = ref(props.dispatcher ?? []);

// Filters
const selectedVehicles = ref([]);
const selectedDrivers = ref([]);
const dateRange = ref(null);

const vehicleOptions = ref(
    vehicles.value.map(v => ({ id: v.id, plate: v.license_plate }))
);
const driverOptions = ref(
    drivers.value.map(d => ({ id: d.id, name: d.user.name }))
);

// Filtered trips
const filteredTrips = computed(() => {
    let list = trips.value;

    if (selectedVehicles.value.length) {
        list = list.filter(t => selectedVehicles.value.includes(t.vehicle_id));
    }

    if (selectedDrivers.value.length) {
        list = list.filter(t => selectedDrivers.value.includes(t.driver_id));
    }

    if (dateRange.value && dateRange.value.length === 2) {
        const [start, end] = dateRange.value.map(d => new Date(d));
        list = list.filter(t => {
            const tripDate = new Date(t.planned_start_time);
            return tripDate >= start && tripDate <= end;
        });
    }

    return list;
});

// Delete trip
const deleteTrip = (id) => {
    if (!confirm('Are you sure you want to delete this trip?')) return;

    router.delete(`/trips/${id}`, {
        onSuccess: () => {
            trips.value = trips.value.filter(t => t.id !== id);
            success('Trip deleted successfully');
        },
        onError: () => {
            error('Failed to delete trip');
        }
    });
};
</script>

<template>
    <Head title="Trips" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Trips</h2>
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
                            placeholder="Filter by Vehicle"
                            class="w-full md:w-80 mr-4"
                        />
                        <MultiSelect
                            v-model="selectedDrivers"
                            :options="driverOptions"
                            optionLabel="name"
                            optionValue="id"
                            filter
                            placeholder="Filter by Driver"
                            class="w-full md:w-80 mr-4"
                        />
                        <DatePicker
                            v-model="dateRange"
                            selectionMode="range"
                            placeholder="Filter by Date Range"
                            showIcon
                            class="w-60"
                        />
                    </template>

                    <template #end>
                        <Link :href="route('trips.create')">
                            <Button label="Add Trip" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <!-- Trips Table -->
                <DataTable
                    :value="filteredTrips"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >
                    <Column header="Driver">
                        <template #body="{ data }">{{ data.driver?.user?.name ?? 'Unknown' }}</template>
                    </Column>
                    <Column header="Vehicle">
                        <template #body="{ data }">{{ data.vehicle?.license_plate ?? 'Unknown' }}</template>
                    </Column>
                    <Column header="Dispatcher">
                        <template #body="{ data }">{{ data.dispatcher.name ?? 'Unknown' }}</template>
                    </Column>
                    <Column field="planned_start_time" header="Planned Start" sortable />
                    <Column field="actual_start_time" header="Actual Start" sortable />
                    <Column header="Status">
                        <template #body="{ data }">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold"
                                  :class="{
                                    'bg-yellow-400 text-black': data.status === 'pending',
                                    'bg-blue-500 text-white': data.status === 'in_progress',
                                    'bg-green-500 text-white': data.status === 'completed'
                                  }">
                                {{ data.status.replace('_', ' ').toUpperCase() }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('trips.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="warning" rounded />
                                </Link>
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    rounded
                                    @click="deleteTrip(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
