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

import { useGlobalToast } from '@/Utils/toast';

const { success, error, info } = useGlobalToast();
const { props } = usePage();

const vehicles = ref(props.vehicles);
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

                    <Column header="Docs">
                        <template #body="{ data }">
                            {{ data.compliance_docs_count ?? 0 }}
                        </template>
                    </Column>

                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('vehicles.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="info" rounded />
                                </Link>

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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
