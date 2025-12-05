<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Image from 'primevue/image';

import { ref } from 'vue';
import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const previewOR = ref(null);
const previewCR = ref(null);
const previewImages = ref([]);

const form = useForm({
    vin: "",
    brand: "",
    model: "",
    license_plate: "",
    capacity_kg: null,
    capacity_m3: null,
    is_active: 1,
    or_image: null,
    cr_image: null,
    vehicle_images: [],
});

const onORSelect = (event) => {
    const file = event.files[0];
    form.or_image = file;
    previewOR.value = URL.createObjectURL(file);
};

const onCRSelect = (event) => {
    const file = event.files[0];
    form.cr_image = file;
    previewCR.value = URL.createObjectURL(file);
};

const onVehicleImagesSelect = (event) => {
    form.vehicle_images = event.files;
    previewImages.value = [];
    for (const file of event.files) {
        previewImages.value.push(URL.createObjectURL(file));
    }
};

const submitForm = () => {
    form.post("/vehicles", {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success("Vehicle created successfully");
            form.reset();
            previewOR.value = null;
            previewCR.value = null;
            previewImages.value = [];
        },
        onError: () => {
            error("Failed to create vehicle");
        }
    });
};
</script>

<template>
    <Head title="Create Vehicle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Create Vehicle
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">

                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        Vehicle Information
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- LEFT SIDE -->
                        <div class="space-y-6">

                            <div>
                                <InputLabel value="VIN" />
                                <InputText v-model="form.vin" class="w-full mt-1" placeholder="1HGBH41JXMN109186" />
                                <InputError :message="form.errors.vin" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Brand" />
                                <InputText v-model="form.brand" class="w-full mt-1" placeholder="Toyota" />
                                <InputError :message="form.errors.brand" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Model" />
                                <InputText v-model="form.model" class="w-full mt-1" placeholder="HiAce" />
                                <InputError :message="form.errors.model" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Plate" />
                                <InputText v-model="form.license_plate" class="w-full mt-1" placeholder="ABC-1234" />
                                <InputError :message="form.errors.license_plate" class="mt-1" />
                            </div>

                        </div>

                        <!-- RIGHT SIDE -->
                        <div class="space-y-6">

                            <div>
                                <InputLabel value="Capacity (kg)" />
                                <InputNumber v-model="form.capacity_kg" class="w-full mt-1" showButtons />
                                <InputError :message="form.errors.capacity_kg" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Capacity (m³)" />
                                <InputNumber v-model="form.capacity_m3" class="w-full mt-1" showButtons />
                                <InputError :message="form.errors.capacity_m3" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Status" />
                                <Select
                                    v-model="form.is_active"
                                    :options="[
                                        { label: 'Active', value: 1 },
                                        { label: 'Inactive', value: 0 },
                                    ]"
                                    optionLabel="label"
                                    optionValue="value"
                                    class="w-full mt-1"
                                />
                                <InputError :message="form.errors.is_active" class="mt-1" />
                            </div>

                        </div>

                        <!-- FILE UPLOADS -->
                        <div class="space-y-4 lg:col-span-2">

                            <!-- OR -->
                            <div>
                                <InputLabel value="OR Image" />
                                <FileUpload
                                    mode="basic"
                                    name="or_image"
                                    accept="image/*"
                                    @select="onORSelect"
                                    chooseLabel="Upload OR"
                                    class="mt-1"
                                />
                                <div v-if="previewOR" class="mt-2">
                                    <Image
                                        preview
                                        alt="Preview" 
                                        :src="previewOR" 
                                        class="h-40 rounded shadow-sm border" 
                                    />
                                </div>
                                <InputError :message="form.errors.or_image" class="mt-1" />
                            </div>

                            <!-- CR -->
                            <div>
                                <InputLabel value="CR Image" />
                                <FileUpload
                                    mode="basic"
                                    name="cr_image"
                                    accept="image/*"
                                    @select="onCRSelect"
                                    chooseLabel="Upload CR"
                                    class="mt-1"
                                />
                                <div v-if="previewCR" class="mt-2">
                                    <Image 
                                        :src="previewCR"
                                        alt="Preview"
                                        imageClass="h-40 rounded shadow-sm border" 
                                        preview
                                    />
                                </div>
                                <InputError :message="form.errors.cr_image" class="mt-1" />
                            </div>

                            <!-- Vehicle Images -->
                            <div>
                                <InputLabel value="Vehicle Images" />
                                <FileUpload
                                    mode="basic"
                                    name="vehicle_images"
                                    accept="image/*"
                                    :multiple="true"
                                    @select="onVehicleImagesSelect"
                                    chooseLabel="Upload Images"
                                    class="mt-1"
                                />
                                <div v-if="previewImages.length > 0" class="w-full mt-2 flex flex-wrap gap-4">
                                    <div
                                        v-for="(src, index) in previewImages"
                                        :key="index"
                                        class="w-32 h-32 border rounded shadow-sm overflow-hidden"
                                    >
                                        <Image 
                                            :src="src"
                                            alt="Preview"
                                            imageClass="object-cover w-full h-full" 
                                            preview
                                        />
                                    </div>
                                </div>
                                <InputError :message="form.errors.vehicle_images" class="mt-1" />
                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end mt-8">
                        <Button label="Create Vehicle" @click="submitForm" />
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
