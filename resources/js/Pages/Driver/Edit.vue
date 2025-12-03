<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";

import InputText from "primevue/inputtext";
import AutoComplete from "primevue/autocomplete";
import FileUpload from "primevue/fileupload";
import DatePicker from "primevue/datepicker";
import Button from "primevue/button";
import MultiSelect from "primevue/multiselect";
import Image from "primevue/image";

import { useGlobalToast } from "@/Utils/toast";
const { success, error } = useGlobalToast();

const { props } = usePage();
const driver = props.driver;
const users = ref(props.drivers || []);

const licenseRestriction = [
    { code: "A", label: "Motorcycles" },
    { code: "A2", label: "Tricycles and microcars" },
    { code: "B", label: "Passenger cars (≤ 8 seats, ≤ 5,000 kg GVW)" },
    { code: "B1", label: "Passenger vehicles (≥ 9 seats, ≤ 5,000 kg GVW)" },
    { code: "B2", label: "Goods vehicles (≤ 3,500 kg GVW)" },
    { code: "C", label: "Goods vehicles (> 3,500 kg GVW)" },
    { code: "D", label: "Passenger vehicles (> 5,000 kg GVW & > 8 seats)" },
    { code: "BE", label: "Articulated vehicles (semi-trailers)" },
    { code: "CE", label: "Cars with trailers" },
];

const selectedCodes = ref((driver.license_restrictions || []).map(r => r.code));

const editForm = useForm({
    _method: "PUT",
    user_id: driver.user_id,
    license_number: driver.license_number,
    license_restriction: selectedCodes.value,
    license_expiration: driver.license_expiration,
    new_license_images: [],
    remove_existing_license_images: false,
});


const previewImages = ref([]);

const oldLicenseImages = ref(driver.driver_license || []);


watch(selectedCodes, (value) => {
    editForm.license_restriction = value;
});

const onLicenseSelect = ({ files }) => {
    editForm.new_license_images = files;

    previewImages.value = [];

    for (const file of files) {
        const url = URL.createObjectURL(file);
        previewImages.value.push(url);
    }
};

const removeOldLicenseImages = () => {
    editForm.remove_existing_license_images = true;
    oldLicenseImages.value = [];
};

const removeNewUploads = () => {
    editForm.new_license_images = [];
    previewImages.value.forEach((url) => URL.revokeObjectURL(url));
    previewImages.value = [];
};

const submitEditForm = () => {
    editForm.transform((data) => {
        const formattedData = { ...data };
        if (formattedData.license_expiration) {
            const date = new Date(formattedData.license_expiration);
            formattedData.license_expiration = date.toISOString().split("T")[0];
        }
        return {
            ...formattedData,
            _method: 'PUT'
        };
    }).post(route('drivers.update', driver.id), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            success('Driver updated successfully');
        },
        onError: (errors) => {
            console.log('Validation Errors:', errors);
            error('Failed to update driver');
        },
    });
};
</script>

<template>
    <Head :title="`Edit Driver - ${driver.user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Edit Driver</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">

                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        Driver Information of 
                        <span class="text-textcolor-light dark:text-textcolor-dark text-3xl font-extrabold mb-6">
                            {{ driver.user.name }}
                        </span>
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <div class="space-y-6">

                            <div>
                                <InputLabel value="License Number" />
                                <InputText
                                    v-model="editForm.license_number"
                                    class="w-full mt-1"
                                    placeholder="X21-31-472495"
                                />
                                <InputError :message="editForm.errors.license_number" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Restriction" />
                                <MultiSelect
                                    v-model="selectedCodes"
                                    :options="licenseRestriction"
                                    optionLabel="label"
                                    optionValue="code"
                                    filter
                                    filterBy="label"
                                    placeholder="Select license codes"
                                    class="w-full mt-1"
                                />
                                <InputError :message="editForm.errors.license_restriction" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Expiration" />
                                <DatePicker
                                    v-model="editForm.license_expiration"
                                    showIcon
                                    fluid
                                    iconDisplay="input"
                                    dateFormat="yy-mm-dd"
                                />
                                <InputError :message="editForm.errors.license_expiration" class="mt-1" />
                            </div>

                        </div>

                        <!-- RIGHT -->
                        <div class="space-y-6">

                            <InputLabel value="Existing License Images" />

                            <!-- OLD IMAGES -->
                            <div v-if="oldLicenseImages.length > 0" class="flex flex-wrap gap-4 mt-2">
                                <div
                                    v-for="(img, index) in oldLicenseImages"
                                    :key="index"
                                    class="w-32 h-32 border rounded-lg overflow-hidden shadow"
                                >
                                    <Image
                                        :src="`/storage/${img.url}`"
                                        alt="License"
                                        imageClass="object-cover w-full h-full"
                                        preview
                                    />
                                </div>

                                <Button
                                    label="Remove Existing Images"
                                    severity="danger"
                                    outlined
                                    @click="removeOldLicenseImages"
                                />
                            </div>
                            <div v-else class="text-gray-500 dark:text-gray-400">
                                No existing license images.
                            </div>

                            <!-- NEW UPLOADS -->
                            <div>
                                <InputLabel value="Upload New License Images" />

                                <FileUpload
                                    mode="basic"
                                    name="new_license_images"
                                    accept="image/*"
                                    :multiple="true"
                                    @select="onLicenseSelect"
                                    chooseLabel="Upload Images"
                                    class="mt-2"
                                />

                                <div v-if="previewImages.length > 0" class="mt-4 flex flex-wrap gap-4">
                                    <div
                                        v-for="(src, index) in previewImages"
                                        :key="index"
                                        class="w-32 h-32 border rounded-lg overflow-hidden shadow"
                                    >
                                        <Image
                                            :src="src"
                                            alt="Preview"
                                            imageClass="object-cover w-full h-full"
                                            preview
                                        />
                                    </div>
                                    <Button
                                        class="mt-2"
                                        label="Remove New Uploads"
                                        severity="danger"
                                        outlined
                                        @click="removeNewUploads"
                                    />
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end mt-8">
                        <Button label="Update Driver" @click="submitEditForm" />
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
