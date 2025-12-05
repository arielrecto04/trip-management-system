<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Image from 'primevue/image';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();
const vehicle = props.vehicle;

// Form
const editForm = useForm({
    _method: 'PUT',
    vin: vehicle.vin,
    brand: vehicle.brand,
    model: vehicle.model,
    license_plate: vehicle.license_plate,
    capacity_kg: vehicle.capacity_kg,
    capacity_m3: vehicle.capacity_m3,
    is_active: vehicle.is_active,
    or_image: null,
    cr_image: null,
    vehicle_images: [],
    remove_existing_or: false,
    remove_existing_cr: false,
    remove_existing_vehicle_images: false,
});

const crDoc = vehicle?.compliance_docs?.find(doc => doc.doc_type === "CR");
const crPath = crDoc?.attachments?.[0]?.url ?? null;

const orDoc = vehicle?.compliance_docs?.find(doc => doc.doc_type === "OR");
const orPath = orDoc?.attachments?.[0]?.url ?? null;

console.log(vehicle);

// PREVIEWS
const previewOR = ref(orPath ? `/storage/${orPath}` : null);
const previewCR = ref(crPath ? `/storage/${crPath}` : null);
const previewVehicleImages = ref(vehicle.attachments?.map(img => `/storage/${img.url}`) || []);

// OLD IMAGES
const oldVehicleImages = ref(vehicle.attachments || []);

// Handlers
const onORSelect = (event) => {
    const file = event.files[0];
    editForm.or_image = file;
    previewOR.value = URL.createObjectURL(file);
};

const onCRSelect = (event) => {
    const file = event.files[0];
    editForm.cr_image = file;
    previewCR.value = URL.createObjectURL(file);
};

const onVehicleImagesSelect = (event) => {
    editForm.vehicle_images = [...event.files];
    previewVehicleImages.value = [];

    previewVehicleImages.value = event.files.map(file => 
        URL.createObjectURL(file)
    );

    // for (const file of event.files) {
    //     previewVehicleImages.value.push(URL.createObjectURL(file));
    // }
};

const removeOldOR = () => {
    editForm.remove_existing_or = true;
    previewOR.value = null;
};

const removeOldCR = () => {
    editForm.remove_existing_cr = true;
    previewCR.value = null;
};

const removeOldVehicleImages = () => {
    editForm.remove_existing_vehicle_images = true;
    editForm.vehicle_images = [];
    oldVehicleImages.value = [];
    previewVehicleImages.value = [];
};

const removeNewVehicleUploads = () => {
    editForm.vehicle_images = [];
    previewVehicleImages.value = [];
};

const submitEditForm = () => {
    editForm.post(route('vehicles.update', vehicle.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => success('Vehicle updated successfully'),
        onError: (errors) => {
            console.log(errors);
            error('Failed to update vehicle');
        },
    });
};
</script>

<template>
    <Head :title="`Edit Vehicle - ${vehicle.license_plate}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Edit Vehicle</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">

                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        Vehicle Information
                        <span class="text-textcolor-light dark:text-textcolor-dark text-3xl font-extrabold mb-6">
                            {{ vehicle.license_plate }}
                        </span>
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- LEFT -->
                        <div class="space-y-6">
                            <div>
                                <InputLabel value="VIN" />
                                <InputText v-model="editForm.vin" class="w-full mt-1" />
                                <InputError :message="editForm.errors.vin" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Brand" />
                                <InputText v-model="editForm.brand" class="w-full mt-1" />
                                <InputError :message="editForm.errors.brand" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Model" />
                                <InputText v-model="editForm.model" class="w-full mt-1" />
                                <InputError :message="editForm.errors.model" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Plate" />
                                <InputText v-model="editForm.license_plate" class="w-full mt-1" />
                                <InputError :message="editForm.errors.license_plate" class="mt-1" />
                            </div>
                        </div>

                        <!-- RIGHT -->
                        <div class="space-y-6">
                            <div>
                                <InputLabel value="Capacity (kg)" />
                                <InputNumber v-model="editForm.capacity_kg" class="w-full mt-1" showButtons />
                                <InputError :message="editForm.errors.capacity_kg" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Capacity (m³)" />
                                <InputNumber v-model="editForm.capacity_m3" class="w-full mt-1" showButtons />
                                <InputError :message="editForm.errors.capacity_m3" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Status" />
                                <Select
                                    v-model="editForm.is_active"
                                    :options="[
                                        { label: 'Active', value: 1 },
                                        { label: 'Inactive', value: 0 },
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="w-full mt-1"
                                />
                                <InputError :message="editForm.errors.is_active" class="mt-1" />
                            </div>
                        </div>

                        <!-- FILE UPLOADS -->
                        <div class="space-y-4 lg:col-span-2">

                            <!-- OR -->
                            <div>
                                <InputLabel value="OR Image" />
                                <FileUpload mode="basic" name="or_image" accept="image/*" @select="onORSelect" chooseLabel="Upload OR" class="mt-1" />
                                <div v-if="previewOR" class="mt-2">
                                    <Image preview :src="previewOR" class="h-40 rounded shadow-sm border" />
                                    <Button class="mt-1" label="Remove OR" severity="danger" outlined @click="removeOldOR" />
                                </div>
                                <InputError :message="editForm.errors.or_image" class="mt-1" />
                            </div>

                            <!-- CR -->
                            <div>
                                <InputLabel value="CR Image" />
                                <FileUpload mode="basic" name="cr_image" accept="image/*" @select="onCRSelect" chooseLabel="Upload CR" class="mt-1" />
                                <div v-if="previewCR" class="mt-2">
                                    <Image preview :src="previewCR" class="h-40 rounded shadow-sm border" />
                                    <Button class="mt-1" label="Remove CR" severity="danger" outlined @click="removeOldCR" />
                                </div>
                                <InputError :message="editForm.errors.cr_image" class="mt-1" />
                            </div>

                            <!-- Vehicle Images -->
                            <div>
                                <InputLabel value="Vehicle Images" />
                                <FileUpload mode="basic" name="vehicle_images" accept="image/*" :multiple="true" @select="onVehicleImagesSelect" chooseLabel="Upload Images" class="mt-1" />
                                <div v-if="previewVehicleImages.length > 0" class="w-full mt-2 flex flex-wrap gap-4">
                                    <div v-for="(src, index) in previewVehicleImages" :key="index" class="w-32 h-32 border rounded shadow-sm overflow-hidden">
                                        <Image preview :src="src" class="object-cover w-full h-full" />
                                    </div>
                                    <Button class="mt-2" label="Remove Vehicle Images" severity="danger" outlined @click="removeOldVehicleImages" />
                                </div>
                                <InputError :message="editForm.errors.vehicle_images" class="mt-1" />
                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end mt-8">
                        <Button label="Update Vehicle" @click="submitEditForm" />
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
