<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    queue: Array
});

const selectedProfile = ref(null);
const showSlideOver = ref(false);
const searchQuery = ref('');
const statusFilter = ref('pending');

// Deep Filters
const rankFilter = ref('');
const regionFilter = ref('');
const certTypeFilter = ref(''); // NEW: Filter by certificate type

const currentPreview = ref(null);
const showPreviewModal = ref(false);

const ranks = computed(() => [...new Set(props.queue?.map(p => p.rank))].filter(Boolean));
const regions = computed(() => [...new Set(props.queue?.map(p => p.region))].filter(Boolean));
const certTypes = ['BST', 'COP', 'COC'];

const filteredQueue = computed(() => {
    if (!props.queue) return [];
    
    return props.queue.filter(p => {
        const matchesSearch = !searchQuery.value || 
            p.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            p.alumni_code?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            p.rank?.toLowerCase().includes(searchQuery.value.toLowerCase());
            
        const matchesStatus = statusFilter.value === 'all' || p.verification_status === statusFilter.value;
        const matchesRank = !rankFilter.value || p.rank === rankFilter.value;
        const matchesRegion = !regionFilter.value || p.region === regionFilter.value;
        
        // Check if has specific cert type
        const matchesCertType = !certTypeFilter.value || 
            p.certificates?.some(c => c.cert_type === certTypeFilter.value);
        
        return matchesSearch && matchesStatus && matchesRank && matchesRegion && matchesCertType;
    });
});

const reviewProfile = (profile) => {
    selectedProfile.value = profile;
    showSlideOver.value = true;
};

const previewFile = (url) => {
    currentPreview.value = url.startsWith('http') ? url : `/storage/${url}`;
    showPreviewModal.value = true;
};

const markAsReviewing = () => {
    if (selectedProfile.value && selectedProfile.value.verification_status === 'pending') {
        router.post(route('admin.verifications.in_review', selectedProfile.value.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                selectedProfile.value.verification_status = 'in_review';
            }
        });
    }
};

const approve = () => {
    if(selectedProfile.value) {
        router.post(route('admin.verifications.approve', selectedProfile.value.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                showSlideOver.value = false;
                selectedProfile.value = null;
            }
        });
    }
};

const stats = computed(() => {
    return {
        pending: props.queue.filter(p => p.verification_status === 'pending').length,
        in_review: props.queue.filter(p => p.verification_status === 'in_review').length,
        verified: props.queue.filter(p => p.verification_status === 'verified').length,
    };
});

const isImage = (url) => {
    if (!url) return false;
    const ext = url.split('.').pop().toLowerCase();
    return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
};

</script>

<template>
    <Head title="Registry Control Center" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50/50 min-h-screen">
            <div class="max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                <!-- Enhanced Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100 pb-12">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-gray-400 mb-2 uppercase tracking-[0.3em] italic">
                            <span>Institutional Command</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-indigo-600">Master Registry Control</span>
                        </div>
                        <h2 class="text-5xl font-black text-gray-900 tracking-tighter">Master Registry Intelligence</h2>
                        <p class="text-sm font-medium text-gray-500 mt-3 max-w-2xl leading-relaxed">Cross-platform digital ledger verification for maritime professional assets. Ensuring institutional compliance and candidate readiness.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative group">
                            <input v-model="searchQuery" type="text" placeholder="Global Unified Search..." class="w-full sm:w-96 bg-white border-gray-200 rounded-3xl text-sm font-bold pl-14 pr-6 py-5 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 shadow-xl shadow-gray-100 transition-all placeholder:text-gray-300" />
                            <div class="absolute left-6 top-5 text-gray-300 group-focus-within:text-indigo-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
                    <!-- Dashboard Control Sidebar -->
                    <div class="xl:col-span-3 space-y-8">
                        
                        <!-- Status Pipeline (Main Filter) -->
                        <div class="bg-gray-900 rounded-[2.5rem] p-8 shadow-2xl text-white">
                            <h3 class="text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-6">Status Pipeline</h3>
                            <div class="flex flex-col gap-3">
                                <button @click="statusFilter = 'all'" :class="['px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all', statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-400/20' : 'bg-white/5 text-gray-400 hover:bg-white/10']">
                                    All Registry ({{ props.queue?.length || 0 }})
                                </button>
                                <button @click="statusFilter = 'pending'" :class="['px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all', statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-xl shadow-amber-400/20' : 'bg-white/5 text-gray-400 hover:bg-white/10']">
                                    Queue: Pending ({{ stats.pending }})
                                </button>
                                <button @click="statusFilter = 'in_review'" :class="['px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all', statusFilter === 'in_review' ? 'bg-indigo-400 text-white shadow-xl shadow-indigo-300/20' : 'bg-white/5 text-gray-400 hover:bg-white/10']">
                                    Queue: Screening ({{ stats.in_review }})
                                </button>
                                <button @click="statusFilter = 'verified'" :class="['px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all', statusFilter === 'verified' ? 'bg-emerald-500 text-white shadow-xl shadow-emerald-400/20' : 'bg-white/5 text-gray-400 hover:bg-white/10']">
                                    Verified Ledger ({{ stats.verified }})
                                </button>
                            </div>
                        </div>

                        <!-- Enhanced Deep Filters -->
                        <div class="bg-white rounded-[2.5rem] border border-gray-200 p-8 shadow-sm">
                            <div class="flex items-center justify-between mb-8">
                                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Registry Intelligence</h3>
                                <button @click="rankFilter = ''; regionFilter = ''; certTypeFilter = ''" class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Clear</button>
                            </div>
                            <div class="space-y-8">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-3 tracking-widest">Filter by Expertise</label>
                                    <select v-model="rankFilter" class="w-full bg-gray-50 border-transparent rounded-2xl text-xs font-bold px-5 py-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 focus:bg-white transition-all">
                                        <option value="">Functional: All Ranks</option>
                                        <option v-for="r in ranks" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-3 tracking-widest">Filter by Operation Region</label>
                                    <select v-model="regionFilter" class="w-full bg-gray-50 border-transparent rounded-2xl text-xs font-bold px-5 py-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 focus:bg-white transition-all">
                                        <option value="">Geographic: All Regions</option>
                                        <option v-for="reg in regions" :key="reg" :value="reg">{{ reg }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-3 tracking-widest">Vault Evidence Search</label>
                                    <select v-model="certTypeFilter" class="w-full bg-gray-50 border-transparent rounded-2xl text-xs font-bold px-5 py-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 focus:bg-white transition-all">
                                        <option value="">Document: BST / COP / COC</option>
                                        <option v-for="ct in certTypes" :key="ct" :value="ct">Holders of {{ ct }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Institutional Branding -->
                        <div class="bg-indigo-600 rounded-[2rem] p-8 shadow-2xl text-white relative overflow-hidden group">
                             <div class="absolute -right-10 -bottom-10 opacity-10 group-hover:scale-110 transition-transform duration-700">
                                <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 12.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                            </div>
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] italic mb-4 block">Unified Ledger Control</span>
                            <p class="text-[10px] font-bold leading-relaxed opacity-80 uppercase tracking-tighter">Verified profiles are recorded as immutable proof of maritime readiness.</p>
                        </div>
                    </div>

                    <!-- Main Registry List -->
                    <div class="xl:col-span-9 space-y-6">
                         <div class="bg-white border border-gray-200 rounded-[3rem] overflow-hidden shadow-sm min-h-[800px] flex flex-col">
                            <div class="px-12 py-8 border-b border-gray-100 flex items-center justify-between bg-gray-50/20">
                                <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] italic">Active Registry Records</h3>
                                <div class="flex items-center gap-4">
                                    <div class="px-5 py-2 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-gray-100">
                                        {{ filteredQueue.length }} Verified Matches
                                    </div>
                                </div>
                            </div>

                            <div v-if="!filteredQueue.length" class="flex-grow flex flex-col items-center justify-center p-24">
                                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic">No matching registry items in current scope.</p>
                            </div>

                            <div v-else class="overflow-x-auto overflow-y-auto max-h-[1000px]">
                                <table class="w-full text-left border-collapse">
                                    <thead class="sticky top-0 z-10 bg-white shadow-sm shadow-gray-50">
                                        <tr class="bg-gray-50/50 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                            <th class="px-12 py-8">Maritime Agent</th>
                                            <th class="px-12 py-8">Role & Ops</th>
                                            <th class="px-12 py-8">Status Control</th>
                                            <th class="px-12 py-8 text-right">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="profile in filteredQueue" :key="profile.id" class="hover:bg-gray-50/40 transition-all group">
                                            <td class="px-12 py-10">
                                                <div class="flex items-center gap-8">
                                                    <div class="flex-shrink-0 w-20 h-20 rounded-[2rem] overflow-hidden shadow-2xl border-4 border-white group-hover:scale-105 transition-transform duration-500">
                                                        <img v-if="profile.avatar_url" :src="`/storage/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                                        <div v-else class="w-full h-full bg-gray-900 flex items-center justify-center text-white font-black text-2xl italic">{{ profile.full_name?.charAt(0) }}</div>
                                                    </div>
                                                    <div>
                                                        <p class="text-xl font-black text-gray-900 leading-none mb-2 tracking-tight">{{ profile.full_name }}</p>
                                                        <div class="flex items-center gap-3">
                                                            <span class="text-[9px] font-mono font-bold text-indigo-500 uppercase tracking-[0.2em] bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100 italic">{{ profile.alumni_code }}</span>
                                                            <span v-if="profile.certificates?.length" class="text-[8px] font-black text-gray-400 uppercase tracking-widest pl-2 border-l border-gray-200">{{ profile.certificates.length }} Evidence Locked</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-12 py-10">
                                                <div class="flex flex-col">
                                                    <p class="text-xs font-black text-gray-800 uppercase italic tracking-tighter">{{ profile.rank }}</p>
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1.5">{{ profile.region }}</p>
                                                </div>
                                            </td>
                                            <td class="px-12 py-10">
                                                 <div v-if="profile.verification_status === 'pending'" class="flex items-center gap-3 bg-amber-50 text-amber-700 px-5 py-2.5 rounded-2xl w-fit border border-amber-100 shadow-sm">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span class="text-[10px] font-black uppercase tracking-widest">Pending</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'in_review'" class="flex items-center gap-3 bg-indigo-600 text-white px-5 py-2.5 rounded-2xl w-fit shadow-xl shadow-indigo-100">
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                    <span class="text-[10px] font-black uppercase tracking-widest italic">Screening</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'verified'" class="flex items-center gap-3 bg-emerald-50 text-emerald-700 px-5 py-2.5 rounded-2xl w-fit border border-emerald-100">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                    <span class="text-[10px] font-black uppercase tracking-widest">Registry Verified</span>
                                                </div>
                                            </td>
                                            <td class="px-12 py-10 text-right">
                                                <button @click="reviewProfile(profile)" class="px-8 py-4 bg-white border border-gray-200 hover:border-gray-900 text-gray-900 text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all hover:bg-gray-900 hover:text-white shadow-sm active:scale-95 shadow-gray-50">
                                                    Initiate Audit
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                             </div>
                         </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Slide Over Panel -->
        <SlideOver :show="showSlideOver" @close="showSlideOver = false" title="Master Registry Audit Intelligence">
            <div v-if="selectedProfile" class="flex flex-col h-full space-y-12 pb-24">
                
                <div class="flex items-center gap-8 p-10 bg-gray-900 rounded-[3rem] text-white mx-6 mt-4 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.3)] relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 opacity-10 group-hover:scale-110 transition-transform duration-1000">
                        <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    </div>
                    <div class="w-28 h-28 rounded-[2rem] overflow-hidden border-4 border-white/10 shadow-2xl flex-shrink-0 cursor-pointer hover:scale-105 transition-all relative z-10" @click="selectedProfile.avatar_url ? previewFile(selectedProfile.avatar_url) : null">
                        <img v-if="selectedProfile.avatar_url" :src="`/storage/${selectedProfile.avatar_url}`" class="w-full h-full object-cover shadow-inner" />
                        <div v-else class="w-full h-full bg-white/5 flex items-center justify-center font-black text-3xl text-white/40 italic">{{ selectedProfile.full_name?.charAt(0) }}</div>
                    </div>
                    <div class="z-10 flex-grow">
                        <p class="text-3xl font-black leading-tight tracking-tighter mb-2 italic">{{ selectedProfile.full_name }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-mono font-bold text-indigo-400 uppercase tracking-[0.2em] bg-white/5 px-3 py-1 rounded-xl border border-white/5">ID: {{ selectedProfile.alumni_code }}</span>
                            <span v-if="selectedProfile.verification_status === 'verified'" class="text-[9px] font-black text-emerald-400 uppercase tracking-widest pl-2">Authenticated</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-12 px-10">
                    <!-- Identity Vault -->
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-6 italic border-b border-gray-100 pb-2 flex items-center gap-3">
                             Vault: Identity Evidence
                        </h3>
                        <div class="flex items-center justify-between p-6 bg-white border border-gray-100 shadow-xl shadow-gray-100/50 rounded-[2rem] hover:border-indigo-300 transition-all group">
                            <div class="flex items-center gap-5">
                                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-900 uppercase italic">Primary Passport Photo</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">National Standard JPEG</p>
                                </div>
                            </div>
                             <button v-if="selectedProfile.avatar_url" @click="previewFile(selectedProfile.avatar_url)" class="px-6 py-3 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all flex-shrink-0 shadow-lg shadow-gray-200">
                                Open Asset
                            </button>
                            <span v-else class="text-[9px] font-bold text-gray-300 uppercase italic">Null Entry</span>
                        </div>
                    </div>

                    <div class="pt-8">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-8 italic border-b border-gray-100 pb-2">Vault: Credential Assets</h3>
                        <div class="space-y-10">
                            <!-- Education -->
                            <div v-if="selectedProfile.educations?.length">
                                <h4 class="text-[10px] font-black text-indigo-600 uppercase mb-5 flex items-center tracking-widest">
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 mr-3 shadow-xl"></span> Academic Evidence Pipeline
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="edu in selectedProfile.educations" :key="edu.id" class="flex items-center justify-between p-6 bg-white border border-gray-100 shadow-lg shadow-gray-100/30 rounded-[2rem] hover:border-indigo-100 transition-all">
                                        <div class="max-w-[70%]">
                                            <p class="text-xs font-black text-gray-900 truncate uppercase tracking-tight">{{ edu.institution_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">{{ edu.degree_program }} &bull; Class of {{ edu.graduation_year }}</p>
                                        </div>
                                        <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="px-6 py-3 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all flex-shrink-0">
                                            Audit Asset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Vault (Certificates) -->
                            <div v-if="selectedProfile.certificates?.length">
                                <h4 class="text-[10px] font-black text-amber-600 uppercase mb-5 flex items-center tracking-widest">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 mr-3 shadow-xl"></span> STCW / COC / COP Certificate Vault
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="cert in selectedProfile.certificates" :key="cert.id" class="flex items-center justify-between p-6 bg-white border border-gray-100 shadow-lg shadow-gray-100/30 rounded-[2rem] hover:border-amber-100 transition-all">
                                        <div class="max-w-[70%] text-left">
                                            <div class="flex items-center gap-2 mb-1.5">
                                                 <span class="text-[8px] font-black px-1.5 py-0.5 rounded-lg bg-gray-900 text-white uppercase">{{ cert.cert_type }}</span>
                                                 <p class="text-xs font-black text-gray-900 truncate uppercase tracking-tight">{{ cert.cert_name }}</p>
                                            </div>
                                            <p class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-widest italic">NO: {{ cert.cert_number }}</p>
                                        </div>
                                        <button v-if="cert.cert_file_url" @click="previewFile(cert.cert_file_url)" class="px-6 py-3 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 transition-all flex-shrink-0">
                                            Audit Asset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sea Service -->
                            <div v-if="selectedProfile.sea_services?.length">
                                <h4 class="text-[10px] font-black text-emerald-600 uppercase mb-5 flex items-center tracking-widest">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-3 shadow-xl"></span> Verified Sea Service Ledger
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="sea in selectedProfile.sea_services" :key="sea.id" class="flex items-center justify-between p-6 bg-white border border-gray-100 shadow-lg shadow-gray-100/30 rounded-[2rem] hover:border-emerald-100 transition-all">
                                        <div class="max-w-[70%]">
                                            <p class="text-xs font-black text-gray-900 truncate uppercase tracking-tight italic">{{ sea.vessel_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-widest">{{ sea.duration_months }} Months Operational &bull; {{ sea.route }}</p>
                                        </div>
                                        <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="px-6 py-3 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all flex-shrink-0 font-black">
                                            Audit Asset
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-16 mt-12 pb-20">
                        <div class="flex flex-col gap-5">
                             <template v-if="selectedProfile.verification_status !== 'verified'">
                                <button 
                                    v-if="selectedProfile.verification_status === 'pending'" 
                                    @click="markAsReviewing" 
                                    class="w-full px-8 py-6 bg-white border-2 border-indigo-200 hover:border-indigo-600 text-indigo-700 text-[11px] font-black uppercase tracking-[0.3em] rounded-3xl shadow-2xl transition-all shadow-indigo-100 italic"
                                >
                                    Initiate Screening Protocol
                                </button>
                                <div v-else class="bg-indigo-600 text-white px-8 py-6 rounded-3xl text-center text-[11px] font-black uppercase tracking-[0.3em] shadow-[0_20px_40px_-10px_rgba(79,70,229,0.3)] italic">
                                    Audit In Progress
                                </div>
                                <button @click="approve" class="w-full px-8 py-6 bg-gray-900 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.3em] rounded-3xl shadow-2xl transition-all active:scale-95 italic">
                                    Authenticate & Issue Ledger Badge
                                </button>
                            </template>
                            <div v-else class="bg-emerald-600 text-white px-8 py-6 rounded-3xl text-center text-[11px] font-black uppercase tracking-[0.3em] shadow-xl flex items-center justify-center gap-4 italic">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Authenticated: Immutable Registry Record
                            </div>
                            <button @click="showSlideOver = false" class="w-full px-6 py-5 bg-gray-50 border border-transparent hover:bg-gray-200 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-3xl transition-all">
                                Terminate Audit View
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </SlideOver>

        <!-- Proof Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-white rounded-[3rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                 <div class="px-12 py-8 border-b border-gray-100 flex items-center justify-between bg-gray-900 text-white">
                    <div class="flex items-center gap-4">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] italic">Registry Asset Inspection</h3>
                    </div>
                    <button @close="showPreviewModal = false" @click="showPreviewModal = false" class="w-12 h-12 rounded-full bg-white/10 flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all shadow-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-12 flex-grow overflow-y-auto flex items-center justify-center bg-gray-950/5">
                    <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full h-auto rounded-[2rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.3)] border-[12px] border-white" />
                    <iframe v-else :src="currentPreview" class="w-full h-[70vh] rounded-3xl border border-gray-100 shadow-2xl bg-white" frameborder="0"></iframe>
                </div>
                <div class="p-10 bg-white border-t border-gray-100 flex justify-end">
                    <button @click="showPreviewModal = false" class="px-12 py-5 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-2xl shadow-gray-300 hover:scale-105 transition-all italic">
                        Confirm Evidence View
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
