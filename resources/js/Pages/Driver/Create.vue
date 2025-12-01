<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

import InputText from 'primevue/inputtext';
import AutoComplete from 'primevue/autocomplete';
import FileUpload from 'primevue/fileupload';
import DatePicker from 'primevue/datepicker';
import Button from 'primevue/button';
import MultiSelect from 'primevue/multiselect';
import Image from 'primevue/image';


import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();

const { props } = usePage();

const users = ref(props.drivers || []);

// FORM
const createForm = useForm({
    user_id: '',
    license_number: '',
    license_restriction: [],
    license_expiration: '',
    license_images: [],
});

const userSuggestions = ref([]);
const selectedUser = ref(null);
const selectedCodes = ref([]);

const previewImages = ref([]);

watch(selectedCodes, (value) => {
    createForm.license_restriction = value;
})

const searchUsers = (event) => {
    const query = event.query.toLowerCase();
    userSuggestions.value = (users.value || []).filter(user => 
        user.name.toLowerCase().includes(query)
    );
}

const onUserSelect = (e) => {
    createForm.user_id = e.value.id;
}

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

const onLicenseSelect = ({ files }) => {
    createForm.license_images = files;

    previewImages.value = [];

    for (const file of files) {
        const url = URL.createObjectURL(file);
        previewImages.value.push(url);
    }
};

const removeLicenseImages = () => {
    createForm.license_images = [];
    previewImages.value.forEach(url => URL.revokeObjectURL(url));
    previewImages.value = [];
};


const submitCreateForm = () => {
    if(createForm.license_expiration) {
        const date = new Date(createForm.license_expiration);
        createForm.license_expiration = date.toISOString().split('T')[0];
    }

    createForm.post('/drivers', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success('Driver created successfully');
            createForm.reset();
        },
        onError: () => {
            error('Failed to create driver');
        },
    });
};
</script>

<template>
    <Head title="Create Driver" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Create Driver
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">

                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        Driver Information
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <!-- LEFT SIDE -->
                        <div class="space-y-6">

                            <!-- User -->
                            <div>
                                <InputLabel value="Select User" />
                                <AutoComplete 
                                    fluid
                                    dropdown
                                    v-model="selectedUser"
                                    optionLabel="name"
                                    :suggestions="userSuggestions"
                                    @complete="searchUsers"
                                    @item-select="onUserSelect"
                                    placeholder="Search user..."
                                    class="w-full mt-1"
                                />
                                <InputError :message="createForm.errors.user_id" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Number" />
                                <InputText v-model="createForm.license_number" class="w-full mt-1" placeholder="X21-31-472495" />
                                <InputError :message="createForm.errors.license_number" class="mt-1" />
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
                                <InputError :message="createForm.errors.license_restriction" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="License Expiration" />
                                <DatePicker 
                                    v-model="createForm.license_expiration" 
                                    showIcon 
                                    fluid 
                                    iconDisplay="input"
                                    dateFormat="yy-mm-dd" 
                                />
                                <InputError :message="createForm.errors.license_expiration" class="mt-1" />
                            </div>

                        </div>

                        <div class="space-y-6">

                            <div>
                                <InputLabel value="License Images (Front & Back)" />

                                <FileUpload
                                    mode="basic"
                                    name="license_images"
                                    accept="image/*"
                                    :multiple="true"
                                    @select="onLicenseSelect"
                                    chooseLabel="Upload Images"
                                    class="mt-2"
                                />

                                <InputError :message="createForm.errors.license_images" class="mt-1" />

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
                                        label="Remove Images"
                                        severity="danger"
                                        outlined
                                        @click="removeLicenseImages"
                                        class="mt-2"
                                    />
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end mt-8">
                        <Button label="Create Driver" @click="submitCreateForm" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
