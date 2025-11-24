<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import { InputText } from 'primevue';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from 'primevue/checkbox';
import CheckboxGroup from 'primevue/checkboxgroup';
import InputError from '@/Components/InputError.vue';

const { props } = usePage();
const users = ref(props.users);
const roles = ref(props.roles);

watch(() => props.newUser, (newUser) => {
    if (newUser) users.value.push(newUser);
});

const visible = ref(false);

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

const createForm = useForm({
    name: '',
    email: '',
    phone_number: '',
    roles: [],
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({});


const submitCreateForm = () => {
    createForm.post('/users', {
        onSuccess: () => {
            createForm.reset();
            visible.value = false;
        }
    });
};

const deleteUser = (id) => {
    if (!confirm('Are you sure you want to delete this user?')) return;

    deleteForm.delete(`/users/${id}`, {
        onSuccess: () => {
            users.value = users.value.filter(u => u.id !== id);
        },
        onError: () => {
            alert('Failed to delete user');
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
                <Button class="mb-4" label="Create" icon="pi pi-plus" severity="secondary" @click="visible = true"/>

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
                            <Button icon="pi pi-home" label="Edit" severity="info"/>
                            <Button icon="pi pi-trash" label="Delete" severity="danger" @click="deleteUser(data.id)"/>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create User Modal -->
        <Dialog v-model:visible="visible" modal header="Create User" :style="{ width: '25rem' }">

            <!-- Name -->
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <InputLabel for="name" value="Name" class="w-1/3" />
                    <InputText v-model="createForm.name" required class="w-2/3" />
                </div>
                <InputError :message="createForm.errors.name" class="mt-1" />
            </div>

            <!-- Email -->
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <InputLabel for="email" value="Email" class="w-1/3" />
                    <InputText v-model="createForm.email" class="w-2/3" />
                </div>
                <InputError :message="createForm.errors.email" class="mt-1" />
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <InputLabel for="phone_number" value="Phone Number" class="w-1/3" />
                    <InputText v-model="createForm.phone_number" class="w-2/3" />
                </div>
                <InputError :message="createForm.errors.phone_number" class="mt-1" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <InputLabel for="password" value="Password" class="w-1/3" />
                    <InputText v-model="createForm.password" type="password" class="w-2/3" />
                </div>
                <InputError :message="createForm.errors.password" class="mt-1" />
            </div>

            <!-- Password Confirmation -->
            <div class="mb-4">
                <div class="flex items-center gap-4">
                    <InputLabel for="password_confirmation" value="Password Confirmation" class="w-1/3" />
                    <InputText v-model="createForm.password_confirmation" type="password" class="w-2/3" />
                </div>
                <InputError :message="createForm.errors.password_confirmation" class="mt-1" />
            </div>

            <!-- Roles -->
            <InputLabel for="selectedRoles" value="Roles" class="mb-2" />

            <div class="grid grid-cols-2 gap-2 mb-2">
                <div 
                    v-for="role in roles" 
                    :key="role.id"
                    class="flex items-center gap-2"
                >
                    <Checkbox 
                        v-model="createForm.roles"
                        :inputId="role.id.toString()"
                        :value="role.id"
                    />
                    <InputLabel :for="role.id.toString()" :value="role.name" />
                </div>
            </div>

            <!-- Roles Error BELOW the grid -->
            <InputError :message="createForm.errors.roles" class="mt-1" />


            <!-- Buttons -->
            <div class="flex justify-end gap-2 mt-8">
                <Button type="button" label="Cancel" severity="secondary" @click="visible = false" />
                <Button type="button" label="Create" @click="submitCreateForm" />
            </div>

        </Dialog>

        
    </AuthenticatedLayout>
</template>
