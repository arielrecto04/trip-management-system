<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Image from 'primevue/image';


import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Password from 'primevue/password';
import InputMask from 'primevue/inputmask';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();
const { props } = usePage();
const roles = ref(props.roles);

const createForm = useForm({
    name: '',
    email: '',
    phone_number: '',
    roles: [],
    password: '',
    password_confirmation: '',
    profile_picture: null,
});

const preview = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    createForm.profile_picture = file;

    if (!file) {
        preview.value = null;
        return;
    }

    preview.value = URL.createObjectURL(file);
};

const removeImage = () => {
    createForm.profile_picture = null;
    preview.value = null;
}

const submitCreateForm = () => {
    createForm.post('/users', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            createForm.reset();
            preview.value = null;
            success('User created successfully');
        },
        onError: () => {
            error('Failed to create user');
        }
    });
};
</script>

<template>
    <Head title="User" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Create User
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

                <div class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow sm:rounded-lg sm:p-8">
                    
                    <h1 class="text-textcolor-light dark:text-textcolor-dark text-2xl font-bold mb-6">
                        User Information
                    </h1>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                        <div class="space-y-4">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <InputText v-model="createForm.name" class="w-full mt-1" />
                                <InputError :message="createForm.errors.name" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <InputText v-model="createForm.email" class="w-full mt-1" />
                                <InputError :message="createForm.errors.email" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="phone_number" value="Phone Number" />
                                <InputMask v-model="createForm.phone_number" class="w-full mt-1" mask="+63999999999" placeholder="+63999999999" fluid />
                                <InputError :message="createForm.errors.phone_number" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Password" />
                                <Password v-model="createForm.password" fluid toggleMask class="w-full mt-1" />
                                <InputError :message="createForm.errors.password" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <Password v-model="createForm.password_confirmation" fluid toggleMask class="w-full mt-1" />
                                <InputError :message="createForm.errors.password_confirmation" class="mt-1" />
                            </div>
                        </div>

                        <div class="space-y-6">

                            <div>
                                <InputLabel for="profile_picture" value="Profile Picture" />
                                <input 
                                    type="file"
                                    class="mt-2"
                                    accept="image/*"
                                    @change="handleImageChange"
                                />
                                <InputError :message="createForm.errors.profile_picture" class="mt-1" />

                                <div v-if="preview" class="mt-4 flex flex-col items-start gap-3">
                                    
                                    <Image 
                                        :src="preview"
                                        alt="Preview"
                                        class="rounded-lg shadow max-w-[200px] w-full"
                                        preview
                                    />

                                    <Button 
                                        label="Remove Image"
                                        severity="danger"
                                        outlined
                                        @click="removeImage"
                                        class="w-full sm:w-auto"
                                    />
                                </div>
                            </div>

                            <div>
                                <InputLabel value="Roles" class="mb-2" />

                                <div class="grid grid-cols-2 gap-2">
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
                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end gap-2 mt-8">
                        <Button label="Create User" @click="submitCreateForm" />
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
