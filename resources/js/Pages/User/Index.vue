<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue'
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { useGlobalToast } from '@/Utils/toast'; 

const { success, error } = useGlobalToast();

const { props } = usePage();
const users = ref(props.users);


const roleBadgeClass = (slug) => {
    switch(slug) {
        case 'logistics-manager': return 'bg-blue-500 text-white';
        case 'fleet-manager': return 'bg-green-500 text-white';
        case 'driver': return 'bg-yellow-400 text-black';
        case 'dispatcher': return 'bg-purple-500 text-white';
        case 'finance-admin-clerk': return 'bg-red-500 text-white';
        case 'customer-service-rep': return 'bg-pink-500 text-white';
        default: return 'bg-gray-300 text-black';
    }
};

const deleteForm = useForm({});

const deleteUser = (id) => {
    if (!confirm('Are you sure you want to delete this user?')) return;

    deleteForm.delete(`/users/${id}`, {
        onSuccess: () => {
            users.value = users.value.filter(u => u.id !== id);
            success('User created successfully');
        },
        onError: () => {
            error('Failed to create user');
            console.log('Failed to delete user');
        }
    });
};



</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-white"
            >
                User
            </h2>
        </template>

        <div class="py-4 px-6">
            <DataTable :value="users" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <Link :href="route('users.create')">
                    <Button class="mb-4" label="Create" icon="pi pi-plus" severity="secondary" @click="createVisible = true"/>
                </Link>

                <Column field="name" header="Name" />
                <Column field="email" header="Email" />
                <Column field="phone_number" header="Phone Number" />

                <!-- Roles -->
                <Column header="Roles">
                    <template #body="{ data }">
                        <span
                            v-for="role in data.roles"
                            :key="role.id"
                            :class="['inline-block px-2 py-1 rounded-full text-xs font-semibold mr-2 mb-1', roleBadgeClass(role.slug)]"
                        >
                            {{ role.name }}
                        </span>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions">
                    <template #body="{ data }">
                        <div class="flex gap-2">
                            <Link :href="route('users.edit', data.id)">
                                <Button icon="pi pi-user-edit" label="Edit" severity="info" />
                            </Link>
                            <Button icon="pi pi-trash" label="Delete" severity="danger" @click="deleteUser(data.id)"/>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>


        
    </AuthenticatedLayout>
</template>
