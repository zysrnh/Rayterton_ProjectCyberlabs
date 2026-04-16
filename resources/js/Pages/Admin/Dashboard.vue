<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

defineProps({
    role: String,
    stats: Object,
    recentVerified: Array
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50/50 min-h-screen">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Page Title -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Institutional Governance Control</h1>
                        <p class="text-sm font-medium text-gray-500 mt-1">Real-time oversight of national maritime alumni verification and enterprise engagement.</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Pending Review -->
                    <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm group hover:border-amber-300 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Pending Review</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-lg shadow-amber-200 animate-pulse"></span>
                            </div>
                        </div>
                        <p class="text-5xl font-black text-gray-900 mt-6 tracking-tighter">{{ stats?.pending || 0 }}</p>
                        <p class="text-[10px] font-bold text-amber-600 mt-4 uppercase tracking-widest bg-amber-50 inline-block px-2 py-1 rounded-lg">Operational Priority</p>
                    </div>

                    <!-- Verified Alumni -->
                    <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm group hover:border-emerald-300 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Verified Assets</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center">
                                <div class="bg-emerald-500 rounded-full p-1.5 shadow-lg shadow-emerald-200">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <p class="text-5xl font-black text-gray-900 mt-6 tracking-tighter">{{ stats?.verified || 0 }}</p>
                        <p class="text-[10px] font-bold text-emerald-600 mt-4 uppercase tracking-widest bg-emerald-50 inline-block px-2 py-1 rounded-lg">Marketplace Ready</p>
                    </div>

                    <!-- Hiring Partners -->
                    <div class="bg-gray-900 rounded-3xl p-8 shadow-xl text-white group relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2m-2 10h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"></path></svg>
                        </div>
                        <div class="relative flex flex-col h-full">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-auto">Hiring Partners</span>
                            <p class="text-5xl font-black text-white mt-8 tracking-tighter">{{ stats?.companies || 0 }}</p>
                            <p class="text-[10px] font-bold text-indigo-400 mt-4 uppercase tracking-widest">Active Enterprise</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities Section -->
                <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                    <!-- Recently Verified List -->
                    <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Recently Verified Alumni</h3>
                                <p class="text-xs font-medium text-gray-500 mt-0.5">Alumni who successfully passed the institutional screening process.</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <div v-if="!recentVerified?.length" class="p-12 text-center">
                                <p class="text-xs font-bold text-gray-300 uppercase tracking-[0.2em]">No activities recorded</p>
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="profile in recentVerified" :key="profile.id" class="p-6 rounded-2xl flex items-center justify-between hover:bg-gray-50 transition-all border border-transparent hover:border-gray-100">
                                    <div class="flex items-center gap-6">
                                        <div class="w-14 h-14 rounded-2xl overflow-hidden shadow-sm border border-gray-100">
                                            <img v-if="profile.avatar_url" :src="`/vault/access/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                            <div v-else class="w-full h-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xl">
                                                {{ profile.full_name?.charAt(0) || '?' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <p class="text-sm font-black text-gray-900">{{ profile.full_name }}</p>
                                                <span class="px-2 py-0.5 bg-gray-900 text-white rounded text-[9px] font-mono font-bold tracking-wider">{{ profile.alumni_code }}</span>
                                            </div>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">{{ profile.rank }} &bull; {{ profile.region }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="px-3 py-1 bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-widest border border-emerald-100 rounded-full">Passed National Screening</span>
                                        <p class="text-[9px] font-bold text-gray-300 uppercase italic">Recorded on Registry</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
