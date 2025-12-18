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

const selectedVehicles = ref([]);
const selectedDrivers = ref([]);
const selectedTrip = ref(null);

const mapInstance = ref(null);
const dateRange = ref(null);
const openMap = ref(false);

// --- Options for Filters ---
const vehicleOptions = ref(vehicles.value.map(v => ({ id: v.id, plate: v.license_plate })));
const driverOptions = ref(drivers.value.map(d => ({ id: d.id, name: d.user.name })));

// --- Logic Helpers ---
const openMapsDialog = (trip) => {
    selectedTrip.value = trip;
    openMap.value = true;
};

const getStatusSeverity = (status) => {
    const severities = {
        'pending': 'info', 'scheduled': 'info',
        'in progress': 'warning', 'en_route': 'warning',
        'completed': 'success',
        'cancelled': 'danger', 'failed': 'danger'
    };
    return severities[status] || 'secondary';
};

// --- COORDINATE VALIDATION (Using Relationship) ---
const hasValidCoordinates = computed(() => {
    const trip = selectedTrip.value;
    if (!trip || !trip.warehouse) return false;

    // Check if both points exist
    const hasWarehouse = trip.warehouse.latitude != null && trip.warehouse.longitude != null;
    const hasCustomer = trip.latitude != null && trip.longitude != null;

    return hasWarehouse && hasCustomer;
});

const coordinateMessage = computed(() => {
    const trip = selectedTrip.value;
    if (!trip) return "No trip selected.";
    
    const hasWarehouse = trip.warehouse?.latitude != null && trip.warehouse?.longitude != null;
    const hasCustomer = trip.latitude != null && trip.longitude != null;

    if (!hasWarehouse && !hasCustomer) return "Missing both Warehouse and Customer coordinates.";
    if (!hasWarehouse) return "Warehouse coordinates are missing.";
    if (!hasCustomer) return "Customer destination coordinates are missing.";
    
    return "";
});

const initializeMap = () => {
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }

    if (!hasValidCoordinates.value) return;

    nextTick(() => {
        const trip = selectedTrip.value;
        
        const warehouseCoords = {
            lat: trip.warehouse.latitude,
            lng: trip.warehouse.longitude
        };
        const customerCoords = {
            lat: trip.latitude,
            lng: trip.longitude
        };

        const map = L.map('trip-map-container', { zoomControl: true }).setView(
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
                styles: [{ color: '#3B82F6', opacity: 0.6, weight: 6 }]
            }
        }).addTo(map);

        L.marker([warehouseCoords.lat, warehouseCoords.lng])
            .addTo(map)
            .bindPopup(`<b>Warehouse:</b> ${trip.warehouse.name}`);

        L.marker([customerCoords.lat, customerCoords.lng])
            .addTo(map)
            .bindPopup(`<b>Customer:</b> ${trip.customer_name}`);
    });
};

// --- DATA TABLE LOGIC ---
const filteredTrips = computed(() => {
    let list = trips.value;
    if (selectedVehicles.value.length) list = list.filter(t => selectedVehicles.value.includes(t.vehicle_id));
    if (selectedDrivers.value.length) list = list.filter(t => selectedDrivers.value.includes(t.driver_id));
    if (dateRange.value && dateRange.value.length === 2 && dateRange.value[1]) {
        const [start, end] = dateRange.value.map(d => new Date(d));
        list = list.filter(t => {
            const tripDate = new Date(t.planned_start_time);
            return tripDate >= start && tripDate <= end;
        });
    }
    return list;
});

const deleteTrip = (id) => {
    if (!confirm('Are you sure?')) return;
    router.delete(`/trips/${id}`, {
        onSuccess: () => {
            trips.value = trips.value.filter(t => t.id !== id);
            success('Trip deleted');
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
            <div class="mx-auto max-w-7xl px-4 py-6">
                <Toolbar class="mb-4 bg-navcolor-light dark:bg-navcolor-dark">
                    <template #start>
                        <MultiSelect v-model="selectedVehicles" :options="vehicleOptions" optionLabel="plate" optionValue="id" placeholder="Filter Vehicle" class="mr-4" />
                        <MultiSelect v-model="selectedDrivers" :options="driverOptions" optionLabel="name" optionValue="id" placeholder="Filter Driver" class="mr-4" />
                        <DatePicker v-model="dateRange" selectionMode="range" placeholder="Date Range" showIcon />
                    </template>
                    <template #end>
                        <Link :href="route('trips.create')">
                            <Button label="Add Trip" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <DataTable :value="filteredTrips" paginator :rows="10">
                    <Column header="Driver">
                        <template #body="{ data }">{{ data.driver?.user?.name ?? 'N/A' }}</template>
                    </Column>
                    <Column header="Dispatcher">
                        <template #body="{ data }">{{ data.dispatcher?.name ?? 'N/A' }}</template>
                    </Column>
                    <Column header="Vehicle">
                        <template #body="{ data }">{{ data.vehicle?.license_plate ?? 'N/A' }}</template>
                    </Column>
                    <Column header="Warehouse">
                        <template #body="{ data }">{{ data.warehouse?.name ?? 'N/A' }}</template>
                    </Column>
                    <Column field="planned_start_time" header="Planned Start" sortable />
                    <Column field="customer_name" header="Customer" sortable />
                    <Column header="Status">
                        <template #body="{ data }">
                            <Tag :value="data.status.toUpperCase()" :severity="getStatusSeverity(data.status)" />
                        </template>
                    </Column>
                    <Column header="Actions">
                        <template #body="{ data }">
                            <div class="flex gap-2">
                                <Link :href="route('trips.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="warning" rounded />
                                </Link>
                                <Button icon="pi pi-map-marker" severity="help" rounded @click="openMapsDialog(data)" />
                                <Button icon="pi pi-trash" severity="danger" rounded @click="deleteTrip(data.id)" />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <Dialog 
                    v-model:visible="openMap"
                    :header="`Trip Route: ${selectedTrip?.vehicle?.license_plate ?? ''}`" 
                    :style="{ width: '80vw' }"
                    @show="initializeMap"
                >
                    <div v-if="hasValidCoordinates" id="trip-map-container" class="h-[60vh] w-full"></div>
                    
                    <div v-else class="h-[60vh] w-full flex items-center justify-center bg-gray-50 dark:bg-gray-800 border-2 border-dashed rounded-lg">
                        <div class="text-center">
                            <i class="pi pi-map-marker text-red-500 text-5xl mb-4"></i>
                            <h3 class="text-xl font-semibold mb-2">Map Unavailable</h3>
                            <p class="text-gray-500">{{ coordinateMessage }}</p>
                        </div>
                    </div>

                    <template #footer>
                        <Button label="Close" icon="pi pi-times" @click="openMap = false" text />
                    </template>
                </Dialog>
            </div>
        </div>
    </AuthenticatedLayout>
</template>