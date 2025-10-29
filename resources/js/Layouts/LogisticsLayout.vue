<template>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 text-gray-800 dark:text-white">
        <header class="bg-white dark:bg-zinc-800 border-b dark:border-zinc-700">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="h-10 w-10 rounded-md bg-[#0ea5a4] flex items-center justify-center text-white font-semibold">
                        LG</div>
                    <div>
                        <div class="font-semibold text-lg">LogiTrack</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Shipment & Tracking Management (Demo)
                        </div>
                    </div>
                </div>
                <nav class="flex items-center gap-4">
                    <a href="#" class="text-sm hover:underline">Dashboard</a>
                    <a href="#" class="text-sm hover:underline">Shipments</a>
                    <a href="#" class="text-sm hover:underline">Reports</a>
                    <button class="ml-4 px-3 py-2 bg-[#FF2D20] text-white rounded-md text-sm">Sign out</button>
                </nav>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-6 py-8">
            <slot />
        </main>

        <footer class="mt-12 border-t dark:border-zinc-800 py-6 text-center text-sm text-gray-500">
            LogiTrack — Demo data only • Designed for logistics tracking workflows
        </footer>
    </div>
</template>

<script setup>
// layout has no reactive logic; keep simple and reusable
</script>
