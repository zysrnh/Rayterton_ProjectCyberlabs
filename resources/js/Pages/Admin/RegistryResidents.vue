<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    residents: Array,
    filters: Object
});

const searchQuery = ref(props.filters.search || '');

const filteredResidents = computed(() => {
    if (!searchQuery.value) return props.residents;
    return props.residents.filter(r => 
        r.email.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        r.alumni_profile?.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const getInitials = (user) => {
    const name = user.alumni_profile?.full_name || user.email;
    return name.charAt(0).toUpperCase();
};

const getStatusColor = (status) => {
    return {
        'unverified': 'bg-gray-100 text-gray-400',
        'pending': 'bg-amber-100 text-amber-600',
        'in_review': 'bg-indigo-100 text-indigo-600',
        'verified': 'bg-emerald-100 text-emerald-600',
        'rejected': 'bg-rose-100 text-rose-600',
    }[status] || 'bg-gray-100 text-gray-400';
};

const confirmTermination = (id) => {
    residentToDelete.value = id;
    showDeleteModal.value = true;
};

const executeTermination = () => {
    router.delete(route('admin.users.destroy', residentToDelete.value), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            residentToDelete.value = null;
        }
    });
};

const showDeleteModal = ref(false);
const residentToDelete = ref(null);

</script>

<template>
    <Head title="Registry Residents Tracker" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50/50 min-h-screen font-sans">
            <div class="max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-10 space-y-10">
                
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100 pb-12">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-gray-400 mb-2 uppercase tracking-[0.3em] italic">
                            <span>Guardian Oversight</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-indigo-600">Registry Residents</span>
                        </div>
                        <h2 class="text-5xl font-black text-gray-900 tracking-tighter italic">Resident Asset Tracker</h2>
                        <p class="text-sm font-medium text-gray-500 mt-3 max-w-2xl leading-relaxed">Continuous monitoring of all non-administrative accounts within the Rayterton Ledger.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative group">
                            <input v-model="searchQuery" type="text" placeholder="Trace Resident Identity..." class="w-full sm:w-[480px] bg-white border-gray-200 rounded-3xl text-sm font-bold pl-14 pr-6 py-5 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 shadow-xl shadow-gray-100 transition-all placeholder:text-gray-300" />
                            <div class="absolute left-6 top-5 text-gray-300 group-focus-within:text-indigo-600 transition-colors pointer-events-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Residents Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    <div v-for="user in filteredResidents" :key="user.id" class="bg-white border border-gray-100 rounded-[3rem] p-8 shadow-sm hover:shadow-2xl hover:shadow-gray-200/50 transition-all duration-500 group relative overflow-hidden flex flex-col h-full">
                        
                        <!-- Role Badge (Top Right) -->
                         <div class="absolute top-8 right-8">
                             <span :class="['px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest italic border', user.role_id === 'alumni' ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600']">
                                {{ user.role_id }}
                             </span>
                         </div>

                        <!-- Avatar / Identity -->
                        <div class="flex items-center gap-6 mb-10 mt-4">
                            <div class="w-20 h-20 rounded-[2rem] bg-gray-900 flex items-center justify-center text-white text-2xl font-black italic shadow-2xl group-hover:rotate-6 transition-transform">
                                <img v-if="user.alumni_profile?.avatar_url" :src="`/storage/${user.alumni_profile.avatar_url}`" class="w-full h-full object-cover rounded-[2rem]" />
                                <span v-else>{{ getInitials(user) }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-xl font-black text-gray-900 leading-none mb-2 tracking-tighter truncate">{{ user.alumni_profile?.full_name || 'Anonymous Resident' }}</h3>
                                <p class="text-[10px] font-bold text-gray-400 truncate opacity-60">{{ user.email }}</p>
                            </div>
                        </div>

                        <!-- Status Context -->
                        <div class="space-y-6 flex-grow">
                             <div class="flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-gray-400">
                                 <span>Registry Status</span>
                                 <span :class="['px-3 py-1 rounded-lg', getStatusColor(user.alumni_profile?.verification_status || 'unverified')]">
                                     {{ user.alumni_profile?.verification_status || 'unverified' }}
                                 </span>
                             </div>

                             <div class="p-6 bg-gray-50 rounded-[2rem] space-y-4">
                                 <div class="flex items-center justify-between">
                                     <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Enrolled</span>
                                     <span class="text-[10px] font-mono font-bold text-gray-900">{{ new Date(user.created_at).toLocaleDateString() }}</span>
                                 </div>
                                 <div class="flex items-center justify-between">
                                     <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Intelligence Link</span>
                                     <span v-if="user.alumni_profile" class="text-[10px] font-black text-indigo-600 italic">Established</span>
                                     <span v-else class="text-[10px] font-black text-rose-500 italic">Missing</span>
                                 </div>
                             </div>

                             <!-- Progress Bar (Completeness) -->
                             <div v-if="user.role_id === 'alumni'" class="space-y-2">
                                 <div class="flex items-center justify-between text-[9px] font-black text-gray-500 uppercase tracking-widest px-1">
                                     <span>Digital CV Completeness</span>
                                     <span>{{ user.alumni_profile?.profile_completeness || 0 }}%</span>
                                 </div>
                                 <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                                     <div :style="{ width: (user.alumni_profile?.profile_completeness || 0) + '%' }" :class="['h-full transition-all duration-1000', (user.alumni_profile?.profile_completeness || 0) < 50 ? 'bg-amber-500' : 'bg-indigo-600']"></div>
                                 </div>
                             </div>
                        </div>

                        <!-- Interaction -->
                        <div class="mt-10 pt-8 border-t border-gray-50 flex items-center justify-between">
                            <button @click="confirmTermination(user.id)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 text-gray-400 hover:bg-rose-50 hover:text-rose-600 transition-all border border-transparent hover:border-rose-100 shadow-sm active:scale-90" title="Terminate Account">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            <Link v-if="user.role_id === 'alumni' && user.alumni_profile" :href="route('admin.verifications.queue')" class="px-6 py-3 bg-white border border-gray-100 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-900 hover:bg-black hover:text-white transition-all shadow-sm">
                                Audit Ledger
                            </Link>
                        </div>

                        <!-- Incomplete Badge -->
                        <div v-if="user.role_id === 'alumni' && (!user.alumni_profile || user.alumni_profile.profile_completeness < 20)" class="absolute -bottom-1 -right-1 bg-amber-500 text-white px-6 py-1.5 rounded-tl-3xl text-[9px] font-black uppercase tracking-widest italic shadow-3xl shadow-amber-200">
                             Incomplete Record
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                 <div v-if="!filteredResidents.length" class="flex flex-col items-center justify-center p-32 bg-white rounded-[3rem] border border-gray-50">
                    <div class="w-32 h-32 bg-gray-50 rounded-[2.5rem] flex items-center justify-center mb-8 shadow-inner">
                        <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] italic">No Residents Traceable in Current Scope.</p>
                </div>

            </div>
        </div>
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" maxWidth="lg">
            <div class="p-10 bg-white text-center">
                <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-[2rem] flex items-center justify-center mx-auto mb-6 shadow-inner ring-8 ring-rose-50/50 animate-pulse">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tighter italic mb-4">Registry Liquidation</h3>
                <p class="text-sm font-medium text-gray-500 leading-relaxed mb-10">You are about to move this candidate registry to the <span class="text-rose-600 font-bold">Cemetery Trash Center</span>. This action restricts all access until restoration.</p>
                <div class="flex flex-col gap-3">
                    <button @click="executeTermination" class="w-full py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 transition-all shadow-xl active:scale-95">
                        Liquidate Resident
                    </button>
                    <button @click="showDeleteModal = false" class="w-full py-4 bg-gray-50 text-gray-400 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-gray-100 hover:text-gray-900 transition-all">
                        Cancel Protocol
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
