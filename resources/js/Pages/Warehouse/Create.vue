<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import FileUpload from 'primevue/fileupload';
import Image from 'primevue/image';
import Button from 'primevue/button';

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import { ref, onMounted, watch, computed, nextTick, onBeforeUnmount } from 'vue';
import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const previewImage = ref(null);
const mapRef = ref(null);
const mapInstance = ref(null);
const marker = ref(null);
const isLocating = ref(false);
const mapInitialized = ref(false);

const form = useForm({
    name: "",
    contact_person_name: "",
    house_number: "",
    street: "",
    region_code: "",
    region_name: "",
    province_code: "",
    province_name: "",
    city_code: "",
    city_name: "",
    barangay_code: "",
    barangay_name: "",
    zip_code: "",
    country: "Philippines",
    latitude: null,
    longitude: null,
});

const regions = ref([]);
const provinces = ref([]);
const cities = ref([]);
const barangays = ref([]);

const loadingRegions = ref(false);
const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);

const isNCR = computed(() => form.region_code === "130000000");

const fullAddress = computed(() => {
    const parts = [];
    
    if (form.house_number) parts.push(form.house_number);
    if (form.street) parts.push(form.street.trim());
    
    // Get names from the selected codes
    const barangay = barangays.value.find(b => b.code === form.barangay_code);
    if (barangay) parts.push(`Barangay ${barangay.name}`);
    
    const city = cities.value.find(c => c.code === form.city_code);
    if (city) parts.push(city.name);
    
    const province = provinces.value.find(p => p.code === form.province_code);
    if (province && !isNCR.value) parts.push(province.name);
    
    const region = regions.value.find(r => r.code === form.region_code);
    if (region && !region.name.startsWith("Region")) {
        parts.push(region.name);
    } 
    
    if(form.zip_code) parts.push(form.zip_code.toString());

    parts.push("Philippines");

    return parts.join(', ');
});

const canLocate = computed(() => {
    return form.city_code && form.barangay_code;
});

// FETCHERS
const fetchRegions = async () => {
    loadingRegions.value = true;
    regions.value = await fetch("https://psgc.gitlab.io/api/regions/").then(r => r.json());
    loadingRegions.value = false;
};

const fetchProvinces = async (regionCode) => {
    if (!regionCode) return;
    loadingProvinces.value = true;
    provinces.value = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`).then(r => r.json());
    loadingProvinces.value = false;
};

const fetchCities = async (provinceCode) => {
    if (!provinceCode) return;
    loadingCities.value = true;
    cities.value = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`).then(r => r.json());
    loadingCities.value = false;
};

const fetchNCRCities = async (regionCode) => {
    if (!regionCode) return;
    loadingCities.value = true;
    cities.value = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/cities-municipalities/`).then(r => r.json());
    loadingCities.value = false;
};

const fetchBarangays = async (cityCode) => {
    if (!cityCode) return;
    loadingBarangays.value = true;
    barangays.value = await fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`).then(r => r.json());
    loadingBarangays.value = false;
};

watch(() => form.region_code, (val) => {
    form.province_code = "";
    form.city_code = "";
    form.barangay_code = "";
    provinces.value = [];
    cities.value = [];
    barangays.value = [];

    const region = regions.value.find(r => r.code === val);
    form.region_name = region ? region.name : "";
    
    if (val) {
        if(val === "130000000") {
            fetchNCRCities(val);
        } else {
            fetchProvinces(val);
        }
    }
});

watch(() => form.province_code, (val) => {
    form.city_code = "";
    form.barangay_code = "";
    cities.value = [];
    barangays.value = [];

    const province = provinces.value.find(p => p.code === val);
    form.province_name = province ? province.name : "";

    if (val) fetchCities(val);
});

watch(() => form.city_code, (val) => {
    form.barangay_code = "";
    barangays.value = [];

    const city = cities.value.find(c => c.code === val);
    form.city_name = city ? city.name : "";

    if (val) fetchBarangays(val);
});

watch(() => form.barangay_code, (val) => {
    const barangay = barangays.value.find(b => b.code === val);
    form.barangay_name = barangay ? barangay.name : "";
});

const defaultIcon = L.icon({
    iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const initMap = () => {
    if (!mapRef.value || mapInstance.value) return;
    
    try {
        mapInstance.value = L.map(mapRef.value, {
            center: [12.8797, 121.7740],
            zoom: 6
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(mapInstance.value);



        mapInitialized.value = true;

        setTimeout(() => {
            if (mapInstance.value) {
                mapInstance.value.invalidateSize();
            }
        }, 100);

    } catch (err) {
        console.error('Error initializing map:', err);
    }
};

const locateAddress = async () => {
    if (!mapInstance.value) initMap();
    if (!canLocate.value) return;

    isLocating.value = true;

    try {
        const queries = [
            fullAddress.value, 
            [
                barangays.value.find(b => b.code === form.barangay_code)?.name, 
                cities.value.find(c => c.code === form.city_code)?.name,
                provinces.value.find(p => p.code === form.province_code)?.name,
                regions.value.find(r => r.code === form.region_code)?.name,
                "Philippines"
            ].filter(Boolean).join(', '),

            [
                cities.value.find(c => c.code === form.city_code)?.name,
                provinces.value.find(p => p.code === form.province_code)?.name,
                regions.value.find(r => r.code === form.region_code)?.name,
                "Philippines"
            ].filter(Boolean).join(', ')
        ];

        let found = false;
        let lat, lon;

        for (const q of queries) {
            const response = await fetch(`https://photon.komoot.io/api/?q=${encodeURIComponent(q)}&limit=1`);
            const data = await response.json();

            console.log(data);
            
            if (data.features?.length) {
                [lon, lat] = data.features[0].geometry.coordinates;
                found = true;
                break;
            }
        }

        if (!found) {
            error("Address not found. Please try a nearby city or barangay.");
            return;
        }

        form.latitude = lat;
        form.longitude = lon;

        if (!marker.value) {
            marker.value = L.marker([lat, lon], { draggable: true, icon: defaultIcon }).addTo(mapInstance.value);
            marker.value.on("dragend", () => {
                const pos = marker.value.getLatLng();
                form.latitude = pos.lat;
                form.longitude = pos.lng;
            });
        } else {
            marker.value.setLatLng([lat, lon]);
        }

        mapInstance.value.setView([lat, lon], 17);
        success("Location marked on map (nearest possible match).");

    } catch (err) {
        console.error(err);
        error("Failed to locate address.");
    } finally {
        isLocating.value = false;
    }
};


const submitForm = () => {
    form.post("/warehouses", {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success("Warehouse created successfully");
            form.reset();
            previewImage.value = null;
            if (marker.value) {
                mapInstance.value.removeLayer(marker.value);
                marker.value = null;
            }
        },
        onError: () => error("Failed to create warehouse")
    });
};

onBeforeUnmount(() => {
    if (mapInstance.value) {
        mapInstance.value.remove();
        mapInstance.value = null;
    }
});

onMounted(() => {
    fetchRegions();
});

</script>

<template>
    <Head title="Create Warehouse" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Create Warehouse
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">

                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        Warehouse Information
                    </h1>

                    <div class="space-y-8">

                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <div>
                                <InputLabel value="Warehouse Name" />
                                <InputText v-model="form.name" class="w-full mt-1" placeholder="Manila Hub" />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>

                        </div>

                        <!-- Address Section -->
                        <div>
                            <h2 class="text-xl font-bold mb-4">Address</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                                <div>
                                    <InputLabel value="House / Unit No." />
                                    <InputText v-model="form.house_number" class="w-full mt-1" />
                                    <InputError :message="form.errors.house_number" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Street" />
                                    <InputText v-model="form.street" class="w-full mt-1" />
                                    <InputError :message="form.errors.street" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Region" />
                                    <Dropdown
                                        v-model="form.region_code"
                                        :options="regions"
                                        optionLabel="name"
                                        optionValue="code"
                                        placeholder="Select Region"
                                        class="w-full mt-1"
                                        :loading="loadingRegions"
                                        showClear
                                    />
                                    <InputError :message="form.errors.region_code" class="mt-1" />
                                </div>

                                <div v-if="!isNCR">
                                    <InputLabel value="Province" />
                                    <Dropdown
                                        v-model="form.province_code"
                                        :options="provinces"
                                        optionLabel="name"
                                        optionValue="code"
                                        placeholder="Select Province"
                                        class="w-full mt-1"
                                        :loading="loadingProvinces"
                                        :disabled="!form.region_code"
                                        showClear
                                    />
                                    <InputError :message="form.errors.province_code" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="City / Municipality" />
                                    <Dropdown
                                        v-model="form.city_code"
                                        :options="cities"
                                        optionLabel="name"
                                        optionValue="code"
                                        placeholder="Select City"
                                        class="w-full mt-1"
                                        :loading="loadingCities"
                                        :disabled="isNCR ? !form.region_code : !form.province_code"
                                        showClear
                                    />
                                    <InputError :message="form.errors.city_code" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Barangay" />
                                    <Dropdown
                                        v-model="form.barangay_code"
                                        :options="barangays"
                                        optionLabel="name"
                                        optionValue="code"
                                        placeholder="Select Barangay"
                                        class="w-full mt-1"
                                        :loading="loadingBarangays"
                                        :disabled="!form.city_code"
                                        showClear
                                    />
                                    <InputError :message="form.errors.barangay_code" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Zip Code" />
                                    <InputNumber v-model="form.zip_code" class="w-full mt-1" :useGrouping="false" />
                                    <InputError :message="form.errors.zip_code" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Country" />
                                    <InputText v-model="form.country" class="w-full mt-1" />
                                    <InputError :message="form.errors.country" class="mt-1" />
                                </div>

                            </div>
                        </div>

                        <div class="border-t mt-6 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-xl font-bold">Warehouse Location</h2>
                                <Button 
                                    label="Locate Address on Map" 
                                    icon="pi pi-map-marker"
                                    @click="locateAddress"
                                    :loading="isLocating"
                                    :disabled="!canLocate"
                                    severity="secondary"
                                />
                            </div>

                            <div 
                                ref="mapRef" 
                                style="height: 400px; width: 100%;"
                                class="rounded shadow-sm border z-0 mb-2"
                            ></div>

                            <p class="text-sm text-gray-500">
                                Click "Locate Address on Map" to find your warehouse location. You can drag the marker to adjust the exact position.
                            </p>

                            <div v-if="form.latitude && form.longitude" class="text-xs text-gray-400 mt-2">
                                Coordinates: {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
                            </div>

                            <InputError :message="form.errors.latitude" class="mt-1" />
                            <InputError :message="form.errors.longitude" class="mt-1" />
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end mt-8">
                        <Button 
                            label="Create Warehouse" 
                            @click="submitForm"
                            :disabled="form.processing"
                            :loading="form.processing"
                        />
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>