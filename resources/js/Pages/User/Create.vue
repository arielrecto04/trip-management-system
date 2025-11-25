<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue'
import { usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from 'primevue/checkbox';
import InputError from '@/Components/InputError.vue';
import { useGlobalToast } from '@/Utils/toast'; 

const { success, error } = useGlobalToast();

const { props } = usePage();
const roles = ref(props.roles);


const createVisible = ref(false);

const createForm = useForm({
    name: '',
    email: '',
    phone_number: '',
    roles: [],
    password: '',
    password_confirmation: '',
});

const submitCreateForm = () => { 
    createForm.post('/users', { 
        onSuccess: (page) => { 
            createForm.reset(), 
            createVisible.value = false,
            success('User created successfully')
            console.log('User created successfully');
        }, 
        onError: () => { 
            if(createForm.errors.name) { 
                createForm.reset('name'); 
            } 
            if(createForm.errors.email) { 
                createForm.reset('email'); 
            } 
            if(createForm.errors.password) { 
                createForm.reset('password', 'password_confirmation'); 
            } 
            if(createForm.errors.phone_number) { 
                createForm.reset('phone_number'); 
            } 

            error('Failed to create user');
            console.log('Failed to create user');
        } 
    }) 
}


</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-white"
            >
                Create
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div
                    class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-4 shadow sm:rounded-lg sm:p-8"
                > 
                    <div>
                        <h1 class=" text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-4">
                            User Information
                        </h1>
                    </div>
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
                    <InputError :message="createForm.errors.roles" class="mt-1" />


                    <!-- Buttons -->
                    <div class="flex justify-end gap-2 mt-8">
                        <Button type="button" label="Create" @click="submitCreateForm" />
                    </div>
                </div>
            </div>

        </div>        
    </AuthenticatedLayout>
</template>
