<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import MultiSelect from 'primevue/multiselect';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

import { useGlobalToast } from '@/Utils/toast';
const { success, error } = useGlobalToast();

const { props } = usePage();

// Reactive warehouses from server
const warehouses = ref(props.warehouses);

console.log(warehouses);

// Search
const search = ref("");

// Debounce setup
let debounceTimer = null;
watch(search, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        applySearch();
    }, 300);
});

const applySearch = () => {
    const q = search.value.toLowerCase();

    warehouses.value = props.warehouses.filter(w =>
        w.name.toLowerCase().includes(q) ||
        w.contact_person_name?.toLowerCase().includes(q) ||
        w.city_name?.toLowerCase().includes(q) ||
        w.address?.toLowerCase().includes(q)
    );
};

const formatAddress = (w) => {
    const address = [];

    if(w.house_number) address.push(w.house_number);
    if(w.street) address.push(w.street);
    if(w.barangay_name) address.push(w.barangay_name);
    if(w.city_name) address.push(w.city_name);
    if(w.provice_name && w.province_name !== w.city_name) address.push(w.provice_name);
    if(w.region_name) address.push(w.region_name);
    if(w.zip_code) address.push(w.zip_code);
    if(w.country) address.push(w.country);

    address.push("Philippines");

    return address.join(', ');
}
// Delete warehouse with clean UI pattern
const loadingDelete = ref(false);
const deleteWarehouse = async (id) => {
    if (!confirm("Are you absolutely sure? This action cannot be undone.")) return;

    loadingDelete.value = true;

    router.delete(`/warehouses/${id}`, {
        onSuccess: () => {
            warehouses.value = warehouses.value.filter(w => w.id !== id);
            success("Warehouse deleted successfully.");
        },
        onError: () => error("Failed to delete warehouse."),
        onFinish: () => loadingDelete.value = false,
    });
};

</script>

<template>
    <Head title="Warehouses" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-white">Warehouses</h2>
        </template>

        <div class="p-6">
            <div class="mx-auto max-w-7xl">

                <!-- Toolbar -->
                <Toolbar class="mb-5">
                    <template #start>
                        <div class="flex items-center gap-3">
                            <InputText
                                v-model="search"
                                placeholder="Search warehouses..."
                                class="w-64"
                            />
                        </div>
                    </template>

                    <template #end>
                        <Link :href="route('warehouses.create')">
                            <Button 
                                label="Add Warehouse" 
                                icon="pi pi-plus" 
                                severity="secondary"
                            />
                        </Link>
                    </template>
                </Toolbar>

                <!-- Data Table -->
                <DataTable
                    :value="warehouses"
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >
                    <Column field="id" header="Code" sortable />

                    <Column field="name" header="Warehouse" sortable />

                    <Column header="Contact Person" sortable>
                        <template #body="{ data }">
                            {{ data.contact_person_name ?? 'N/A' }}
                        </template>
                    </Column>

                    <Column field="city_name" header="City" sortable />

                    <Column header="Address">
                        <template #body="{ data }">
                            {{ formatAddress(data) }}
                        </template>
                    </Column>

                    <Column header="Actions" class="w-32">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('warehouses.edit', data.id)">
                                    <Button icon="pi pi-pencil" severity="info" rounded />
                                </Link>

                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    rounded
                                    :loading="loadingDelete"
                                    @click="deleteWarehouse(data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
