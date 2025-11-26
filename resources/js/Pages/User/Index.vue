<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';

import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();
const { props } = usePage();
const users = ref(props.users);

const deleteUser = (id) => {
    if (!confirm('Are you sure you want to delete this user?')) return;

    router.delete(`/users/${id}`, {
        onSuccess: () => {
            users.value = users.value.filter(u => u.id !== id);
            success('User deleted successfully');
        },
        onError: () => {
            error('Failed to delete user');
        }
    });
};

const roleBadgeClass = (slug) => {
    const classes = {
        'logistics-manager': 'bg-blue-500 text-white',
        'fleet-manager': 'bg-green-500 text-white',
        'driver': 'bg-yellow-400 text-black',
        'dispatcher': 'bg-purple-500 text-white',
        'finance-admin-clerk': 'bg-red-500 text-white',
        'customer-service-rep': 'bg-pink-500 text-white',
    };
    return classes[slug] ?? 'bg-gray-300 text-black';
};
</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-white">User</h2>
        </template>

        <div class="py-4 px-6">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <!-- Toolbar -->
                <Toolbar class="mb-4">
                    <template #start>
                        <h3 class="text-lg font-semibold">User List</h3>
                    </template>

                    <template #end>
                        <Link :href="route('users.create')">
                            <Button label="Create User" icon="pi pi-plus" severity="secondary" />
                        </Link>
                    </template>
                </Toolbar>

                <!-- Table -->
                <DataTable 
                    :value="users" 
                    dataKey="id"
                    stripedRows
                    paginator
                    :rows="10"
                    class="shadow rounded-lg"
                >

                    <!-- Avatar -->
                    <Column header="Avatar" class="w-20 text-center">
                        <template #body="{ data }">
                            <img
                                :src="data.profile_picture?.url 
                                    ? `/storage/${data.profile_picture.url}` 
                                    : '/images/default-avatar.png'"
                                class="w-12 h-12 rounded-full object-cover mx-auto"
                            />
                        </template>
                    </Column>

                    <Column field="name" header="Name" sortable />
                    <Column field="email" header="Email" sortable />
                    <Column field="phone_number" header="Phone" sortable />

                    <!-- Roles -->
                    <Column header="Roles">
                        <template #body="{ data }">
                            <span
                                v-for="role in data.roles"
                                :key="role.id"
                                class="inline-block px-2 py-1 rounded-full text-xs font-semibold mr-2"
                                :class="roleBadgeClass(role.slug)"
                            >
                                {{ role.name }}
                            </span>
                        </template>
                    </Column>

                    <!-- Action buttons -->
                    <Column header="Actions" class="w-40">
                        <template #body="{ data }">
                            <div class="flex items-center gap-2">
                                <Link :href="route('users.edit', data.id)">
                                    <Button icon="pi pi-user-edit" severity="info" rounded />
                                </Link>

                                <Button 
                                    icon="pi pi-trash" 
                                    severity="danger" 
                                    rounded 
                                    @click="deleteUser(data.id)"
                                />
                            </div>
                        </template>
                    </Column>

                </DataTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

