<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Sidebar from '@/Components/Sidebar.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Component -->
        <Sidebar :showing="showingNavigationDropdown" @close="showingNavigationDropdown = false" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-20 border-b border-gray-100">
                <div class="flex items-center justify-between px-6 lg:px-10 h-20">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="text-gray-500 focus:outline-none lg:hidden p-2 rounded-xl hover:bg-gray-50 transition-colors">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="flex-1 flex justify-between">
                        <div class="flex-1 flex">
                            <!-- Page Heading -->
                            <div class="flex items-center" v-if="$slots.header">
                                <slot name="header" />
                            </div>
                        </div>
                        <div class="ml-4 flex items-center md:ml-6 gap-6">
                            <!-- Command Notifications -->
                            <div class="hidden md:flex items-center gap-4 border-r border-gray-100 pr-6">
                                <button class="relative p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-xl transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                </button>
                                <button class="relative p-2.5 text-gray-400 hover:text-indigo-600 hover:bg-gray-50 rounded-xl transition-all">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <!-- Dynamic Badge for Registry -->
                                    <span v-if="$page.props.notifications.pending_count > 0" class="absolute top-2 right-2 flex h-4 w-4">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[8px] font-black text-white items-center justify-center">{{ $page.props.notifications.pending_count }}</span>
                                    </span>
                                </button>
                            </div>

                            <Dropdown align="right" width="56">
                                <template #trigger>
                                    <button type="button" class="group flex items-center p-1 rounded-2xl hover:bg-gray-50 transition-all duration-300">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-indigo-100 group-hover:scale-105 transition-transform">
                                            {{ $page.props.auth.user.name ? $page.props.auth.user.name.charAt(0).toUpperCase() : 'U' }}
                                        </div>
                                        <div class="hidden md:block text-left ml-4 mr-2">
                                            <p class="text-xs font-black text-gray-900 leading-none mb-1">{{ $page.props.auth.user.name || 'Master Agent' }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ $page.props.auth.user.role_id.replace('_', ' ') }}</p>
                                        </div>
                                        <svg class="ml-1 h-4 w-4 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="px-4 py-3 border-b border-gray-100 mb-1">
                                         <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Signed in as</p>
                                         <p class="text-xs font-bold text-gray-900 truncate">{{ $page.props.auth.user.email }}</p>
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

            <!-- Page Content -->
            <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
                <slot />
            </main>
        </div>
        
        <!-- Overlay -->
        <div v-if="showingNavigationDropdown" @click="showingNavigationDropdown = false" class="fixed inset-0 z-20 bg-gray-900/60 backdrop-blur-sm lg:hidden transition-all duration-300"></div>
    </div>
</template>
