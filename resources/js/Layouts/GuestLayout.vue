<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center 
               bg-bgcolor-light dark:bg-bgcolor-dark 
               pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <ApplicationLogo
                    class="h-20 w-20 fill-current 
                           text-textcolor-light dark:text-textcolor-dark"
                />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden 
                   bg-bgcontentcolor-light dark:bg-bgcontentcolor-dark 
                   text-textcolor-light dark:text-textcolor-dark
                   px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
