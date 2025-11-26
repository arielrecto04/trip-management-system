<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Image from 'primevue/image';

import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { useGlobalToast } from '@/Utils/toast';

const { success, error } = useGlobalToast();
const { props } = usePage();

const user = ref(props.user);
const roles = ref(props.roles);

const editForm = useForm({
    id: props.user?.id || '',
    name: props.user?.name || '',
    email: props.user?.email || '',
    phone_number: props.user?.phone_number || '',
    roles: props.user?.roles?.map(r => r.id) || [],
    password: '',
    password_confirmation: '',
    profile_picture: null,
    remove_profile_picture: false,
});

const preview = ref(null);

onMounted(() => {
    if (user.value?.profile_picture?.url) {
        preview.value = `/storage/${user.value.profile_picture.url}`;
    }
});

const handleImageChange = (e) => {
    const file = e.target.files[0];
    

    if (!file) {
        editForm.profile_picture = null;
        preview.value = user.value.profile_picture?.url
            ? `/storage/${user.value.profile_picture.url}`
            : null;
        return;
    }

    editForm.profile_picture = file; 
    editForm.remove_profile_picture = false; 
    preview.value = URL.createObjectURL(file);
};

const removeImage = () => {
    editForm.profile_picture = null;
    editForm.remove_profile_picture = true; 
    preview.value = null;
};

const submitEditForm = () => {
    editForm.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('users.update', editForm.id), {
        preserveScroll: true,
        preserveState: true,
        forceFormData: true,
        onSuccess: () => {
            success('User updated successfully');
        },
        onError: (errors) => {
            console.log('Validation Errors:', errors);
            error('Failed to update user:', errors);
        },
    });
};
</script>

<template>
<Head title="Edit User" />

<AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold leading-tight text-white">Edit User</h2>
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
                            <InputText v-model="editForm.name" class="w-full mt-1" />
                            <InputError :message="editForm.errors.name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <InputText v-model="editForm.email" class="w-full mt-1" />
                            <InputError :message="editForm.errors.email" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="phone_number" value="Phone Number" />
                            <InputText v-model="editForm.phone_number" class="w-full mt-1" />
                            <InputError :message="editForm.errors.phone_number" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="password" value="Password" />
                            <InputText v-model="editForm.password" type="password" class="w-full mt-1" />
                            <InputError :message="editForm.errors.password" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Confirm Password" />
                            <InputText v-model="editForm.password_confirmation" type="password" class="w-full mt-1" />
                            <InputError :message="editForm.errors.password_confirmation" class="mt-1" />
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
                            <InputError :message="editForm.errors.profile_picture" class="mt-1" />

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
                                        v-model="editForm.roles"
                                        :inputId="role.id.toString()"
                                        :value="role.id"
                                    />
                                    <InputLabel :for="role.id.toString()" :value="role.name" />
                                </div>
                            </div>

                            <InputError :message="editForm.errors.roles" class="mt-1" />
                        </div>

                    </div>

                </div>

                <div class="flex justify-end gap-2 mt-8">
                    <Button label="Update User" @click="submitEditForm" />
                </div>

            </div>
        </div>
    </div>
</AuthenticatedLayout>
</template>
