<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    shipment: { type: Object, required: true }
});

const emit = defineEmits(['view']);

function view() {
    emit('view', props.shipment);
}

function statusColor(status) {
    if (status === 'Delivered') return 'bg-green-100 text-green-700';
    if (status === 'In Transit') return 'bg-blue-100 text-blue-700';
    if (status === 'Delayed') return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <div
        class="flex items-center justify-between rounded-lg p-4 bg-white dark:bg-zinc-800 shadow-sm border dark:border-zinc-700">
        <div class="flex items-center gap-4">
            <div class="w-14">
                <div class="text-xs text-gray-400">Tracking</div>
                <div class="font-semibold">{{ shipment.trackingNumber }}</div>
            </div>
            <div>
                <div class="text-sm font-medium">{{ shipment.origin }} → {{ shipment.destination }}</div>
                <div class="text-xs text-gray-500">{{ shipment.courier }} • {{ shipment.vehicle }}</div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div :class="['px-3 py-1 rounded-md text-sm font-medium', statusColor(shipment.status)]">{{ shipment.status
                }}</div>
            <div class="text-xs text-gray-500 text-right">
                <div>ETA</div>
                <div class="font-medium">{{ shipment.eta }}</div>
            </div>
            <button @click="view" class="px-3 py-2 bg-[#0ea5a4] text-white rounded-md ml-4">View</button>
        </div>
    </div>
</template>

<style scoped>
/* no extra styles */
</style>
