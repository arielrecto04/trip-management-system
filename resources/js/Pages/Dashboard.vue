<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import "leaflet-routing-machine";
import "leaflet-routing-machine/dist/leaflet-routing-machine.css";
import L from "leaflet";
import { onMounted, ref } from "vue";
import Card from 'primevue/card';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Fieldset from 'primevue/fieldset';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import ProgressSpinner from 'primevue/progressspinner';
import Chart from 'primevue/chart';

const props = defineProps({
    warehouses: {
        type: Array,
        required: true,
    }
})

// --- Dummy Data Setup ---
const stats = ref([
    { title: 'Total Trips', value: 125, icon: 'pi pi-compass', color: 'text-blue-500' },
    { title: 'Active Drivers', value: 42, icon: 'pi pi-users', color: 'text-green-500' },
    { title: 'Vehicles in Maint.', value: 5, icon: 'pi pi-wrench', color: 'text-yellow-500' },
    { title: 'Pending Liquidations', value: 18, icon: 'pi pi-wallet', color: 'text-red-500' },
    { title: 'Avg Route Distance', value: '85.4 KM', icon: 'pi pi-road', color: 'text-purple-500' }, // New KPI
]);

const upcomingTrips = ref([
    { id: 'T901', driver: 'Juan Dela Cruz', vehicle: 'VAN-123', status: 'Scheduled', start: '09:00 AM', destination: 'Makati CBD' },
    { id: 'T902', driver: 'Maria Santos', vehicle: 'TRUCK-001', status: 'Assigned', start: '10:30 AM', destination: 'QC Warehouse' },
    { id: 'T903', driver: 'Pedro Reyes', vehicle: 'VAN-456', status: 'Scheduled', start: '11:00 AM', destination: 'Taguig City' },
]);

const maintenanceLogs = ref([
    { vehicle: 'VAN-123', technician: 'AutoFix Inc.', status: 'Completed', end_date: '2025-12-10' },
    { vehicle: 'TRUCK-001', technician: 'Fast Lube', status: 'In Progress', end_date: '2025-12-18' },
    { vehicle: 'SEDAN-999', technician: 'AutoFix Inc.', status: 'Pending', end_date: '2025-12-24' },
]);

// --- Chart Data ---

const vehicleStatusData = ref({
    labels: ['Available', 'In Trip', 'Maintenance'],
    datasets: [
        {
            data: [35, 15, 5],
            backgroundColor: ['#22C55E', '#3B82F6', '#F59E0B'], // Green, Blue, Yellow
            hoverBackgroundColor: ['#16A34A', '#2563EB', '#D97706']
        }
    ]
});

const tripStatusData = ref({
    labels: ['Completed', 'In Progress', 'Scheduled', 'Cancelled'],
    datasets: [
        {
            label: 'Trips by Volume', // Existing Volume Breakdown
            backgroundColor: '#3B82F6', // Blue
            data: [70, 15, 20, 5],
            yAxisID: 'y'
        },
        {
            label: 'Avg. Route Distance (KM)', // NEW: Layered metric on the bar chart
            backgroundColor: 'rgba(239, 68, 68, 0.6)', // Red for distance
            data: [120, 95, 80, 0], // Dummy average distance for each status
            type: 'line', // Displayed as a line overlay
            borderColor: '#EF4444',
            yAxisID: 'y1'
        }
    ]
});

const routePerformanceData = ref({
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [
        {
            label: 'Planned Distance (KM)',
            data: [400, 350, 450, 500, 420, 200, 100],
            fill: false,
            borderColor: '#10B981', // Emerald Green
            tension: 0.4
        },
        {
            label: 'Actual Distance (KM)', // If actual routes deviate from planned
            data: [410, 340, 460, 490, 400, 210, 100],
            fill: false,
            borderColor: '#F59E0B', // Yellow/Amber
            tension: 0.4
        }
    ]
});

const tripStatusChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#f9fafb'
            }
        }
    },
    scales: {
        x: {
            ticks: { color: '#9ca3af' },
            grid: { color: 'rgba(255,255,255,0.1)' }
        },
        y: { // Primary Y-axis for Volume (bars)
            type: 'linear',
            display: true,
            position: 'left',
            ticks: { color: '#3B82F6' },
            grid: { color: 'rgba(255,255,255,0.1)' }
        },
        y1: { // Secondary Y-axis for Distance (line)
            type: 'linear',
            display: true,
            position: 'right',
            grid: { drawOnChartArea: false }, // Only draw grid lines for the primary Y axis
            ticks: { color: '#EF4444' }
        }
    }
});

const lineChartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            labels: {
                color: '#f9fafb'
            }
        }
    },
    scales: {
        x: {
            ticks: { color: '#9ca3af' },
            grid: { color: 'rgba(255,255,255,0.1)' }
        },
        y: {
            ticks: { color: '#9ca3af' },
            grid: { color: 'rgba(255,255,255,0.1)' }
        }
    }
});


const chartOptions = ref({
    plugins: {
        legend: {
            labels: {
                color: '#f9fafb' // textcolor-dark
            }
        }
    },
    scales: {
        y: {
            ticks: {
                color: '#9ca3af' // gray-400
            },
            grid: {
                color: 'rgba(255,255,255,0.1)' // faint white grid lines
            }
        },
        x: {
            ticks: {
                color: '#9ca3af' // gray-400
            },
            grid: {
                color: 'rgba(255,255,255,0.1)'
            }
        }
    }
});

// --- Helper Functions ---

/**
 * Gets a PrimeVue Tag severity based on the status string.
 */
const getStatusSeverity = (status) => {
    switch (status) {
        case 'Scheduled':
            return 'info';
        case 'Assigned':
            return 'primary';
        case 'In Progress':
            return 'warning';
        case 'Completed':
            return 'success';
        case 'Pending':
            return 'secondary';
        case 'Cancelled':
            return 'danger';
        default:
            return null;
    }
};

onMounted(() => {
    const initialLat = props.warehouses.length > 0 ? props.warehouses[0].latitude : 14.5995;
    const initialLng = props.warehouses.length > 0 ? props.warehouses[0].longitude : 120.9842;

    const map = L.map('map').setView([initialLat, initialLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const warehouseDivIcon = L.divIcon({
        html: '<i class="pi pi-warehouse text-3xl text-blue-600 bg-white p-1 rounded-full shadow-lg border-2 border-blue-800"></i>',
        iconSize: [36, 36], 
        iconAnchor: [18, 36], 
        className: '' 
    });

    props.warehouses.forEach(warehouse => {
        L.marker([warehouse.latitude, warehouse.longitude], { icon: warehouseDivIcon })
            .addTo(map)
            .bindPopup(`<b>Warehouse: ${warehouse.name}</b><br>${warehouse.city_name || ''}`)
            .openPopup();
    })
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-textcolor-dark">
                Operations Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="space-y-6">

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
                        <Card 
                            v-for="stat in stats" 
                            :key="stat.title"
                            class="bg-bgcontentcolor-dark shadow-lg border-2 border-navcolor-dark"
                        >
                            <template #content>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="mb-2 text-sm font-medium text-gray-400">
                                            {{ stat.title }}
                                        </p>
                                        <div class="text-3xl font-bold text-textcolor-dark">
                                            {{ stat.value }}
                                        </div>
                                    </div>
                                    <i :class="[stat.icon, stat.color, 'text-4xl']"></i>
                                </div>
                            </template>
                        </Card>
                    </div>

                    <div class="grid grid-cols-1">
                        <Fieldset legend="Live Trip Tracking (Manila)"
                            class="bg-bgcontentcolor-dark shadow-lg border-2 border-navcolor-dark"
                            :pt="{
                                legend: { class: 'text-textcolor-dark' },
                                content: { class: 'p-0' }
                            }">
                            <div id="map" class="h-[450px] w-full rounded-b-lg"></div>
                        </Fieldset>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">

                        <Fieldset legend="Vehicle Status Distribution"
                            class="h-full bg-bgcontentcolor-dark shadow-lg border-2 border-navcolor-dark"
                            :pt="{ legend: { class: 'text-textcolor-dark' } }">
                            <div class="flex items-center justify-center h-full">
                                <Chart type="pie" :data="vehicleStatusData" :options="chartOptions"
                                    class="w-full" />
                            </div>
                        </Fieldset>

                        <Fieldset legend="Trip Status & Avg Distance"
                            class="h-full bg-bgcontentcolor-dark shadow-lg border-2 border-navcolor-dark"
                            :pt="{ legend: { class: 'text-textcolor-dark' } }">
                            <div class="flex items-center justify-center h-full p-4">
                                <Chart type="bar" :data="tripStatusData" :options="tripStatusChartOptions"
                                    class="w-full h-80" />
                            </div>
                        </Fieldset>

                        <Fieldset legend="Weekly Route Performance (KM)"
                            class="h-full bg-bgcontentcolor-dark shadow-lg border-2 border-navcolor-dark"
                            :pt="{ legend: { class: 'text-textcolor-dark' } }">
                            <div class="flex items-center justify-center h-full p-4">
                                <Chart type="line" :data="routePerformanceData" :options="lineChartOptions"
                                    class="w-full h-80" />
                            </div>
                        </Fieldset>
                    </div>

                    <TabView :pt="{
                        navContainer: { class: 'bg-navcolor-dark' },
                        nav: { class: 'bg-navcolor-dark' },
                        panelContainer: { class: 'p-0' }
                    }">
                        <TabPanel header="Upcoming Trips">
                            <Card class="bg-bgcontentcolor-dark shadow-lg">
                                <template #content>
                                    <DataTable :value="upcomingTrips" paginator :rows="5"
                                        :pt="{ table: { class: 'min-w-full' } }">
                                        <Column field="id" header="Trip ID"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="driver" header="Driver"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="vehicle" header="Vehicle"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="destination" header="Destination"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="start" header="Start Time"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column header="Status"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark">
                                            <template #body="slotProps">
                                                <Tag :severity="getStatusSeverity(slotProps.data.status)"
                                                    :value="slotProps.data.status" />
                                            </template>
                                        </Column>
                                    </DataTable>
                                </template>
                            </Card>
                        </TabPanel>

                        <TabPanel header="Active Maintenance Logs">
                            <Card class="bg-bgcontentcolor-dark shadow-lg">
                                <template #content>
                                    <DataTable :value="maintenanceLogs" paginator :rows="5"
                                        :pt="{ table: { class: 'min-w-full' } }">
                                        <Column field="vehicle" header="Vehicle"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="technician" header="Technician"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column field="end_date" header="Est. End Date"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark"></Column>
                                        <Column header="Status"
                                            class="bg-bgcontentcolor-dark text-textcolor-dark">
                                            <template #body="slotProps">
                                                <Tag :severity="getStatusSeverity(slotProps.data.status)"
                                                    :value="slotProps.data.status" />
                                            </template>
                                        </Column>
                                    </DataTable>
                                </template>
                            </Card>
                        </TabPanel>
                    </TabView>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Adjustments for the PrimeVue/Tailwind dark theme */
/* Overrides for text color in unselected tabs */
.p-tabview-nav-link {
    color: #9ca3af !important; /* Tailwind gray-400 */
}
/* Overrides for text color in selected tab */
.p-tabview-nav-link.p-highlight {
    color: #f9fafb !important; /* textcolor-dark */
}
/* Ensure the map container is rendered with no border */
#map {
    border-radius: 0 0 0.5rem 0.5rem; /* Match the Fieldset rounded corners */
}
</style>