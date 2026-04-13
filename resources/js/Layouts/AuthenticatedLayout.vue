<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- Sidebar -->
        <div :class="[showingNavigationDropdown ? 'translate-x-0 ease-out' : '-translate-x-full ease-in', 'fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 shadow-xl transform transition duration-300 lg:translate-x-0 lg:static lg:inset-0 flex flex-col']">
            <!-- Sidebar Header / Logo -->
            <div class="flex items-center justify-center h-16 bg-slate-900 border-b border-slate-800 px-4 shrink-0">
                <Link :href="route('dashboard')" class="flex items-center gap-3 w-full pl-2">
                    <ApplicationLogo class="block h-8 w-auto fill-current text-indigo-500" />
                    <span class="text-white font-bold text-xl tracking-wide">Rayterton</span>
                </Link>
            </div>

            <!-- Sidebar Links -->
            <nav class="flex-1 mt-6 px-3 space-y-2 overflow-y-auto">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
                
                <!-- Alumni Links -->
                <template v-if="$page.props.auth.user.role_id === 'alumni'">
                    <Link 
                        :href="route('dashboard')" 
                        :class="[route().current('dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white', 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200']"
                    >
                        <svg :class="[route().current('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white', 'mr-3 shrink-0 h-5 w-5 transition-colors duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Dashboard Alumni
                    </Link>
                </template>

                <!-- Admin/Verifier Links -->
                <template v-if="['super_admin', 'verifier'].includes($page.props.auth.user.role_id)">
                    <Link 
                        :href="route('admin.dashboard')" 
                        :class="[route().current('admin.dashboard') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white', 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200']"
                    >
                        <svg :class="[route().current('admin.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white', 'mr-3 shrink-0 h-5 w-5 transition-colors duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Admin Dashboard
                    </Link>
                    <Link 
                        :href="route('admin.verifications.queue')" 
                        :class="[route().current('admin.verifications.queue') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white', 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200']"
                    >
                        <svg :class="[route().current('admin.verifications.queue') ? 'text-white' : 'text-slate-400 group-hover:text-white', 'mr-3 shrink-0 h-5 w-5 transition-colors duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Verification Queue
                    </Link>
                    <Link 
                        v-if="$page.props.auth.user.role_id === 'super_admin'"
                        :href="route('admin.users.index')" 
                        :class="[route().current('admin.users.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white', 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200']"
                    >
                        <svg :class="[route().current('admin.users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white', 'mr-3 shrink-0 h-5 w-5 transition-colors duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Manage Accounts
                    </Link>
                </template>
                
                <Link 
                    :href="route('profile.edit')" 
                    :class="[route().current('profile.edit') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white', 'group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 mt-4']"
                >
                    <svg :class="[route().current('profile.edit') ? 'text-white' : 'text-slate-400 group-hover:text-white', 'mr-3 shrink-0 h-5 w-5 transition-colors duration-200']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile Settings
                </Link>
            </nav>
            
            <!-- Sidebar Footer -->
            <div class="p-4 border-t border-slate-800 shrink-0">
                <Link :href="route('logout')" method="post" as="button" class="flex items-center w-full px-3 py-2.5 text-sm font-medium text-slate-400 rounded-lg hover:bg-rose-500/10 hover:text-rose-500 transition-colors duration-200 group">
                    <svg class="mr-3 shrink-0 h-5 w-5 text-slate-500 group-hover:text-rose-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Log out
                </Link>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="text-gray-500 focus:outline-none lg:hidden">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <div class="flex-1 px-4 flex justify-between">
                        <div class="flex-1 flex">
                            <!-- Page Heading -->
                            <div class="flex items-center" v-if="$slots.header">
                                <slot name="header" />
                            </div>
                        </div>
                        <div class="ml-4 flex items-center md:ml-6">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button type="button" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-800 font-bold mr-2">
                                            {{ $page.props.auth.user.name ? $page.props.auth.user.name.charAt(0).toUpperCase() : 'U' }}
                                        </div>
                                        <span class="hidden md:inline">{{ $page.props.auth.user.name || $page.props.auth.user.email }}</span>
                                        <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">
                                        Account Security
                                    </DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">
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
        <div v-if="showingNavigationDropdown" @click="showingNavigationDropdown = false" class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"></div>
    </div>
</template>
