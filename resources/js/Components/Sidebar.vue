<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    showing: Boolean
});

defineEmits(['close']);
</script>

<template>
    <div :class="[showing ? 'translate-x-0 ease-out' : '-translate-x-full ease-in', 'fixed inset-y-0 left-0 z-30 w-72 bg-[#0F172A] shadow-2xl transform transition duration-300 lg:translate-x-0 lg:static lg:inset-0 flex flex-col']">
        <!-- Sidebar Header / Logo -->
        <div class="flex items-center justify-center h-24 bg-[#0F172A] border-b border-white/5 px-8 shrink-0">
            <Link :href="route('dashboard')" class="flex items-center gap-4 w-full">
                <img src="/images/Logo/rayterton-apps-software-logo.png" class="h-14 w-auto object-contain" alt="Rayterton Logo" />
            </Link>
        </div>

        <!-- Sidebar Links -->
        <nav class="flex-1 mt-10 px-6 space-y-3 overflow-y-auto">
            <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Institutional Console</p>
            
            <!-- Alumni Links -->
            <template v-if="$page.props.auth.user.role_id === 'alumni'">
                <Link 
                    :href="route('dashboard')" 
                    :class="[route().current('dashboard') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard Alumni
                </Link>
            </template>

            <!-- Admin/Verifier Links -->
            <template v-if="['super_admin', 'verifier'].includes($page.props.auth.user.role_id)">
                <Link 
                    :href="route('admin.dashboard')" 
                    :class="[route().current('admin.dashboard') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Master Insight
                </Link>
                <Link 
                    :href="route('admin.verifications.queue')" 
                    :class="[route().current('admin.verifications.queue') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="flex-grow">Registry Control</span>
                    <span v-if="$page.props.notifications.pending_count > 0" class="ml-2 inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-black text-white bg-indigo-500 rounded-lg shadow-lg shadow-indigo-500/40 transform group-hover:scale-110 transition-transform">
                        {{ $page.props.notifications.pending_count }}
                    </span>
                </Link>
                <Link 
                    v-if="$page.props.auth.user.role_id === 'super_admin'"
                    :href="route('admin.users.index')" 
                    :class="[route().current('admin.users.*') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    System Guardians
                </Link>
                <Link 
                    :href="route('admin.residents')" 
                    :class="[route().current('admin.residents') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Registry Residents
                </Link>
            </template>
            
            <div class="pt-10">
                 <p class="px-4 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Global Preferences</p>
                 <Link 
                    :href="route('profile.edit')" 
                    :class="[route().current('profile.edit') ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-500/20' : 'text-slate-400 hover:bg-white/5 hover:text-white', 'group flex items-center px-4 py-3.5 text-xs font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
                >
                    <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Registry Security
                </Link>
            </div>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="p-8 border-t border-white/5 shrink-0 space-y-2">
            <!-- Trash Hub (Only for Super Admin) -->
            <Link 
                v-if="$page.props.auth.user.role_id === 'super_admin'"
                :href="route('admin.trash')" 
                :class="[route().current('admin.trash') ? 'bg-rose-600 text-white shadow-xl shadow-rose-500/20' : 'text-slate-500 hover:bg-rose-500/10 hover:text-rose-500', 'group flex items-center px-5 py-4 text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all duration-300']"
            >
                <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Registry Trash
            </Link>

            <Link :href="route('logout')" method="post" as="button" class="flex items-center w-full px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 rounded-2xl hover:bg-rose-500/10 hover:text-rose-500 transition-all duration-300 group">
                <svg class="mr-4 h-5 w-5 opacity-70 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Exit Control
            </Link>
        </div>
    </div>
</template>
