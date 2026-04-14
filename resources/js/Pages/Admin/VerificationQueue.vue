u<script setup>
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

const currentPreview = ref(null);
const showPreviewModal = ref(false);

const ranks = computed(() => [...new Set(props.queue?.map(p => p.rank))].filter(Boolean));
const regions = computed(() => [...new Set(props.queue?.map(p => p.region))].filter(Boolean));

const filteredQueue = computed(() => {
    if (!props.queue) return [];
    
    return props.queue.filter(p => {
        const matchesSearch = !searchQuery.value || 
            p.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            p.alumni_code?.toLowerCase().includes(searchQuery.value.toLowerCase());
            
        const matchesStatus = statusFilter.value === 'all' || p.verification_status === statusFilter.value;
        const matchesRank = !rankFilter.value || p.rank === rankFilter.value;
        const matchesRegion = !regionFilter.value || p.region === regionFilter.value;
        
        return matchesSearch && matchesStatus && matchesRank && matchesRegion;
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
    <Head title="Verification Queue" />

    <AuthenticatedLayout>
        <div class="py-10 bg-gray-50/50 min-h-screen">
            <div class="max-w-[100rem] mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                
                <!-- Header Section -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-gray-100 pb-10">
                    <div>
                        <div class="flex items-center gap-2 text-[10px] font-black text-gray-400 mb-2 uppercase tracking-[0.2em]">
                            <span>Registry Guard</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-indigo-600">Master Verification</span>
                        </div>
                        <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Governance Intelligence</h2>
                        <p class="text-sm font-medium text-gray-500 mt-2 max-w-xl">Audit-ready screening and verification for national maritime professional records.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="relative group">
                            <input v-model="searchQuery" type="text" placeholder="Quick Search..." class="w-full sm:w-80 bg-white border-gray-200 rounded-2xl text-xs font-bold pl-12 pr-5 py-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 shadow-sm transition-all" />
                            <div class="absolute left-4 top-4 text-gray-300 group-focus-within:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
                    <!-- Left Sidebar (Health & Deep Filters) -->
                    <div class="xl:col-span-3 space-y-8">
                        
                        <!-- Status Tabs -->
                        <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm">
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Status Pipeline</h3>
                            <div class="flex flex-col gap-3">
                                <button @click="statusFilter = 'all'" :class="['px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all border', statusFilter === 'all' ? 'bg-gray-900 border-gray-900 text-white shadow-xl shadow-gray-200' : 'bg-white border-gray-50 text-gray-400']">
                                    All Records ({{ props.queue?.length || 0 }})
                                </button>
                                <button @click="statusFilter = 'pending'" :class="['px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all border', statusFilter === 'pending' ? 'bg-amber-50 border-amber-200 text-amber-700 shadow-sm shadow-amber-100' : 'bg-white border-gray-50 text-gray-400']">
                                    Pending ({{ stats.pending }})
                                </button>
                                <button @click="statusFilter = 'in_review'" :class="['px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all border', statusFilter === 'in_review' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 shadow-sm shadow-indigo-100' : 'bg-white border-gray-50 text-gray-400']">
                                    In Review ({{ stats.in_review }})
                                </button>
                                <button @click="statusFilter = 'verified'" :class="['px-5 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-left transition-all border', statusFilter === 'verified' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 shadow-sm shadow-emerald-100' : 'bg-white border-gray-50 text-gray-400']">
                                    Verified ({{ stats.verified }})
                                </button>
                            </div>
                        </div>

                        <!-- Deep Filters Module -->
                        <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Deep Filter</h3>
                                <button @click="rankFilter = ''; regionFilter = ''" class="text-[9px] font-bold text-indigo-600">Reset</button>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-2">Rank Filter</label>
                                    <select v-model="rankFilter" class="w-full bg-gray-50 border-transparent rounded-xl text-xs font-bold px-4 py-3 focus:ring-indigo-600 focus:bg-white transition-all">
                                        <option value="">All Ranks</option>
                                        <option v-for="r in ranks" :key="r" :value="r">{{ r }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-black text-gray-400 uppercase mb-2">Region Filter</label>
                                    <select v-model="regionFilter" class="w-full bg-gray-50 border-transparent rounded-xl text-xs font-bold px-4 py-3 focus:ring-indigo-600 focus:bg-white transition-all">
                                        <option value="">All Regions</option>
                                        <option v-for="reg in regions" :key="reg" :value="reg">{{ reg }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Risk Protocol -->
                        <div class="bg-gray-900 rounded-3xl p-8 shadow-2xl text-white">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest">Risk Guard</span>
                            </div>
                            <p class="text-[10px] font-bold leading-relaxed opacity-60 uppercase tracking-tighter">Verified profiles with expired safety certificates are flagged as institutional risks.</p>
                        </div>
                    </div>

                    <!-- Right Column: Results Grid -->
                    <div class="xl:col-span-9 space-y-6">
                         <div class="bg-white border border-gray-200 rounded-[2.5rem] overflow-hidden shadow-sm min-h-[700px] flex flex-col">
                            <div class="px-10 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Alumni Registry Items</h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ filteredQueue.length }} Records</span>
                                </div>
                            </div>

                            <div v-if="!filteredQueue.length" class="flex-grow flex flex-col items-center justify-center p-20">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No matching records found.</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-[0.2em]">
                                            <th class="px-10 py-6">Candidate</th>
                                            <th class="px-10 py-6">Expertise</th>
                                            <th class="px-10 py-6">Governance</th>
                                            <th class="px-10 py-6 text-right">Ops</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="profile in filteredQueue" :key="profile.id" class="hover:bg-gray-50/80 transition-all group">
                                            <td class="px-10 py-6">
                                                <div class="flex items-center gap-6">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-3xl overflow-hidden shadow-lg border-2 border-white">
                                                        <img v-if="profile.avatar_url" :src="`/storage/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                                        <div v-else class="w-full h-full bg-indigo-600 flex items-center justify-center text-white font-black text-xl">{{ profile.full_name?.charAt(0) }}</div>
                                                    </div>
                                                    <div>
                                                        <p class="text-base font-black text-gray-900 leading-tight">{{ profile.full_name }}</p>
                                                        <p class="text-[10px] font-mono font-bold text-indigo-500 uppercase mt-1 tracking-widest">{{ profile.alumni_code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6">
                                                <div class="flex flex-col">
                                                    <p class="text-xs font-black text-gray-800 uppercase leading-tight">{{ profile.rank }}</p>
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ profile.region }}</p>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6">
                                                 <div v-if="profile.verification_status === 'pending'" class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-2xl w-fit border border-amber-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span class="text-[9px] font-black uppercase tracking-widest">Pending</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'in_review'" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-2xl w-fit shadow-lg shadow-indigo-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    <span class="text-[9px] font-black uppercase tracking-widest">Screening</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'verified'" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-2xl w-fit border border-emerald-100">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                    <span class="text-[9px] font-black uppercase tracking-widest">Verified</span>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6 text-right">
                                                <button @click="reviewProfile(profile)" class="px-6 py-3 bg-white border border-gray-200 hover:border-gray-900 text-gray-900 text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all hover:bg-gray-900 hover:text-white">
                                                    Review
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
        <SlideOver :show="showSlideOver" @close="showSlideOver = false" title="Member Screening Intelligence">
            <div v-if="selectedProfile" class="flex flex-col h-full space-y-8 pb-20">
                
                <div class="flex items-center gap-6 p-8 bg-gray-900 rounded-[2.5rem] text-white mx-4 mt-2 shadow-2xl relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 opacity-10">
                        <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                    </div>
                    <div class="w-24 h-24 rounded-3xl overflow-hidden border-4 border-white/10 shadow-xl flex-shrink-0 cursor-pointer hover:scale-105 transition-all" @click="selectedProfile.avatar_url ? previewFile(selectedProfile.avatar_url) : null">
                        <img v-if="selectedProfile.avatar_url" :src="`/storage/${selectedProfile.avatar_url}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full bg-white/5 flex items-center justify-center font-black text-2xl text-white/50">{{ selectedProfile.full_name?.charAt(0) }}</div>
                    </div>
                    <div class="z-10">
                        <p class="text-2xl font-black leading-none mb-1">{{ selectedProfile.full_name }}</p>
                        <p class="text-[10px] font-mono font-bold text-indigo-400 uppercase tracking-widest">{{ selectedProfile.alumni_code }}</p>
                    </div>
                </div>

                <div class="space-y-8 px-8">
                    <!-- Identity Documents Preview (Avatar/PP) -->
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Identity Verification</h3>
                        <div class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl hover:border-indigo-300 transition-colors">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-indigo-600">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <p class="text-xs font-black text-gray-900 uppercase">Passport Photo (Pas Foto)</p>
                            </div>
                             <button v-if="selectedProfile.avatar_url" @click="previewFile(selectedProfile.avatar_url)" class="px-5 py-2.5 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all flex-shrink-0">
                                Preview
                            </button>
                            <span v-else class="text-[9px] font-bold text-gray-300 uppercase italic">Not Provided</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Expertise Evidence</h3>
                        <div class="space-y-6">
                            <!-- Education -->
                            <div v-if="selectedProfile.educations?.length">
                                <h4 class="text-[9px] font-black text-indigo-600 uppercase mb-4 flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2 shadow-sm"></span> Academic Pipeline
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="edu in selectedProfile.educations" :key="edu.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl">
                                        <div class="max-w-[70%]">
                                            <p class="text-xs font-black text-gray-900 truncate uppercase leading-tight">{{ edu.institution_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">{{ edu.degree_program }} &bull; {{ edu.graduation_year }}</p>
                                        </div>
                                        <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="px-5 py-2.5 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-all flex-shrink-0">
                                            Preview
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Certificates -->
                            <div v-if="selectedProfile.certificates?.length">
                                <h4 class="text-[9px] font-black text-amber-600 uppercase mb-4 flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2 shadow-sm"></span> Competency Log
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="cert in selectedProfile.certificates" :key="cert.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl">
                                        <div class="max-w-[70%]">
                                            <p class="text-xs font-black text-gray-900 truncate uppercase leading-tight">{{ cert.cert_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">NO: {{ cert.cert_number }}</p>
                                        </div>
                                        <button v-if="cert.cert_file_url" @click="previewFile(cert.cert_file_url)" class="px-5 py-2.5 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 transition-all flex-shrink-0">
                                            Preview
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- Sea Service -->
                            <div v-if="selectedProfile.sea_services?.length">
                                <h4 class="text-[9px] font-black text-emerald-600 uppercase mb-4 flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 shadow-sm"></span> Sea Service Proof
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="sea in selectedProfile.sea_services" :key="sea.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl">
                                        <div class="max-w-[70%]">
                                            <p class="text-xs font-black text-gray-900 truncate uppercase leading-tight">{{ sea.vessel_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 mt-1 uppercase">{{ sea.duration_months }} Months &bull; {{ sea.route }}</p>
                                        </div>
                                        <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="px-5 py-2.5 bg-gray-900 text-white text-[9px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-600 transition-all flex-shrink-0">
                                            Preview
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-10">
                        <div class="flex flex-col gap-4">
                             <template v-if="selectedProfile.verification_status !== 'verified'">
                                <button 
                                    v-if="selectedProfile.verification_status === 'pending'" 
                                    @click="markAsReviewing" 
                                    class="w-full px-6 py-5 bg-white border border-indigo-200 hover:border-indigo-600 text-indigo-700 text-[10px] font-black uppercase tracking-[0.2em] rounded-3xl shadow-sm transition-all shadow-indigo-50"
                                >
                                    Initiate Screening
                                </button>
                                <div v-else class="bg-indigo-600 text-white px-6 py-5 rounded-3xl text-center text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-100">
                                    In Review Process
                                </div>
                                <button @click="approve" class="w-full px-6 py-5 bg-gray-900 hover:bg-black text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-3xl shadow-2xl transition-all">
                                    Verify & Issue Badge
                                </button>
                            </template>
                            <div v-else class="bg-emerald-600 text-white px-6 py-5 rounded-3xl text-center text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Verified Record Immutable
                            </div>
                            <button @click="showSlideOver = false" class="w-full px-6 py-4 bg-gray-50 border border-transparent hover:bg-gray-200 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-3xl transition-all">
                                Close Review Panel
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </SlideOver>

        <!-- Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                 <div class="px-10 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Document Evidence Preview</h3>
                    <button @click="showPreviewModal = false" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-900 hover:text-white transition-all shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-10 flex-grow overflow-y-auto flex items-center justify-center bg-gray-200/10">
                    <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full h-auto rounded-3xl shadow-2xl border-[10px] border-white" />
                    <iframe v-else :src="currentPreview" class="w-full h-[70vh] rounded-2xl border border-gray-100" frameborder="0"></iframe>
                </div>
                <div class="p-8 bg-white border-t border-gray-100 flex justify-end">
                    <button @click="showPreviewModal = false" class="px-10 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-gray-200">
                        Confirm View
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
