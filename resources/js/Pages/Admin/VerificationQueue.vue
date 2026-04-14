<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import Modal from '@/Components/Modal.vue';
import CustomSelect from '@/Components/CustomSelect.vue';
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
const certTypeFilter = ref('');

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
        const matchesCertType = !certTypeFilter.value || p.certificates?.some(c => c.cert_type === certTypeFilter.value);
        
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
            <div class="max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-10 space-y-10">
                
                <!-- Enhanced Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100 pb-12">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-gray-400 mb-2 uppercase tracking-[0.3em] italic">
                            <span>Institutional Command</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-indigo-600">Master Registry Control</span>
                        </div>
                        <h2 class="text-5xl font-black text-gray-900 tracking-tighter">Master Registry Intelligence</h2>
                        <p class="text-sm font-medium text-gray-500 mt-3 max-w-2xl leading-relaxed">Cross-platform digital ledger verification for maritime professional assets.</p>
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

                <!-- NEW: Integrated Filter Section (Top Level) -->
                <div class="space-y-8">
                    <!-- Status Tabs -->
                    <div class="flex flex-wrap items-center gap-3">
                         <button @click="statusFilter = 'all'" :class="['px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2', statusFilter === 'all' ? 'bg-indigo-600 border-indigo-600 text-white shadow-xl shadow-indigo-100' : 'bg-white border-transparent text-gray-400 hover:border-gray-200']">
                            All Registry ({{ props.queue?.length || 0 }})
                        </button>
                        <button @click="statusFilter = 'pending'" :class="['px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2', statusFilter === 'pending' ? 'bg-amber-500 border-amber-500 text-white shadow-xl shadow-amber-100' : 'bg-white border-transparent text-gray-400 hover:border-gray-200']">
                            Queue: Pending ({{ stats.pending }})
                        </button>
                        <button @click="statusFilter = 'in_review'" :class="['px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2', statusFilter === 'in_review' ? 'bg-indigo-800 border-indigo-800 text-white shadow-xl shadow-indigo-100' : 'bg-white border-transparent text-gray-400 hover:border-gray-200']">
                            Queue: Screening ({{ stats.in_review }})
                        </button>
                        <button @click="statusFilter = 'verified'" :class="['px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border-2', statusFilter === 'verified' ? 'bg-emerald-600 border-emerald-600 text-white shadow-xl shadow-emerald-100' : 'bg-white border-transparent text-gray-400 hover:border-gray-200']">
                            Verified Ledger ({{ stats.verified }})
                        </button>
                    </div>

                    <!-- Deep Intelligence Filters (Inline) -->
                    <div class="bg-white border border-gray-100 rounded-[2.5rem] p-6 shadow-sm flex flex-wrap items-center gap-8">
                        <div class="flex items-center gap-4 border-r border-gray-100 pr-8 mr-2">
                             <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                             </div>
                             <span class="text-[10px] font-black text-gray-900 uppercase tracking-[0.2em] italic">Deep Insight</span>
                        </div>
                        
                        <div class="flex flex-wrap items-end gap-6 flex-grow">
                            <CustomSelect 
                                v-model="rankFilter" 
                                :options="ranks" 
                                placeholder="Functional: All Ranks" 
                                label="Expertise Search"
                            />
                            <CustomSelect 
                                v-model="regionFilter" 
                                :options="regions" 
                                placeholder="Geography: All Regions" 
                                label="Operational Scope"
                            />
                            <CustomSelect 
                                v-model="certTypeFilter" 
                                :options="certTypes" 
                                placeholder="Evidence: All Vaults" 
                                label="Vault Search"
                            />
                        </div>

                        <button @click="rankFilter = ''; regionFilter = ''; certTypeFilter = ''" class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition-colors mr-2">
                            Reset Filters
                        </button>
                    </div>
                </div>

                <!-- Main Registry List -->
                <div class="bg-white border border-gray-100 rounded-[3rem] overflow-hidden shadow-sm min-h-[700px] flex flex-col">
                    <div class="px-12 py-10 border-b border-gray-100 flex items-center justify-between bg-gray-50/20">
                        <div>
                            <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em] italic">Active Registry Records</h3>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Live from secure maritime ledger</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="px-6 py-3 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-gray-200">
                                {{ filteredQueue.length }} Verified Matches
                            </div>
                        </div>
                    </div>

                    <div v-if="!filteredQueue.length" class="flex-grow flex flex-col items-center justify-center p-32">
                        <div class="w-32 h-32 bg-gray-50 rounded-[2.5rem] flex items-center justify-center mb-8 shadow-inner shadow-gray-100">
                            <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] italic">Scope returned null results.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/30 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em] border-b border-gray-50">
                                    <th class="px-12 py-8">Maritime Agent</th>
                                    <th class="px-12 py-8">Role / Region</th>
                                    <th class="px-12 py-8">Status Control</th>
                                    <th class="px-12 py-8 text-right pr-16">Audit Command</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="profile in filteredQueue" :key="profile.id" class="hover:bg-gray-50/40 transition-all group">
                                    <td class="px-12 py-12">
                                        <div class="flex items-center gap-10">
                                            <div class="flex-shrink-0 w-24 h-24 rounded-[2.5rem] overflow-hidden shadow-2xl border-4 border-white group-hover:scale-105 transition-transform duration-500">
                                                <img v-if="profile.avatar_url" :src="`/storage/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full bg-gray-900 flex items-center justify-center text-white font-black text-3xl italic">{{ profile.full_name?.charAt(0) }}</div>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-2xl font-black text-gray-900 leading-none mb-3 tracking-tighter">{{ profile.full_name }}</p>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-[10px] font-mono font-bold text-indigo-600 uppercase tracking-[0.2em] italic bg-indigo-50 px-3 py-1 rounded-xl">{{ profile.alumni_code }}</span>
                                                    <div class="flex items-center gap-1.5 pl-3 border-l border-gray-100">
                                                         <span class="text-xs">📂</span>
                                                         <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ profile.certificates?.length || 0 }} Vaults</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-12 py-12">
                                        <div class="flex flex-col gap-1.5">
                                            <p class="text-xs font-black text-gray-800 uppercase italic tracking-tight">{{ profile.rank }}</p>
                                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">{{ profile.region }}</p>
                                        </div>
                                    </td>
                                    <td class="px-12 py-12">
                                         <div v-if="profile.verification_status === 'pending'" class="flex items-center gap-3 bg-white border-2 border-amber-100 text-amber-600 px-6 py-3 rounded-2xl w-fit shadow-xl shadow-amber-50">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase tracking-[0.2em] italic">Awaiting Pivot</span>
                                        </div>
                                        <div v-else-if="profile.verification_status === 'in_review'" class="flex items-center gap-3 bg-indigo-900 text-white px-6 py-3 rounded-2xl w-fit shadow-2xl shadow-indigo-100">
                                            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-bounce"></span>
                                            <span class="text-[10px] font-black uppercase tracking-[0.2em] italic">Active Audit</span>
                                        </div>
                                        <div v-else-if="profile.verification_status === 'verified'" class="flex items-center gap-3 bg-emerald-50 text-emerald-700 px-6 py-3 rounded-2xl w-fit border border-emerald-100">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                            <span class="text-[10px] font-black uppercase tracking-[0.2em]">Verified Record</span>
                                        </div>
                                    </td>
                                    <td class="px-12 py-12 text-right pr-16">
                                        <button @click="reviewProfile(profile)" class="px-10 py-5 bg-white border-2 border-gray-100 hover:border-gray-900 text-gray-900 text-[10px] font-black uppercase tracking-[0.3em] rounded-2xl transition-all hover:bg-black hover:text-white shadow-sm active:scale-95 italic">
                                            Launch Audit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Slide Over Panel (Audit) -->
        <SlideOver :show="showSlideOver" @close="showSlideOver = false" title="Institutional Audit Command">
            <div v-if="selectedProfile" class="flex flex-col h-full space-y-12 pb-32">
                
                <!-- Avatar Header -->
                <div class="flex items-center gap-10 p-12 bg-gray-900 rounded-[4rem] text-white mx-6 mt-4 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] relative overflow-hidden group">
                     <div class="absolute -right-10 -top-10 opacity-5 group-hover:scale-125 transition-transform duration-2000">
                        <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 12.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    </div>
                    <div class="w-32 h-32 rounded-[3rem] overflow-hidden border-4 border-white/10 shadow-3xl flex-shrink-0 cursor-pointer hover:rotate-2 hover:scale-105 transition-all relative z-10" @click="selectedProfile.avatar_url ? previewFile(selectedProfile.avatar_url) : null">
                        <img v-if="selectedProfile.avatar_url" :src="`/storage/${selectedProfile.avatar_url}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full bg-white/5 flex items-center justify-center font-black text-4xl text-white/20 italic">{{ selectedProfile.full_name?.charAt(0) }}</div>
                    </div>
                    <div class="z-10 flex-grow">
                        <h4 class="text-4xl font-black leading-none tracking-tighter mb-4 italic">{{ selectedProfile.full_name }}</h4>
                        <div class="flex items-center gap-4">
                            <span class="text-[10px] font-mono font-bold text-indigo-400 uppercase tracking-[0.2em] bg-white/10 px-4 py-1.5 rounded-2xl border border-white/5 shadow-inner">UID: {{ selectedProfile.alumni_code }}</span>
                            <span v-if="selectedProfile.verification_status === 'verified'" class="text-[10px] font-black text-emerald-400 uppercase tracking-widest border border-emerald-500/30 px-3 py-1 rounded-xl">Authorized Asset</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-16 px-12">
                    <!-- Identity Section -->
                    <div>
                         <h3 class="text-[11px] font-black text-gray-300 uppercase tracking-[0.4em] mb-10 italic border-b border-gray-50 pb-4">01 // Identity Proof</h3>
                         <div class="flex items-center justify-between p-8 bg-white border border-gray-100 shadow-2xl shadow-gray-200/50 rounded-[2.5rem] hover:border-indigo-400 transition-all group">
                            <div class="flex items-center gap-8">
                                <div class="w-16 h-16 rounded-[1.5rem] bg-gray-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                                     <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-900 uppercase italic">Digital Passport Record</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1.5 leading-none">Institutional Capture</p>
                                </div>
                            </div>
                             <button v-if="selectedProfile.avatar_url" @click="previewFile(selectedProfile.avatar_url)" class="px-8 py-4 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-indigo-600 transition-all shadow-xl shadow-gray-200">
                                Inspect
                            </button>
                         </div>
                    </div>

                    <!-- Academic Section -->
                    <div>
                         <h3 class="text-[11px] font-black text-gray-300 uppercase tracking-[0.4em] mb-10 italic border-b border-gray-50 pb-4">02 // Academic Intelligence</h3>
                         <div v-if="selectedProfile.educations?.length" class="space-y-6">
                              <div v-for="edu in selectedProfile.educations" :key="edu.id" class="p-8 bg-white border border-gray-50 shadow-xl shadow-gray-100 rounded-[3rem] group hover:border-indigo-400 transition-all flex items-center justify-between">
                                  <div class="flex items-center gap-8">
                                      <div class="w-16 h-16 rounded-[1.5rem] bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all shadow-inner">
                                          <svg class="w-8 h-8 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                                      </div>
                                      <div>
                                          <p class="text-xs font-black text-gray-900 uppercase italic">{{ edu.institution_name }}</p>
                                          <div class="flex items-center gap-4 mt-2">
                                              <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ edu.degree_program }}</p>
                                              <span class="text-[10px] font-mono font-bold text-indigo-400">Class of {{ edu.graduation_year }}</span>
                                          </div>
                                      </div>
                                  </div>
                                  <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="w-14 h-14 bg-gray-900 text-white rounded-2xl flex items-center justify-center hover:bg-indigo-600 transition-all shadow-2xl active:scale-90 flex-shrink-0">
                                      <svg class="w-6 h-6 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                  </button>
                              </div>
                         </div>
                         <div v-else class="p-10 border-2 border-dashed border-gray-100 rounded-[3rem] text-center">
                             <p class="text-[10px] font-black text-gray-300 uppercase italic">No Academic Records Filed</p>
                         </div>
                    </div>

                    <!-- Academic / Certs Section -->
                    <div>
                         <h3 class="text-[11px] font-black text-gray-300 uppercase tracking-[0.4em] mb-10 italic border-b border-gray-50 pb-4">03 // Evidence Collection</h3>
                         <div class="space-y-10">
                             <!-- Certificates (Vault) -->
                             <div v-if="selectedProfile.certificates?.length" class="space-y-6">
                                  <h4 class="text-[10px] font-black text-amber-500 uppercase tracking-widest flex items-center gap-4">
                                      <span class="h-0.5 w-12 bg-amber-500/20"></span>
                                      Sertifikasi Kompetensi (Vault)
                                  </h4>
                                  <div v-for="cert in selectedProfile.certificates" :key="cert.id" class="flex items-center justify-between p-8 bg-white border border-gray-50 shadow-xl shadow-gray-100 rounded-[3rem] group hover:border-amber-400 transition-all">
                                      <div class="max-w-[70%] text-left">
                                          <div class="flex items-center gap-4 mb-3">
                                               <span class="text-[9px] font-black px-2.5 py-1 rounded-xl bg-gray-900 text-white uppercase tracking-widest">{{ cert.cert_type }}</span>
                                               <p class="text-sm font-black text-gray-900 truncate uppercase italic tracking-tighter">{{ cert.cert_name }}</p>
                                          </div>
                                          <p class="text-[10px] font-mono font-bold text-gray-400 uppercase italic">Registry ID: {{ cert.cert_number }}</p>
                                      </div>
                                      <button v-if="cert.cert_file_url" @click="previewFile(cert.cert_file_url)" class="w-14 h-14 bg-gray-900 text-white rounded-2xl flex items-center justify-center hover:bg-amber-600 transition-all shadow-2xl active:scale-90 flex-shrink-0">
                                          <svg class="w-6 h-6 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                      </button>
                                  </div>
                             </div>

                             <!-- Sea Service History -->
                             <div v-if="selectedProfile.sea_services?.length" class="space-y-6">
                                  <h4 class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-4">
                                      <span class="h-0.5 w-12 bg-emerald-500/20"></span>
                                      Operational Experience History
                                  </h4>
                                  <div v-for="sea in selectedProfile.sea_services" :key="sea.id" class="flex items-center justify-between p-8 bg-white border border-gray-50 shadow-xl shadow-gray-100 rounded-[3rem] group hover:border-emerald-400 transition-all">
                                      <div>
                                          <p class="text-sm font-black text-gray-900 uppercase italic tracking-tighter mb-2">{{ sea.vessel_name }}</p>
                                          <div class="flex items-center gap-4">
                                               <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-xl">Exp: {{ sea.duration_months }} Mos</span>
                                               <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ sea.route }} Route</span>
                                          </div>
                                      </div>
                                      <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="w-14 h-14 bg-gray-900 text-white rounded-2xl flex items-center justify-center hover:bg-emerald-600 transition-all shadow-2xl active:scale-90 flex-shrink-0">
                                          <svg class="w-6 h-6 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                      </button>
                                  </div>
                             </div>
                         </div>
                    </div>

                    <!-- Audit Commands -->
                    <div class="border-t border-gray-100 pt-20 mt-12 pb-24">
                        <div class="flex flex-col gap-6">
                             <template v-if="selectedProfile.verification_status !== 'verified'">
                                <button 
                                    v-if="selectedProfile.verification_status === 'pending'" 
                                    @click="markAsReviewing" 
                                    class="w-full px-8 py-7 bg-white border-2 border-indigo-200 hover:border-indigo-600 text-indigo-700 text-[11px] font-black uppercase tracking-[0.4em] rounded-[2.5rem] shadow-3xl transition-all shadow-indigo-100 italic"
                                >
                                    Activate Audit Protocol
                                </button>
                                <div v-else class="bg-indigo-950 text-white px-10 py-7 rounded-[2.5rem] text-center text-[11px] font-black uppercase tracking-[0.4em] shadow-3xl shadow-indigo-200/50 italic flex items-center justify-center gap-6">
                                     <span class="w-3 h-3 rounded-full bg-white animate-ping"></span>
                                     Screening Priority Live
                                </div>
                                <button @click="approve" class="w-full px-8 py-7 bg-gray-900 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.4em] rounded-[2.5rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.4)] transition-all active:scale-95 italic border-4 border-indigo-600/50">
                                    Authenticate Registry Asset
                                </button>
                            </template>
                            <div v-else class="bg-emerald-600 text-white px-8 py-8 rounded-[2.5rem] text-center text-[11px] font-black uppercase tracking-[0.4em] shadow-2xl flex items-center justify-center gap-6 italic border-4 border-emerald-400/50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                Immutably Verified Record
                            </div>
                            <button @click="showSlideOver = false" class="w-full px-6 py-6 bg-gray-50 border border-transparent hover:bg-indigo-50 hover:text-indigo-600 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-[2rem] transition-all">
                                Terminate Session
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </SlideOver>

        <!-- Proof Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-white rounded-[4rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                 <div class="px-14 py-10 border-b border-gray-100 flex items-center justify-between bg-gray-900 text-white shadow-xl">
                    <div class="flex items-center gap-6">
                        <div class="w-4 h-4 rounded-full bg-indigo-500 animate-pulse"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] italic">Vault Asset Inspection Mode</h3>
                    </div>
                    <button @click="showPreviewModal = false" class="w-14 h-14 rounded-[1.5rem] bg-white/10 flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all shadow-2xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-16 flex-grow overflow-y-auto flex items-center justify-center bg-gray-950/5">
                    <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full h-auto rounded-[3.5rem] shadow-[0_60px_120px_-30px_rgba(0,0,0,0.4)] border-[16px] border-white rotate-1" />
                    <iframe v-else :src="currentPreview" class="w-full h-[75vh] rounded-[3rem] border-8 border-white shadow-3xl bg-white" frameborder="0"></iframe>
                </div>
                <div class="p-12 bg-white border-t border-gray-50 flex justify-end">
                    <button @click="showPreviewModal = false" class="px-16 py-6 bg-gray-900 text-white rounded-[2rem] text-[11px] font-black uppercase tracking-[0.4em] shadow-3xl hover:bg-indigo-600 hover:scale-105 transition-all italic">
                        Confirm Registry Verification
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
