<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Divider from 'primevue/divider';
import Fieldset from 'primevue/fieldset';

import { ref, watch } from 'vue';
import { useGlobalToast } from '@/Utils/toast';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import 'leaflet-routing-machine/dist/leaflet-routing-machine.css';
import 'leaflet-routing-machine';
import axios from 'axios';

const { success, error } = useGlobalToast();

const { props } = usePage();
const warehouses = props.warehouses ?? [];
const drivers = props.drivers ?? [];
const vehicles = props.vehicles ?? [];

const map = ref(null);
let mapInitialized = false;
let warehouseMarker = null;
const customerMarker = ref(null);
let routingControl = null;

const isLocating = ref(false);

const form = useForm({
    driver_id: null,
    dispatcher_id: props.auth.user.id,
    vehicle_id: null,
    warehouse_id: null,
    planned_start_time: null,
    status: '',
    route: '',
    route_distance_km: null,
    is_liquidation_required: false,
    is_pre_trip_checked: false,
    customer_name: '',
    customer_address: '',
    latitude: null,
    longitude: null,
});

const formatDateTime = (dateObj) => {
    if (!dateObj) return null;

    const dt = new Date(dateObj);
    if (isNaN(dt)) return null; 

    const yyyy = dt.getFullYear();
    const mm = String(dt.getMonth() + 1).padStart(2, '0');
    const dd = String(dt.getDate()).padStart(2, '0');
    const hh = String(dt.getHours()).padStart(2, '0');
    const min = String(dt.getMinutes()).padStart(2, '0');

    return `${yyyy}-${mm}-${dd} ${hh}:${min}`;
};


const driverOptions = drivers.map(d => ({ label: d.user.name, value: d.id }));
const vehicleOptions = vehicles.map(v => ({ label: v.license_plate, value: v.id }));
const warehouseOptions = warehouses.map(w => ({ label: w.name, value: w.id }));

const initializeMap = (lat, lon) => {
    if (!mapInitialized) {
        map.value = L.map('map').setView([lat, lon], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map.value);

        mapInitialized = true;
    }
};

const locateCustomerOnMap = async () => {
    if (!form.customer_address) {
        error("Please enter a customer address first.");
        return;
    }

    if (!warehouseMarker) {
        error("Select a warehouse before locating the customer.");
        return;
    }

    isLocating.value = true;

    try {
        const response = await axios.get(`https://photon.komoot.io/api/?q=${encodeURIComponent(form.customer_address)}&limit=1`);
        const data = response.data;

        if (!data.features?.length) {
            error("Customer address not found on map.");
            return;
        }

        const [lon, lat] = data.features[0].geometry.coordinates;
        form.latitude = lat;
        form.longitude = lon;

        initializeMap(lat, lon);

        if (!customerMarker.value) {
            customerMarker.value = L.marker([lat, lon], { draggable: true })
                .addTo(map.value)
                .bindPopup("Customer Location")
                .openPopup();

            customerMarker.value.on("dragend", () => {
                const pos = customerMarker.value.getLatLng();
                form.latitude = pos.lat;
                form.longitude = pos.lng;
                updateRoute();
            });
        } else {
            customerMarker.value.setLatLng([lat, lon]).openPopup();
        }

        updateRoute();

        if (warehouseMarker) {
            const bounds = L.latLngBounds([
                warehouseMarker.getLatLng(),
                customerMarker.value.getLatLng()
            ]);
            map.value.fitBounds(bounds, { padding: [50, 50] });
        } else {
            map.value.setView([lat, lon], 15);
        }

        console.log(customerMarker.value);
        console.log(warehouseMarker);


        success("Customer located on map!");
    } catch (err) {
        console.error(err);
        error("Failed to locate customer on map");
    } finally {
        isLocating.value = false;
    }
};

const updateRoute = () => {
    if (!map.value || !warehouseMarker || !customerMarker.value) return;

    const waypoints = [
        warehouseMarker.getLatLng(),
        customerMarker.value.getLatLng()
    ];

    if (!routingControl) {
        routingControl = L.Routing.control({
            waypoints: waypoints,
            routeWhileDragging: false,
            draggableWaypoints: true,
            show: false
        }).addTo(map.value);

        routingControl.on('routesfound', function (e) {
        const route = e.routes[0];

        form.route_distance_km = (route.summary.totalDistance / 1000).toFixed(2);
        form.route = JSON.stringify(route.coordinates);
        
        console.log(`Distance: ${form.route_distance_km}`);
        console.log(`Route: ${form.route}`);
    });
    } else {
        routingControl.setWaypoints(waypoints);
    }


    console.log(`Waypoints: ${waypoints}`);
};


watch(() => form.warehouse_id, (warehouseId) => {
    if (!warehouseId) return;

    const selected = warehouses.find(w => w.id === warehouseId);
    if (!selected || !selected.latitude || !selected.longitude) return;

    initializeMap(selected.latitude, selected.longitude);

    if (!warehouseMarker) {
        warehouseMarker = L.marker([selected.latitude, selected.longitude])
            .addTo(map.value)
            .bindPopup(selected.name)
            .openPopup();
    } else {
        warehouseMarker.setLatLng([selected.latitude, selected.longitude])
            .setPopupContent(selected.name);
    }

    updateRoute();
});

const submitForm = () => {
    form
        .transform(data => ({
            ...data,
            planned_start_time: formatDateTime(data.planned_start_time), 
        }))
        .post('/trips', {
            preserveScroll: true,
            onSuccess: () => success('Trip created successfully'),
            onError: (errors) => {
                console.error('Create trip error: ', errors);
                error('Failed to create trip');
            }
        });
};
</script>

<template>
    <Head title="Create Trip" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Create Trip
            </h2>
        </template>

        <div class="py-12">
            <div class="bg-bgcontentcolor-light dark:bg-bgcontentcolor-dark rounded mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="p-6 shadow sm:rounded-lg">
                    <h1 class="text-2xl font-bold mb-6">
                        Trip Information
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <Fieldset legend="Trip Details" class="p-4">
                            <div class="space-y-6">

                                <div>
                                    <InputLabel value="Driver" />
                                    <Select
                                        v-model="form.driver_id"
                                        :options="driverOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select Driver"
                                        class="w-full mt-1"
                                    />
                                    <InputError :message="form.errors.driver_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Vehicle" />
                                    <Select
                                        v-model="form.vehicle_id"
                                        :options="vehicleOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select Vehicle"
                                        class="w-full mt-1"
                                    />
                                    <InputError :message="form.errors.vehicle_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Warehouse" />
                                    <Select
                                        v-model="form.warehouse_id"
                                        :options="warehouseOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        placeholder="Select Warehouse"
                                        class="w-full mt-1"
                                    />
                                    <InputError :message="form.errors.warehouse_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Planned Start Time" />
                                    <DatePicker
                                        v-model="form.planned_start_time"
                                        showTime
                                        showIcon
                                        :minDate="new Date()"
                                        placeholder="Select Date & Time"
                                        class="w-full mt-1"
                                    />
                                    <InputError :message="form.errors.planned_start_time" class="mt-1" />
                                </div>

                                <div class="flex flex-col gap-3 mt-2">

                                    <div class="flex items-center gap-2">
                                        <Checkbox 
                                            v-model="form.is_liquidation_required" 
                                            inputId="liquidation"
                                            binary
                                        />
                                        <InputLabel for="liquidation" value="Requires Liquidation"/>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Checkbox 
                                            v-model="form.is_pre_trip_checked" 
                                            inputId="pretrip" 
                                            binary
                                        />
                                        <InputLabel for="pretrip" value="Pre-Trip Checked"/>
                                    </div>
                                </div>

                            </div>
                        </Fieldset>

                        <Fieldset legend="Customer Information" class="p-4">
                            <div class="space-y-6">

                                <div>
                                    <InputLabel value="Customer Name" />
                                    <InputText
                                        v-model="form.customer_name"
                                        placeholder="Enter Name"
                                        class="w-full mt-1"
                                    />
                                    <InputError :message="form.errors.customer_name" class="mt-1" />
                                </div>

                                <div>
                                    <div>
                                        <InputLabel value="Customer Address" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <InputText
                                            v-model="form.customer_address"
                                            placeholder="Enter Address"
                                            class="w-full mt-1"
                                        />
                                        <Button
                                            label="Locate"
                                            icon="pi pi-map-marker"
                                            class="mt-1"
                                            :loading="isLocating"
                                            :disabled="!form.customer_address"
                                            @click="locateCustomerOnMap"
                                            severity="primary"
                                        />
                                    </div>
                                    <InputError :message="form.errors.customer_address" class="mt-1" />
                                </div>


                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Distance (km)" />
                                        <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg mt-1 text-sm">
                                            {{ form.route_distance_km ?? '—' }}
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </Fieldset>

                    </div>

                    <Divider class="my-8" />

                    <div class="p-0 overflow-hidden">
                        <div class="p-4">
                            <InputLabel value="Route Map" />
                        </div>

                        <div id="map" class="w-full h-96"></div>
                    </div>
                    <InputError :message="form.errors.latitude" class="mt-1" />
                    <InputError :message="form.errors.longitude" class="mt-1" />

                    <div class="flex justify-end mt-8">
                        <Button label="Create Trip" @click="submitForm" />
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

