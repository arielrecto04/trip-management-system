<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import InputText from 'primevue/inputtext';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import Image from 'primevue/image';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();
const logs = ref(props.maintenanceLogs);

// Filters
const selectedVehicles = ref([]);
const searchQuery = ref('');
const dateRange = ref(null);
const vehicleOptions = ref(
    props.vehicles?.map(v => ({ id: v.id, plate: v.license_plate })) ?? []
);

const computeStatus = (log) => {
    const today = new Date();
    const start = new Date(log.start_maintenance_date);
    const end = new Date(log.end_maintenance_date);

    if (today < start) return "Pending";
    if (today >= start && today < end) return "In Progress";
    return "Completed";
};

const statusBadgeClass = (status) => {
    const classes = {
        'Pending': 'bg-yellow-400 text-black',
        'In Progress': 'bg-blue-500 text-white',
        'Completed': 'bg-green-500 text-white',
    };
    return classes[status] ?? 'bg-gray-300 text-black';
};

const filteredLogs = computed(() => {
    let list = logs.value;

    if (selectedVehicles.value.length) {
        list = list.filter(log => selectedVehicles.value.includes(log.vehicle_id));
    }

    if (searchQuery.value.trim() !== '') {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(log =>
            log.technician?.toLowerCase().includes(q) ||
            log.work_performed?.toLowerCase().includes(q) ||
            log.vehicle?.license_plate.toLowerCase().includes(q)
        );
    }

    if (dateRange.value && dateRange.value.length === 2) {
        const [start, end] = dateRange.value.map(d => new Date(d));
        list = list.filter(log => {
            const logDate = new Date(log.start_maintenance_date);
            return logDate >= start && logDate <= end;
        });
    }

    return list;
});

// CSV export
const exportCSV = () => {
    if (!filteredLogs.value.length) {
        error("No data to export");
        return;
    }

    const headers = [
        "Vehicle Plate",
        "Start Date",
        "End Date",
        "Technician",
        "Work Performed",
        "Cost",
        "Odometer"
    ];

    const rows = filteredLogs.value.map(log => [
        log.vehicle?.license_plate ?? "",
        log.start_maintenance_date ?? "",
        log.end_maintenance_date ?? "",
        log.technician ?? "",
        (log.work_performed ?? "").replace(/\n/g, " "),
        log.cost ?? "",
        log.current_odometer ?? ""
    ]);

    const csvContent = [
        headers.join(","),
        ...rows.map(r => r.map(d => `"${String(d).replace(/"/g, '""')}"`).join(","))
    ].join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = `maintenance_logs_${Date.now()}.csv`;
    link.click();

    URL.revokeObjectURL(url);
    success("CSV exported successfully");
};

// Delete log
const deleteLog = (id) => {
    if (!confirm('Delete this maintenance record?')) return;

    router.delete(`/maintenance/${id}`, {
        onSuccess: () => {
            logs.value = logs.value.filter(l => l.id !== id);
            success("Maintenance log deleted");
        },
        onError: () => error("Failed to delete record"),
    });
};

// Attachments dialog
const showAttachmentsDialog = ref(false);
const selectedLogAttachments = ref([]);
const selectedLog = ref(null);

const openAttachmentsDialog = (log) => {
    selectedLog.value = log;
    selectedLogAttachments.value = log.attachments ?? [];
    showAttachmentsDialog.value = true;
};
</script>

<template>
    <Head title="Maintenance Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">Maintenance Logs</h2>
        </template>

        <div class="py-4 px-6">
            <div class="mx-auto max-w-7xl px-4 py-6">

                <!-- Toolbar -->
                <Toolbar class="mb-4 bg-navcolor-light dark:bg-navcolor-dark">
                    <template #start>
                        <InputText
                            v-model="searchQuery"
                            placeholder="Search…"
                            class="mr-4 w-60"
                        />
                        <DatePicker
                            v-model="dateRange"
                            selectionMode="range"
                            placeholder="Filter by Date Range"
                            showIcon
                            class="mr-4"
                        />
                        <MultiSelect
                            v-model="selectedVehicles"
                            :options="vehicleOptions"
                            optionLabel="plate"
                            optionValue="id"
                            filter
                            placeholder="Filter by Vehicle"
                            :maxSelectedLabels="3"
                            class="w-60"
                        />
                    </template>

                    <template #end>
                        <Button 
                            label="Export CSV" 
                            icon="pi pi-file-export" 
                            severity="info" 
                            class="mr-3"
                            @click="exportCSV"
                        />
                        <Link :href="route('maintenance.create')">
                            <Button label="Add" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <DataTable
                    :value="filteredLogs"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >
                    <Column field="vehicle.license_plate" header="Vehicle" sortable>
                        <template #body="{ data }">{{ data.vehicle?.license_plate ?? 'Unknown' }}</template>
                    </Column>
                    <Column field="start_maintenance_date" header="Start Date" sortable />
                    <Column field="end_maintenance_date" header="End Date" sortable />
                    <Column field="technician" header="Technician" sortable />
                    <Column header="Cost">
                        <template #body="{ data }">₱ {{ parseFloat(data.cost).toLocaleString() }}</template>
                    </Column>
                    <Column header="Odometer">
                        <template #body="{ data }">{{ data.current_odometer?.toLocaleString() }} km</template>
                    </Column>
                    <Column header="Work Performed">
                        <template #body="{ data }">{{ data.work_performed }}</template>
                    </Column>
                    <Column header="Status">
                        <template #body="{ data }">
                            <span 
                                :class="`px-2 py-1 rounded-full text-xs font-semibold ${statusBadgeClass(computeStatus(data))}`"
                            >
                                {{ computeStatus(data) }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('maintenance.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="warning" rounded />
                                </Link>
                                <Button 
                                    icon="pi pi-eye" 
                                    severity="help" 
                                    rounded 
                                    @click="openAttachmentsDialog(data)" 
                                />
                                <Button 
                                    icon="pi pi-trash"
                                    severity="danger"
                                    rounded
                                    @click="deleteLog(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <Dialog
                    v-model:visible="showAttachmentsDialog"
                    :header="`Attachments for ${selectedLog?.vehicle?.license_plate ?? 'Unknown Vehicle'}`"
                    :modal="true"
                    :style="{ width: '60vw' }"
                    :closable="true"
                >
                    <div class="space-y-6 p-4">
                        <div v-if="selectedLogAttachments.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            <div 
                                v-for="file in selectedLogAttachments"
                                :key="file.id"
                                class="rounded-xl shadow-md overflow-hidden bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 flex flex-col"
                            >
                                <div class="w-full h-48 bg-gray-100 dark:bg-zinc-700 flex items-center justify-center">
                                    <Image
                                        preview
                                        :src="`/storage/${file.url}`"
                                        alt="Attachment Image"
                                        class="max-h-full max-w-full object-contain rounded-md"
                                    />
                                </div>
                                <div class="p-3 text-sm text-gray-600 dark:text-gray-300 truncate">
                                    {{ file.name ?? 'Attachment' }}
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 text-sm">No attachments uploaded for this maintenance log.</div>
                    </div>
                </Dialog>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
