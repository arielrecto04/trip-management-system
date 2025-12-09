<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});

// Hardcoded content / demo data
const company = {
    name: 'AeroLogix Transport',
    tagline: 'Reliable. Transparent. On-time.',
    heroText: 'End-to-end logistics and fleet solutions with real-time visibility.',
    phone: '+1 (555) 123-4567',
    email: 'info@aerologix.example',
    address: '125 Logistics Ave, Suite 200, Chicago, IL',
};

const stats = [
    { label: 'Shipments Delivered', value: '12,482' },
    { label: 'On-time Rate', value: '98.4%' },
    { label: 'Active Trucks', value: '214' },
    { label: 'Worldwide Hubs', value: '18' },
];

const services = [
    { title: 'Freight Forwarding', desc: 'Full-service sea, air, and road freight with customs clearance.' },
    { title: 'Real-time Tracking', desc: 'Live updates, geo-fencing and ETA predictions.' },
    { title: 'Warehousing & Fulfillment', desc: 'Secure storage, cross-docking and order fulfillment.' },
    { title: 'Cold Chain Logistics', desc: 'Temperature-controlled solutions for sensitive cargo.' },
];

const fleet = [
    { name: 'Unit 42 — Heavy Truck', status: 'In Transit', eta: '2025-11-02' },
    { name: 'VN-88 — Delivery Van', status: 'Loading', eta: '2025-10-30' },
    { name: 'P-11 — Reefer Truck', status: 'Maintenance', eta: '2025-11-05' },
];

const testimonials = [
    { name: 'J. Morgan', company: 'Retail Co.', quote: 'Exceptional visibility and service — reduced our transit times by 20%.' },
    { name: 'S. Kline', company: 'PharmaCorp', quote: 'Cold chain management that we trust with critical shipments.' },
];

const shipments = ref([
    { tracking: 'ALX-10001', origin: 'Los Angeles, CA', destination: 'Chicago, IL', status: 'In Transit' },
    { tracking: 'ALX-10002', origin: 'Miami, FL', destination: 'New York, NY', status: 'Delivered' },
    { tracking: 'ALX-10003', origin: 'Seattle, WA', destination: 'Denver, CO', status: 'Delayed' },
]);

const query = ref('');
const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return shipments.value;
    return shipments.value.filter(s =>
        s.tracking.toLowerCase().includes(q) ||
        s.origin.toLowerCase().includes(q) ||
        s.destination.toLowerCase().includes(q) ||
        s.status.toLowerCase().includes(q)
    );
});

function openContact() {
    const el = document.getElementById('contact');
    el?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<template>

    <Head title="AeroLogix — Logistics & Tracking" />

    <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 text-gray-800 dark:text-white">
        <!-- NAV -->
        <header class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="h-10 w-10 rounded-md bg-sky-600 flex items-center justify-center text-white font-semibold">
                        AL</div>
                    <div>
                        <div class="font-semibold text-lg">{{ company.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ company.tagline }}</div>
                    </div>
                </div>

                <nav class="flex items-center gap-4">
                    <a href="#services" class="text-sm hover:underline">Services</a>
                    <a href="#fleet" class="text-sm hover:underline">Fleet</a>
                    <a href="#track" class="text-sm hover:underline">Track</a>
                    <a href="#contact" class="text-sm hover:underline">Contact</a>
                    <Link :href="route('login')">
                        <button class="ml-4 px-3 py-2 bg-sky-600 text-white rounded-md text-sm">
                            Login
                        </button>
                    </Link>
                </nav>
            </div>
        </header>

        <!-- HERO -->
        <section class="bg-gradient-to-b from-white to-gray-50 dark:from-zinc-900 dark:to-zinc-900">
            <div class="max-w-7xl mx-auto px-6 py-16 lg:py-24">
                <div class="grid gap-10 lg:grid-cols-2 items-center">
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-extrabold leading-tight">{{ company.name }} — {{
                            company.tagline }}</h1>
                        <p class="mt-4 text-gray-600 dark:text-gray-300 max-w-xl">{{ company.heroText }}</p>

                        <div class="mt-6 flex gap-4">
                            <button @click="openContact"
                                class="px-5 py-3 bg-sky-600 text-white rounded-md shadow">Request a Quote</button>
                            <Link :href="route('dashboard')" class="px-5 py-3 border rounded-md text-sky-600">Client
                            Dashboard</Link>
                        </div>

                        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div v-for="s in stats" :key="s.label"
                                class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-4 rounded shadow-sm">
                                <div class="text-sm text-gray-500">{{ s.label }}</div>
                                <div class="mt-1 text-xl font-semibold">{{ s.value }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="rounded-lg dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 shadow flex flex-col gap-4">
                            <div class="text-sm text-gray-500">Quick Track (demo)</div>
                            <input v-model="query" placeholder="Enter tracking number, origin or destination"
                                class="px-3 py-2 border rounded-md bg-white dark:bg-zinc-900 focus:outline-none" />
                            <div class="space-y-3 mt-2">
                                <div v-for="s in filtered" :key="s.tracking"
                                    class="p-3 rounded border dark:border-zinc-700 flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium">{{ s.tracking }}</div>
                                        <div class="text-xs text-gray-500">{{ s.origin }} → {{ s.destination }}</div>
                                    </div>
                                    <div class="text-sm">
                                        <div
                                            :class="s.status === 'Delivered' ? 'text-green-600' : s.status === 'Delayed' ? 'text-yellow-600' : 'text-blue-600'">
                                            {{ s.status }}</div>
                                    </div>
                                </div>

                                <div v-if="filtered.length === 0" class="text-sm text-gray-500">No shipments found.
                                </div>
                            </div>

                            <div class="pt-3 text-xs text-gray-500">This is a static demo. Integrate live APIs for
                                production tracking.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services -->
        <section id="services" class="max-w-7xl mx-auto px-6 py-14">
            <div class="text-center">
                <h2 class="text-2xl font-semibold">Our Services</h2>
                <p class="mt-2 text-gray-500 dark:text-gray-300">Comprehensive logistics solutions tailored to your
                    business needs.</p>
            </div>

            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="svc in services" :key="svc.title"
                    class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 rounded-lg shadow-sm">
                    <div class="text-lg font-semibold">{{ svc.title }}</div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ svc.desc }}</p>
                </div>
            </div>
        </section>

        <!-- Fleet -->
        <section id="fleet" class="dark:bg-bgcolor-dark bg-bgcolor-light border-t dark:border-zinc-800">
            <div class="max-w-7xl mx-auto px-6 py-14">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold">Fleet & Operations</h3>
                        <p class="mt-1 text-gray-500 dark:text-gray-300">Modern vehicles and optimized routes for safe,
                            efficient delivery.</p>
                    </div>
                    <div class="text-sm text-gray-500">Contact: {{ company.phone }}</div>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="f in fleet" :key="f.name"
                        class="rounded-lg p-4 dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light border dark:border-zinc-700">
                        <div class="font-medium">{{ f.name }}</div>
                        <div class="text-sm text-gray-500 mt-1">Status: <span class="font-semibold">{{ f.status
                                }}</span></div>
                        <div class="text-xs text-gray-400 mt-2">ETA: {{ f.eta }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="max-w-7xl mx-auto px-6 py-14">
            <div class="text-center">
                <h3 class="text-xl font-semibold">What clients say</h3>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div v-for="t in testimonials" :key="t.name" class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 rounded-lg shadow-sm">
                    <div class="text-sm text-gray-600 dark:text-gray-300">“{{ t.quote }}”</div>
                    <div class="mt-4 text-sm font-medium">{{ t.name }} · <span class="text-gray-500">{{ t.company
                            }}</span></div>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section id="contact"
            class="bg-gradient-to-b from-gray-50 to-white dark:from-zinc-900 dark:to-zinc-900 border-t dark:border-zinc-800">
            <div class="max-w-4xl mx-auto px-6 py-14">
                <div class="grid gap-8 lg:grid-cols-2 items-start">
                    <div>
                        <h3 class="text-2xl font-semibold">Get in touch</h3>
                        <p class="mt-2 text-gray-500 dark:text-gray-300">Request a custom quote or ask about our
                            services. Our team will respond within one business day.</p>

                        <div class="mt-6 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <div><strong>Email:</strong> <a class="underline" :href="`mailto:${company.email}`">{{
                                    company.email }}</a></div>
                            <div><strong>Phone:</strong> {{ company.phone }}</div>
                            <div><strong>Address:</strong> {{ company.address }}</div>
                        </div>
                    </div>

                    <div>
                        <form class="dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light p-6 rounded-lg shadow-sm space-y-3"
                            onsubmit="event.preventDefault()">
                            <div>
                                <label class="text-sm">Full name</label>
                                <input class="mt-1 w-full px-3 py-2 border rounded-md bg-white dark:bg-zinc-900"
                                    placeholder="Jane Doe" />
                            </div>

                            <div>
                                <label class="text-sm">Email</label>
                                <input class="mt-1 w-full px-3 py-2 border rounded-md bg-white dark:bg-zinc-900"
                                    placeholder="you@company.com" />
                            </div>

                            <div>
                                <label class="text-sm">Message</label>
                                <textarea class="mt-1 w-full px-3 py-2 border rounded-md bg-white dark:bg-zinc-900"
                                    rows="4" placeholder="Tell us about your shipment or requirement"></textarea>
                            </div>

                            <div class="flex items-center justify-between">
                                <button class="px-4 py-2 bg-sky-600 text-white rounded-md">Send Request</button>
                                <div class="text-xs text-gray-500">Or call us: {{ company.phone }}</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t dark:border-zinc-800 dark:bg-bgcontentcolor-dark bg-bgcontentcolor-light">
            <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="text-sm text-gray-600 dark:text-gray-300">© {{ new Date().getFullYear() }} {{ company.name
                    }}. All rights reserved.</div>
                <div class="text-sm text-gray-500">Laravel v{{ props?.laravelVersion }} (PHP v{{ props?.phpVersion }})
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Minimal visual polish for a professional landing */
</style>
