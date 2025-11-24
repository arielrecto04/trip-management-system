<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-sm font-medium text-textcolor-light dark:text-textcolor-dark">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>