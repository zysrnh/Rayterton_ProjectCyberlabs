<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
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
                            <input v-model="searchQuery" type="text" placeholder="Trace Resident Identity..." class="w-full sm:w-96 bg-white border-gray-200 rounded-3xl text-sm font-bold pl-14 pr-6 py-5 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 shadow-xl shadow-gray-100 transition-all placeholder:text-gray-300" />
                            <div class="absolute left-6 top-5 text-gray-300 group-focus-within:text-indigo-600 transition-colors">
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
                            <span class="text-[9px] font-black text-gray-300 uppercase italic tracking-widest tracking-widest">Resident ID: {{ user.id.slice(0, 8) }}</span>
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
    </AuthenticatedLayout>
</template>
