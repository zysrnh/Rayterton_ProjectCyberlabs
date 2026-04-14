<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    auth: Object,
    notifications: Object
});

const emit = defineEmits(['toggle-sidebar']);

</script>

<template>
    <header class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-gray-100">
        <div class="flex items-center justify-between px-6 lg:px-10 h-20">
            <button @click="$emit('toggle-sidebar')" class="text-gray-500 focus:outline-none lg:hidden p-2 rounded-xl hover:bg-gray-50 transition-colors">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            
            <div class="flex-1 flex justify-between">
                <div class="flex-1 flex">
                    <!-- Page Heading Slot -->
                    <div class="flex items-center">
                        <slot name="header" />
                    </div>
                </div>
                <div class="ml-4 flex items-center md:ml-6 gap-3">
                    <!-- Command Notifications Hub -->
                    <div class="hidden md:flex items-center gap-2 border-r border-gray-100 pr-4">
                        <!-- Messages Dropdown (Institutional Broadcasts) -->
                        <Dropdown align="right" width="96">
                            <template #trigger>
                                <button class="relative p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-xl transition-all outline-none">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                </button>
                            </template>
                            <template #content>
                                <div class="px-6 py-5 border-b border-gray-50">
                                    <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em] italic">Institutional Broadcasts</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div v-for="ann in notifications.system_announcements" :key="ann.title" class="p-4 bg-indigo-50 border border-indigo-100 rounded-2xl group cursor-pointer hover:bg-indigo-600 transition-all duration-300">
                                        <p class="text-[10px] font-black text-indigo-600 group-hover:text-white uppercase tracking-tight mb-1">{{ ann.title }}</p>
                                        <p class="text-[11px] font-bold text-indigo-400 group-hover:text-indigo-100 leading-relaxed">{{ ann.message }}</p>
                                    </div>
                                </div>
                                <div class="p-4 bg-gray-50 text-center text-[9px] font-black text-gray-400 uppercase tracking-widest border-t border-gray-100">
                                    Official Directives Only
                                </div>
                            </template>
                        </Dropdown>

                        <!-- Bell Dropdown (Task Queue for Admin / Status for Alumni) -->
                        <Dropdown align="right" width="96">
                            <template #trigger>
                                <button class="relative p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-xl transition-all outline-none">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <span v-if="notifications.pending_count > 0 && ['super_admin', 'verifier'].includes(auth.user.role_id)" class="absolute top-2 right-2 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[8px] font-black text-white items-center justify-center">{{ notifications.pending_count }}</span>
                                    </span>
                                </button>
                            </template>
                            <template #content>
                                <!-- Header for Admin -->
                                <template v-if="['super_admin', 'verifier'].includes(auth.user.role_id)">
                                    <div class="px-6 py-5 border-b border-gray-50">
                                        <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em] italic">Registry Activity Hub</h3>
                                    </div>
                                    <div class="max-h-96 overflow-y-auto">
                                        <div v-if="!notifications.latest_entries.length" class="p-10 text-center text-[10px] font-black text-gray-300 uppercase italic">
                                            Zero Activity Records
                                        </div>
                                        <div v-else class="divide-y divide-gray-50">
                                            <Link 
                                                v-for="reg in notifications.latest_entries" 
                                                :key="reg.id" 
                                                :href="reg.verification_status === 'pending' ? route('admin.verifications.queue') : route('admin.residents')"
                                                class="block p-5 hover:bg-gray-50 transition-all group text-left"
                                            >
                                                <div class="flex items-center gap-4">
                                                    <div :class="['w-10 h-10 rounded-xl text-white flex items-center justify-center text-xs font-black shadow-lg', reg.verification_status === 'pending' ? 'bg-indigo-600' : 'bg-slate-900']">
                                                        {{ reg.full_name?.charAt(0) || '?' }}
                                                    </div>
                                                    <div class="flex-grow">
                                                        <div class="flex items-center justify-between mb-0.5">
                                                            <p class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ reg.full_name || 'Anonymous Resident' }}</p>
                                                            <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest">{{ new Date(reg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                                                        </div>
                                                        <p v-if="reg.verification_status === 'pending'" class="text-[10px] font-bold text-indigo-500 uppercase tracking-widest">Verification Requested</p>
                                                        <p v-else-if="reg.verification_status === 'unverified'" class="text-[10px] font-bold text-amber-500 uppercase tracking-widest">New Resident Registration</p>
                                                        <p v-else class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">System Status Update</p>
                                                        
                                                        <p class="text-[8px] font-black text-gray-300 uppercase mt-1 tracking-[0.1em]">{{ new Date(reg.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</p>
                                                    </div>
                                                    <div :class="['w-1.5 h-1.5 rounded-full shadow-lg', reg.verification_status === 'pending' ? 'bg-indigo-500 shadow-indigo-200 animate-pulse' : 'bg-amber-400 shadow-amber-200']"></div>
                                                </div>
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2">
                                        <Link :href="route('admin.verifications.queue')" class="block p-4 bg-indigo-600 text-center text-[9px] font-black text-white uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all">
                                            Audit Ledger
                                        </Link>
                                        <Link :href="route('admin.residents')" class="block p-4 bg-gray-900 text-center text-[9px] font-black text-white uppercase tracking-[0.2em] hover:bg-black transition-all">
                                            Manage Residents
                                        </Link>
                                    </div>
                                </template>

                                <!-- Header for Alumni -->
                                <template v-else>
                                     <div class="px-6 py-5 border-b border-gray-50">
                                        <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em] italic">Registry Status Feed</h3>
                                    </div>
                                    <div class="p-8 text-center text-left">
                                        <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                             <svg class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] italic">No New Status Updates</p>
                                    </div>
                                </template>
                            </template>
                        </Dropdown>
                    </div>

                    <Dropdown align="right" width="64">
                        <template #trigger>
                            <button type="button" class="group flex items-center p-1 rounded-2xl hover:bg-gray-50 transition-all duration-300 outline-none">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100 group-hover:scale-105 transition-transform">
                                    {{ auth.user.name ? auth.user.name.charAt(0).toUpperCase() : 'U' }}
                                </div>
                                <div class="hidden md:block text-left ml-4 mr-2">
                                    <p class="text-xs font-black text-gray-900 leading-none mb-1">{{ auth.user.name || 'Master Agent' }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ auth.user.role_id.replace('_', ' ') }}</p>
                                </div>
                                <svg class="ml-1 h-4 w-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="px-4 py-3 border-b border-gray-100 mb-1 text-left">
                                 <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Signed in as</p>
                                 <p class="text-xs font-bold text-gray-900 truncate">{{ auth.user.email }}</p>
                            </div>
                            <DropdownLink :href="route('profile.edit')">
                                Account Security
                            </DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button" class="text-rose-600 hover:bg-rose-50">
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </div>
        </div>
    </header>
</template>
