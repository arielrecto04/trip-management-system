<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import L from "leaflet";
import { onMounted } from "vue";

onMounted(() => {
    const map = L.map("map").setView([14.5995, 120.9842], 13);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 19
    }).addTo(map);
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-white"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-bgcolor-light bg-bgcontentcolor-dark shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-100">
                        You're logged in!
                    </div>
                    <div id="map" class="w-full h-[400px]"></div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
