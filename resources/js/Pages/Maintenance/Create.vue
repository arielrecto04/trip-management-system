<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import AutoComplete from 'primevue/autocomplete';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();

const vehicles = ref(
    props.vehicles.map(v => ({
        ...v,
        label: `${v.brand} - ${v.model}` 
    }))
);

console.log(props.vehicles);

const formatLocalDate = (d) => {
    if (!d) return "";

    const date = new Date(d);

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const vehicleSuggestions = ref([]);
const selectedVehicle = ref(null);

const searchVehicle = (event) => {
    const query = (event.query || "").toLowerCase();

    vehicleSuggestions.value = vehicles.value.filter(v => {
        return (
            (v.brand && v.brand.toLowerCase().includes(query)) ||
            (v.model && v.model.toLowerCase().includes(query)) ||
            (v.vin && v.vin.toLowerCase().includes(query))
        )
    });

    console.log(vehicleSuggestions);
};

const form = useForm({
    vehicle_id: "",
    start_maintenance_date: "",
    end_maintenance_date: "",
    technician: "",
    work_performed: "",
    cost: "",
    current_odometer: "",
    attachments: [],
});

const previewFiles = ref([]);

const onFileSelect = ({ files }) => {
    form.attachments = files;

    previewFiles.value = [];
    for (const f of files) {
        const url = URL.createObjectURL(f);
        previewFiles.value.push(url);
    }
};

const removeFiles = () => {
    form.attachments = [];
    previewFiles.value.forEach(u => URL.revokeObjectURL(u));
    previewFiles.value = [];
};

const submitForm = () => {
    form.start_maintenance_date = formatLocalDate(form.start_maintenance_date);
    form.end_maintenance_date = formatLocalDate(form.end_maintenance_date);

    form.post('/maintenance', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            success('Maintenance log created');
            form.reset();
            previewFiles.value = [];
        },
        onError: () => {
            error('Failed to create maintenance log');
        }
    })
};
</script>

<template>
    <Head title="Create Maintenance" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Create Maintenance Log</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl bg-bgcontentcolor-light dark:bg-bgcontentcolor-dark p-6 rounded-lg shadow">

                <h1 class="text-2xl font-bold text-textcolor-light dark:text-textcolor-dark mb-6">
                    Maintenance Information
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <div class="space-y-6">

                        <div>
                            <label class="font-semibold">Select Vehicle</label>
                            <AutoComplete
                                v-model="selectedVehicle"
                                optionLabel="label"
                                :suggestions="vehicleSuggestions"
                                @complete="searchVehicle"
                                @item-select="e => form.vehicle_id = e.value.id"
                                placeholder="Search vehicle..."
                                dropdown
                                class="w-full mt-1"
                            />
                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.vehicle_id }}
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold">Technician / Shop</label>
                            <InputText
                                v-model="form.technician"
                                class="w-full mt-1"
                                placeholder="Juan Dela Cruz"
                            />
                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.technician }}
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold">Cost</label>
                            <InputNumber
                                v-model="form.cost"
                                class="w-full mt-1"
                                inputClass="w-full"
                                placeholder="1500"
                            />
                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.cost }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 space-x-3">
                            <div>
                                <label class="font-semibold">Start Maintenance Date</label>
                                <DatePicker
                                    v-model="form.start_maintenance_date"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full mt-1"
                                />
                                <p class="text-red-500 text-sm mt-1">
                                    {{ form.errors.start_maintenance_date }}
                                </p>
                            </div>
                            <div>
                                <label class="font-semibold">End Maintenance Date</label>
                                <DatePicker
                                    v-model="form.end_maintenance_date"
                                    dateFormat="yy-mm-dd"
                                    showIcon
                                    class="w-full mt-1"
                                />
                                <p class="text-red-500 text-sm mt-1">
                                    {{ form.errors.end_maintenance_date }}
                                </p>
                            </div>
                        </div>




                    </div>

                    <div class="space-y-6">

                        <div>
                            <label class="font-semibold">Current Odometer</label>
                            <InputNumber
                                v-model="form.current_odometer"
                                class="w-full mt-1"
                                inputClass="w-full"
                                placeholder="102340"
                            />
                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.current_odometer }}
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold">Work Performed</label>
                            <Textarea
                                v-model="form.work_performed"
                                rows="6"
                                class="w-full mt-1"
                                placeholder="Describe what was repaired or replaced..."
                                autoResize
                            />
                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.work_performed }}
                            </p>
                        </div>

                        <div>
                            <label class="font-semibold">Attachments (Optional)</label>

                            <FileUpload
                                mode="basic"
                                name="attachments"
                                accept="image/*"
                                :multiple="true"
                                @select="onFileSelect"
                                class="mt-2"
                                chooseLabel="Upload Files"
                            />

                            <p class="text-red-500 text-sm mt-1">
                                {{ form.errors.attachments }}
                            </p>

                            <div v-if="previewFiles.length" class="mt-4 flex flex-wrap gap-4">
                                <div
                                    v-for="(src, i) in previewFiles"
                                    :key="i"
                                    class="w-32 h-32 rounded-lg overflow-hidden shadow border"
                                >
                                    <img :src="src" class="object-cover w-full h-full" />
                                </div>

                                <Button
                                    label="Remove Files"
                                    severity="danger"
                                    outlined
                                    class="h-10"
                                    @click="removeFiles"
                                />
                            </div>
                        </div>

                    </div>

                </div>

                <div class="flex justify-end mt-10">
                    <Button label="Create Maintenance Log" @click="submitForm" />
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
