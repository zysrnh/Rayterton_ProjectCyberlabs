<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    trashed: Array
});

const restore = (id) => {
    if (confirm('Initiate Rescue Protocol? This will restore the resident registry to its previous state.')) {
        router.post(route('admin.users.restore', id), {}, {
            onSuccess: () => alert('Governance Action: Resident Restored.')
        });
    }
};

const purge = (id) => {
    if (confirm('ULTIMATE WARNING: Purging will permanently remove this identity from the Rayterton Ledger. This action is IMMUTABLE. Proceed?')) {
        router.delete(route('admin.users.purge', id), {
            onSuccess: () => alert('Registry Asset Permanently Purged.')
        });
    }
};
</script>

<template>
    <Head title="Governance Trash Center" />

    <AuthenticatedLayout>
        <div class="py-12 bg-gray-50/30 min-h-screen">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                
                <!-- Header -->
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-rose-500 mb-2 uppercase tracking-[0.3em]">
                            <span>Sentinel Security</span>
                            <span class="text-gray-300">/</span>
                            <span>Isolated Trash Center</span>
                        </div>
                        <h2 class="text-5xl font-black text-gray-900 tracking-tighter italic">Registry Cemetery</h2>
                        <p class="text-sm text-gray-500 mt-3 font-medium">Review and manage liquidated maritime assets before final record expulsion.</p>
                    </div>
                    <Link :href="route('admin.residents')" class="px-7 py-3.5 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl hover:bg-black transition-all active:scale-95">
                        Back to Registry
                    </Link>
                </div>

                <!-- Trash Matrix -->
                <div v-if="trashed.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="user in trashed" :key="user.id" class="bg-white border-2 border-dashed border-gray-100 rounded-[2.5rem] p-8 hover:border-gray-300 transition-all group relative overflow-hidden">
                        
                        <!-- Liquidation Date -->
                        <div class="absolute top-8 right-8 text-right">
                            <p class="text-[8px] font-black text-gray-300 uppercase tracking-widest leading-none mb-1">Liquidated At</p>
                            <p class="text-[10px] font-mono font-bold text-gray-400">{{ new Date(user.deleted_at).toLocaleDateString() }}</p>
                        </div>

                        <!-- User Info -->
                        <div class="flex items-center gap-5 mb-8">
                            <div class="w-16 h-16 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-300 text-xl font-black opacity-50 grayscale group-hover:grayscale-0 transition-all duration-700">
                                <img v-if="user.alumni_profile?.avatar_url" :src="`/storage/${user.alumni_profile.avatar_url}`" class="w-full h-full object-cover rounded-2xl" />
                                <span v-else>{{ (user.alumni_profile?.full_name || user.email).charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-black text-gray-600 truncate tracking-tight">{{ user.alumni_profile?.full_name || 'Anonymous Resident' }}</h3>
                                <p class="text-[10px] font-bold text-gray-400 truncate">{{ user.email }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mb-8">
                            <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 inline-block">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic">Identity UUID: {{ user.id.slice(0, 8) }}...</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3">
                            <button @click="restore(user.id)" class="flex-grow py-4 bg-emerald-50 text-emerald-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center justify-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Restore Profile
                            </button>
                            <button @click="purge(user.id)" class="w-14 h-14 bg-white border border-rose-100 text-rose-500 rounded-2xl flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-90" title="Purge Permanently">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center p-40 bg-white border-4 border-dashed border-gray-100 rounded-[4rem]">
                    <div class="w-24 h-24 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6 text-gray-200">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <p class="text-[11px] font-black text-gray-400 uppercase tracking-[0.4em] italic mb-2">Trash Center Vacuumed.</p>
                    <p class="text-xs text-gray-300 font-medium">No liquidated records found in current governance scope.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
