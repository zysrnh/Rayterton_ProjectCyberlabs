<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

defineProps({
    role: String,
    stats: Object
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-gray-200">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Administrator Control Panel
                            </h2>
                            <p class="text-gray-500 mt-1">Sistem informasi dan verifikasi kepelautan Alumni.</p>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 px-6 flex items-center gap-3">
                            <div class="bg-indigo-600 rounded-full h-10 w-10 flex items-center justify-center text-white font-bold text-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-500 uppercase tracking-wider font-semibold">Active Role</p>
                                <p class="font-bold text-indigo-900 capitalize">{{ user.role_id.replace('_', ' ') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats / Quick Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-500">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Verifications</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.pending || 0 }}</p>
                        <p class="text-xs text-amber-600 mt-2 font-medium">Needs Attention</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-emerald-500">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Verified Alumni</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.verified || 0 }}</p>
                        <p class="text-xs text-emerald-600 mt-2 font-medium">Ready for deployment</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                        <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Active Companies</p>
                        <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats?.companies || 0 }}</p>
                        <p class="text-xs text-blue-600 mt-2 font-medium">Hiring partners</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
