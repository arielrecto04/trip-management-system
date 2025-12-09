<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';
import FileUpload from 'primevue/fileupload';
import Button from 'primevue/button';
import Image from 'primevue/image';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();
const maintenance = props.maintenance;

// Attachments
const previewFiles = ref([]);
const attachments = ref(maintenance.attachments || []);

// MAIN FORM
const form = useForm({
    _method: 'PUT',
    vehicle_id: maintenance.vehicle_id,
    technician: maintenance.technician,
    work_performed: maintenance.work_performed,
    cost: maintenance.cost,
    current_odometer: maintenance.current_odometer,

    start_maintenance_date: maintenance.start_maintenance_date,
    end_maintenance_date: maintenance.end_maintenance_date,

    attachments: [],
    existing_attachments: maintenance.attachments ?? [],
    deleted_attachments: [],
});

// FILE HANDLING
const onFileSelect = ({ files }) => {
    form.attachments = files;
    previewFiles.value = [];

    for (const f of files) {
        previewFiles.value.push(URL.createObjectURL(f));
    }
};

const removeFiles = () => {
    form.attachments = [];
    previewFiles.value.forEach(u => URL.revokeObjectURL(u));
    previewFiles.value = [];
};

const removeExistingFile = (file) => {
    form.deleted_attachments.push(file);
    form.existing_attachments = form.existing_attachments.filter(f => f !== file);
};

const submitForm = () => {
    if (form.start_maintenance_date) {
        form.start_maintenance_date = new Date(form.start_maintenance_date).toISOString().split("T")[0];
    }

    if (form.end_maintenance_date) {
        form.end_maintenance_date = new Date(form.end_maintenance_date).toISOString().split("T")[0];
    }

    form.post(route('maintenance.update', maintenance.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => success("Maintenance updated"),
        onError: () => error("Failed to update maintenance"),
    });
};
</script>

<template>
    <Head title="Edit Maintenance Log" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Edit Maintenance Log</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl bg-bgcontentcolor-light dark:bg-bgcontentcolor-dark p-6 rounded-lg shadow">

                <h1 class="text-2xl font-bold mb-6 text-textcolor-light dark:text-textcolor-dark">
                    Maintenance Information
                </h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- LEFT COLUMN -->
                    <div class="space-y-6">

                        <!-- VEHICLE (READ ONLY) -->
                        <div>
                            <label class="font-semibold">Vehicle</label>

                            <div class="w-full mt-1 p-3 rounded border bg-gray-100 dark:bg-gray-800">
                                {{ maintenance.vehicle.brand }} - {{ maintenance.vehicle.model }}
                            </div>

                            <input type="hidden" v-model="form.vehicle_id" />
                        </div>

                        <!-- Technician -->
                        <div>
                            <label class="font-semibold">Technician / Shop</label>
                            <InputText
                                v-model="form.technician"
                                class="w-full mt-1"
                                placeholder="Juan Dela Cruz"
                            />
                            <p class="text-red-500 text-sm">{{ form.errors.technician }}</p>
                        </div>

                        <!-- Cost -->
                        <div>
                            <label class="font-semibold">Cost</label>
                            <InputNumber
                                v-model="form.cost"
                                class="w-full mt-1"
                                inputClass="w-full"
                                placeholder="1500"
                            />
                            <p class="text-red-500 text-sm">{{ form.errors.cost }}</p>
                        </div>

                        <!-- DATES -->
                        <div class="grid grid-cols-2 space-x-3">
                            <div>
                                <label class="font-semibold">Start Maintenance Date</label>
                                <DatePicker
                                    v-model="form.start_maintenance_date"
                                    showIcon
                                    dateFormat="yy-mm-dd"
                                    class="w-full mt-1"
                                />
                                <p class="text-red-500 text-sm">{{ form.errors.start_maintenance_date }}</p>
                            </div>

                            <div>
                                <label class="font-semibold">End Maintenance Date</label>
                                <DatePicker
                                    v-model="form.end_maintenance_date"
                                    showIcon
                                    dateFormat="yy-mm-dd"
                                    class="w-full mt-1"
                                />
                                <p class="text-red-500 text-sm">{{ form.errors.end_maintenance_date }}</p>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="space-y-6">

                        <!-- ODOMETER -->
                        <div>
                            <label class="font-semibold">Current Odometer</label>
                            <InputNumber
                                v-model="form.current_odometer"
                                class="w-full mt-1"
                                inputClass="w-full"
                                placeholder="102340"
                            />
                            <p class="text-red-500 text-sm">{{ form.errors.current_odometer }}</p>
                        </div>

                        <!-- WORK PERFORMED -->
                        <div>
                            <label class="font-semibold">Work Performed</label>
                            <Textarea
                                v-model="form.work_performed"
                                rows="6"
                                class="w-full mt-1"
                                placeholder="Describe what was repaired..."
                                autoResize
                            />
                            <p class="text-red-500 text-sm">{{ form.errors.work_performed }}</p>
                        </div>

                        <!-- Attachments -->
                        <div>
                            <label class="font-semibold">Attachments</label>

                            <FileUpload
                                mode="basic"
                                accept="image/*"
                                :multiple="true"
                                @select="onFileSelect"
                                class="mt-2"
                                chooseLabel="Upload Files"
                            />

                            <!-- Existing Attachments -->
                            <div v-if="form.existing_attachments.length" class="mt-6">
                                <h3 class="font-semibold mb-3">Existing Attachments</h3>

                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 space-x-8">
                                    <div
                                        v-for="file in form.existing_attachments"
                                        :key="file.id"
                                        class="relative group"
                                    >
                                        <div class="w-full h-32 rounded-lg overflow-hidden border shadow-sm">
                                            <Image
                                                preview
                                                :src="`/storage/${file.url}`"
                                                class="object-cover w-full h-full"
                                            />
                                        </div>

                                        <Button
                                            label="Remove"
                                            severity="danger"
                                            size="small"
                                            outlined
                                            class="mt-2"
                                            @click="removeExistingFile(file)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- New Files Preview -->
                            <div v-if="previewFiles.length" class="mt-4 flex flex-wrap gap-4">
                                <div
                                    v-for="(src, i) in previewFiles"
                                    :key="i"
                                    class="w-32 h-32 rounded-lg overflow-hidden shadow border"
                                >
                                    <img :src="src" class="object-cover w-full h-full" />
                                </div>

                                <Button
                                    label="Remove New"
                                    severity="danger"
                                    outlined
                                    @click="removeFiles"
                                />
                            </div>

                        </div>

                    </div>

                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end mt-10">
                    <Button label="Update Maintenance Log" @click="submitForm" />
                </div>

            </div>
        </div>

    </AuthenticatedLayout>
</template>
