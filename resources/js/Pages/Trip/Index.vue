<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, nextTick } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import DatePicker from 'primevue/datepicker';
import Tag from 'primevue/tag'; 
import Dialog from 'primevue/dialog';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css'; 
import 'leaflet-routing-machine';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();
const trips = ref(props.trips ?? []);
const drivers = ref(props.drivers ?? []);
const vehicles = ref(props.vehicles ?? []);
const dispatcher = ref(props.dispatcher ?? []);
const warehouses = ref(props.warehouse ?? []);

// Filters
const selectedVehicles = ref([]);
const selectedDrivers = ref([]);
const selectedTrip = ref(null);
const selectedWarehouse = ref(null);

const mapInstance = ref(null);
const dateRange = ref(null);
const openMap = ref(false);

const openMapsDialog = (trip) => {

    selectedTrip.value = trip;

    console.log(selectedTrip.value);

    openMap.value = true;
}

const getWarehouseById = (id) => {
    return warehouses.value.find(w => w.id === id) || null;
}

const getWarehouseCoords = (warehouseId) => {
    const warehouse = warehouses.value.find(w => w.id === warehouseId);
    
    if (warehouse) {
        return { lat: warehouse.latitude, lng: warehouse.longitude };
    }
    
    return { lat: 14.58, lng: 121.05 }; 
}

const vehicleOptions = ref(
    vehicles.value.map(v => ({ id: v.id, plate: v.license_plate }))
);
const driverOptions = ref(
    drivers.value.map(d => ({ id: d.id, name: d.user.name }))
);

// Helper function to determine Tag severity based on status
const getStatusSeverity = (status) => {
    switch (status) {
        case 'pending':
        case 'scheduled':
            return 'info'; // Blue/Info for scheduled or pending
        case 'in_progress':
        case 'en_route':
            return 'warning'; // Yellow/Warning for active trips
        case 'completed':
            return 'success'; // Green for completion
        case 'cancelled':
        case 'failed':
            return 'danger'; // Red for failed/cancelled
        default:
            return 'secondary';
    }
};

let routingControl = null;

const initializeMap = () => {
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }

    if (!selectedTrip.value) return;

    const warehouseCoords = getWarehouseCoords(selectedTrip.value.warehouse_id);
    const customerCoords = {
        lat: selectedTrip.value.latitude,
        lng: selectedTrip.value.longitude
    };

    const map = L.map('trip-map-container', { zoomControl: false }).setView(
        [customerCoords.lat, customerCoords.lng],
        12
    );
    mapInstance.value = map;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    L.Routing.control({
        waypoints: [
        L.latLng(warehouseCoords.lat, warehouseCoords.lng),
        L.latLng(customerCoords.lat, customerCoords.lng)
        ],
        routeWhileDragging: false,
        show: false,
        fitSelectedRoutes: true,
        lineOptions: {
        styles: [{ color: '#3B82F6', opacity: 0.5, weight: 6 }]
        }
    }).addTo(map);

    L.marker([warehouseCoords.lat, warehouseCoords.lng])
        .addTo(map)
        .bindPopup(`<p>Warehouse: <b>${selectedTrip.value.warehouse?.name } </b></p>`)
        .openPopup();

    L.marker([customerCoords.lat, customerCoords.lng])
        .addTo(map)
        .bindPopup(`<p>Destination: <b>${selectedTrip.value.customer_name}</b></p>`)
        .openPopup();
};


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
            <h2 class="text-xl font-semibold text-white">Trip Management</h2>
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
                        <template #body="{ data }">{{ data.dispatcher?.name ?? 'Unknown' }}</template>
                    </Column>
                    
                    <Column field="planned_start_time" header="Planned Start" sortable />
                    <Column field="actual_start_time" header="Actual Start" sortable />
                    
                    <Column header="Distance" field="route_distance_km" sortable>
                         <template #body="{ data }">{{ data.route_distance_km ? `${data.route_distance_km} KM` : '-' }}</template>
                    </Column>
                    <Column field="customer_name" header="Customer" sortable />
                    
                    <Column header="Pre-Trip">
                        <template #body="{ data }">
                            <Tag 
                                :value="data.is_pre_trip_checked ? 'CHECKED' : 'PENDING'" 
                                :severity="data.is_pre_trip_checked ? 'success' : 'info'" 
                            />
                        </template>
                    </Column>
                    <Column header="Liquidation">
                        <template #body="{ data }">
                            <Tag 
                                :value="data.is_liquidation_required ? 'REQUIRED' : 'NONE'" 
                                :severity="data.is_liquidation_required ? 'warning' : 'secondary'" 
                            />
                        </template>
                    </Column>
                    
                    <Column header="Status" field="status" sortable>
                        <template #body="{ data }">
                            <Tag 
                                :value="data.status.replace('_', ' ').toUpperCase()"
                                :severity="getStatusSeverity(data.status)"
                            />
                        </template>
                    </Column>
                    
                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('trips.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="warning" rounded />
                                </Link>
                                <Button 
                                    icon="pi pi-map-marker"
                                    severity="help"
                                    rounded
                                    @click="openMapsDialog(data)"
                                />
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

                <Dialog 
                    v-model:visible="openMap"
                    :header="`Trip Route: ${selectedTrip?.vehicle?.license_plate ?? 'Loading...'}`" 
                    :style="{ width: '80vw' }"
                    @show="initializeMap"
                >
                    <div class="h-[60vh] w-full" id="trip-map-container">
                        <p v-if="!selectedTrip">Select a trip to view the route.</p>
                    </div>
                    <template #footer>
                        <Button label="Close" icon="pi pi-times" @click="openMap = false" text />
                    </template>
                </Dialog>
            </div>
        </div>
    </AuthenticatedLayout>
</template>