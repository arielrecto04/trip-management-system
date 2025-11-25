<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from 'primevue/checkbox';
import InputError from '@/Components/InputError.vue';
import { useGlobalToast } from '@/Utils/toast'; 

const { success, error } = useGlobalToast();


const { props } = usePage();
const user = ref(props.user);
const roles = ref(props.roles);

const editForm = useForm({
    id: '',
    name: '',
    email: '',
    phone_number: '',
    roles: [],
    password: '',
    password_confirmation: '',
});

onMounted(() => {
    if(user.value) {
        editForm.id = user.value.id;
        editForm.name = user.value.name;
        editForm.email = user.value.email;
        editForm.phone_number = user.value.phone_number;
        editForm.roles = user.value.roles.map(r => r.id);
    }
})

console.log('user', user.value);

const submitEditForm = () => {
    editForm.put(`/users/${editForm.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            if(page.props.user) {
                editForm.name = page.props.user.name;
                editForm.email = page.props.user.email;
                editForm.phone_number = page.props.user.phone_number;
                editForm.roles = page.props.user.roles.map(r => r.id);
            }
            success('User updated successfully');
            console.log('User updated successfully');
        },
        onError: () => {
            error('Failed to update user');
            console.log('Failed to update user');
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
                            <InputText v-model="editForm.name" required class="w-2/3" />
                        </div>
                        <InputError :message="editForm.errors.name" class="mt-1" />
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <InputLabel for="email" value="Email" class="w-1/3" />
                            <InputText v-model="editForm.email" class="w-2/3" />
                        </div>
                        <InputError :message="editForm.errors.email" class="mt-1" />
                    </div>

                    <!-- Phone -->
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <InputLabel for="phone_number" value="Phone Number" class="w-1/3" />
                            <InputText v-model="editForm.phone_number" class="w-2/3" />
                        </div>
                        <InputError :message="editForm.errors.phone_number" class="mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <InputLabel for="password" value="Password" class="w-1/3" />
                            <InputText v-model="editForm.password" type="password" class="w-2/3" />
                        </div>
                        <InputError :message="editForm.errors.password" class="mt-1" />
                    </div>

                    <!-- Password Confirmation -->
                    <div class="mb-4">
                        <div class="flex items-center gap-4">
                            <InputLabel for="password_confirmation" value="Password Confirmation" class="w-1/3" />
                            <InputText v-model="editForm.password_confirmation" type="password" class="w-2/3" />
                        </div>
                        <InputError :message="editForm.errors.password_confirmation" class="mt-1" />
                    </div>

                    <!-- Roles -->
                    <InputLabel value="Roles" class="mb-2" />

                    <div class="grid grid-cols-2 gap-2 mb-2">
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


                    <!-- Buttons -->
                    <div class="flex justify-end gap-2 mt-8">
                        <Button type="submit" label="Update" @click="submitEditForm" />
                    </div>
                </div>
            </div>

        </div>        
    </AuthenticatedLayout>
</template>
