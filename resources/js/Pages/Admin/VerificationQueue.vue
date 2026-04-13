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

const currentPreview = ref(null);
const showPreviewModal = ref(false);

const filteredQueue = computed(() => {
    if (!props.queue) return [];
    
    return props.queue.filter(p => {
        const matchesSearch = !searchQuery.value || 
            p.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
            p.alumni_code?.toLowerCase().includes(searchQuery.value.toLowerCase());
            
        const matchesStatus = statusFilter.value === 'all' || p.verification_status === statusFilter.value;
        
        return matchesSearch && matchesStatus;
    });
});

const reviewProfile = (profile) => {
    selectedProfile.value = profile;
    showSlideOver.value = true;
};

const previewFile = (url) => {
    currentPreview.value = `/storage/${url}`;
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
                            <input v-model="searchQuery" type="text" placeholder="Search Candidates..." class="w-full sm:w-72 bg-white border-gray-200 rounded-2xl text-xs font-bold pl-12 pr-5 py-4 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-600 shadow-sm transition-all" />
                            <div class="absolute left-4 top-4 text-gray-300 group-focus-within:text-indigo-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                        <button class="w-full sm:w-auto px-8 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-gray-200 hover:scale-105 active:scale-95 transition-all">
                            Export PDF
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
                    <!-- Left Sidebar -->
                    <div class="xl:col-span-3 space-y-8">
                        <div class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm space-y-8">
                            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Queue Breakdown</h3>
                            
                            <div class="space-y-6">
                                <div @click="statusFilter = 'pending'" :class="['cursor-pointer p-4 rounded-2xl transition-all border', statusFilter === 'pending' ? 'bg-amber-50 border-amber-200 ring-2 ring-amber-100' : 'bg-gray-50 border-transparent']">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-black text-amber-600 uppercase">Pending Review</span>
                                        <span class="text-lg font-black text-gray-900">{{ stats.pending }}</span>
                                    </div>
                                    <div class="w-full bg-amber-200/50 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-amber-500 h-full" :style="{width: (stats.pending / (queue?.length || 1) * 100) + '%'}"></div>
                                    </div>
                                </div>

                                <div @click="statusFilter = 'in_review'" :class="['cursor-pointer p-4 rounded-2xl transition-all border', statusFilter === 'in_review' ? 'bg-indigo-50 border-indigo-200 ring-2 ring-indigo-100' : 'bg-gray-50 border-transparent']">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-black text-indigo-600 uppercase">In Progress</span>
                                        <span class="text-lg font-black text-gray-900">{{ stats.in_review }}</span>
                                    </div>
                                    <div class="w-full bg-indigo-200/50 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-indigo-500 h-full" :style="{width: (stats.in_review / (queue?.length || 1) * 100) + '%'}"></div>
                                    </div>
                                </div>

                                <div @click="statusFilter = 'verified'" :class="['cursor-pointer p-4 rounded-2xl transition-all border', statusFilter === 'verified' ? 'bg-emerald-50 border-emerald-200 ring-2 ring-emerald-100' : 'bg-gray-50 border-transparent']">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-[10px] font-black text-emerald-600 uppercase">Verified Master</span>
                                        <span class="text-lg font-black text-gray-900">{{ stats.verified }}</span>
                                    </div>
                                    <div class="w-full bg-emerald-200/50 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full" :style="{width: (stats.verified / (queue?.length || 1) * 100) + '%'}"></div>
                                    </div>
                                </div>
                                
                                <button @click="statusFilter = 'all'" class="w-full py-3 text-[10px] font-black uppercase text-gray-400 hover:text-gray-900 transition-colors tracking-widest">Show All Entries</button>
                            </div>
                        </div>

                        <div class="bg-gray-900 rounded-3xl p-8 shadow-2xl text-white">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-lg shadow-rose-200 animate-pulse"></div>
                                <span class="text-[10px] font-black uppercase tracking-widest">Risk Guard Protocol</span>
                            </div>
                            <p class="text-[11px] font-medium leading-relaxed opacity-80">
                                Profiles with <span class="text-rose-400 font-bold">STCW Expiry</span> within 3 months are automatically flagged. Confirm data consistency before issuing the institutional badge.
                            </p>
                        </div>
                    </div>

                    <!-- Right Main Area -->
                    <div class="xl:col-span-9 space-y-6">
                        <div class="bg-white border border-gray-200 rounded-[2.5rem] overflow-hidden shadow-sm min-h-[600px] flex flex-col">
                            <div class="px-10 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Candidate Assets</h3>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-bold text-gray-400 uppercase">{{ filteredQueue.length }} Candidates Found</span>
                                </div>
                            </div>

                            <div v-if="!filteredQueue.length" class="flex-grow flex flex-col items-center justify-center p-20">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No matching results in this criteria</p>
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-[0.15em]">
                                            <th class="px-10 py-6">Member Identity</th>
                                            <th class="px-10 py-6">Current Stats</th>
                                            <th class="px-10 py-6">Governance Status</th>
                                            <th class="px-10 py-6 text-right">Operations</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="profile in filteredQueue" :key="profile.id" class="hover:bg-gray-50/80 transition-all group">
                                            <td class="px-10 py-6">
                                                <div class="flex items-center gap-6">
                                                    <div class="flex-shrink-0 w-16 h-16 rounded-3xl overflow-hidden shadow-lg border-2 border-white">
                                                        <img v-if="profile.avatar_url" :src="`/storage/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                                        <div v-else class="w-full h-full bg-indigo-600 flex items-center justify-center text-white font-black text-xl">
                                                            {{ profile.full_name?.charAt(0) }}
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p class="text-base font-black text-gray-900 leading-tight">{{ profile.full_name }}</p>
                                                        <p class="text-[10px] font-mono font-bold text-indigo-500 uppercase mt-1 tracking-wider">{{ profile.alumni_code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6">
                                                <div class="flex flex-col gap-1">
                                                    <p class="text-xs font-bold text-gray-800 leading-tight uppercase">{{ profile.rank }}</p>
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ profile.region }}</p>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6">
                                                <div v-if="profile.verification_status === 'pending'" class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2 rounded-xl w-fit border border-amber-100 shadow-sm">
                                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                                    <span class="text-[10px] font-black uppercase tracking-widest">Pending Review</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'in_review'" class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-xl w-fit shadow-lg shadow-indigo-100">
                                                    <span class="w-2 h-2 rounded-full bg-white"></span>
                                                    <span class="text-[10px] font-black uppercase tracking-widest">Screening</span>
                                                </div>
                                                <div v-else-if="profile.verification_status === 'verified'" class="flex items-center gap-2 bg-emerald-50 text-emerald-700 px-4 py-2 rounded-xl w-fit border border-emerald-100 shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                                    <span class="text-[10px] font-black uppercase tracking-widest">Verified Master</span>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6 text-right">
                                                <button @click="reviewProfile(profile)" class="px-6 py-3 bg-white border border-gray-200 hover:border-gray-900 text-gray-900 text-[10px] font-black uppercase tracking-widest rounded-2xl shadow-sm transition-all hover:bg-gray-900 hover:text-white">
                                                    {{ profile.verification_status === 'verified' ? 'View Details' : 'Process Review' }}
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
            <div v-if="selectedProfile" class="flex flex-col h-full space-y-8 pb-10">
                
                <div class="flex items-center gap-6 p-8 bg-gray-900 rounded-[2.5rem] text-white mx-4 mt-2 shadow-2xl">
                    <div class="w-20 h-20 rounded-3xl overflow-hidden border-4 border-white/10 shadow-xl">
                        <img v-if="selectedProfile.avatar_url" :src="`/storage/${selectedProfile.avatar_url}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full bg-white/5 flex items-center justify-center font-black text-2xl text-white/50">{{ selectedProfile.full_name?.charAt(0) }}</div>
                    </div>
                    <div>
                        <p class="text-2xl font-black leading-none mb-1">{{ selectedProfile.full_name }}</p>
                        <p class="text-[10px] font-mono font-bold text-indigo-400 uppercase tracking-widest">{{ selectedProfile.alumni_code }}</p>
                    </div>
                </div>

                <div class="space-y-8 px-6">
                    <div>
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Core Proofs</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <span class="block text-[9px] font-black text-gray-400 uppercase mb-1">Rank Authority</span>
                                <span class="block text-xs font-black text-gray-900 truncate uppercase">{{ selectedProfile.rank }}</span>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                                <span class="block text-[9px] font-black text-gray-400 uppercase mb-1">Regional Branch</span>
                                <span class="block text-xs font-black text-gray-900 truncate uppercase">{{ selectedProfile.region }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 pt-8">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6">Credential Evidence Gallery</h3>
                        
                        <div class="space-y-8">
                            <!-- Education -->
                            <div v-if="selectedProfile.educations?.length">
                                <h4 class="text-[10px] font-black text-indigo-600 uppercase mb-4 flex items-center tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2 shadow-sm"></span> Academic Records
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="edu in selectedProfile.educations" :key="edu.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl hover:border-indigo-300 transition-colors">
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
                                <h4 class="text-[10px] font-black text-amber-600 uppercase mb-4 flex items-center tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2 shadow-sm"></span> Professional Certificates
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="cert in selectedProfile.certificates" :key="cert.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl hover:border-amber-300 transition-colors">
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
                                <h4 class="text-[10px] font-black text-emerald-600 uppercase mb-4 flex items-center tracking-widest">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2 shadow-sm"></span> Sea Service Proof
                                </h4>
                                <div class="space-y-4">
                                    <div v-for="sea in selectedProfile.sea_services" :key="sea.id" class="flex items-center justify-between p-5 bg-white border border-gray-100 shadow-sm rounded-3xl hover:border-emerald-300 transition-colors">
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

                    <div class="border-t border-gray-100 pt-10 mt-6 pb-20">
                        <div class="flex flex-col gap-4">
                            <template v-if="selectedProfile.verification_status !== 'verified'">
                                <button 
                                    v-if="selectedProfile.verification_status === 'pending'" 
                                    @click="markAsReviewing" 
                                    class="w-full flex items-center justify-center px-6 py-5 bg-white border border-indigo-200 hover:border-indigo-600 text-indigo-700 text-[10px] font-black uppercase tracking-[0.2em] rounded-3xl shadow-sm transition-all shadow-indigo-50"
                                >
                                    Initiate Screening
                                </button>
                                <div v-else class="bg-indigo-600 text-white px-6 py-5 rounded-3xl text-center text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-indigo-100">
                                    In Review Process
                                </div>

                                <button @click="approve" class="w-full flex items-center justify-center px-6 py-5 bg-gray-900 hover:bg-black text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-3xl shadow-2xl transition-all">
                                    Approve & Commit to Registry
                                </button>
                            </template>
                            <div v-else class="bg-emerald-600 text-white px-6 py-5 rounded-3xl text-center text-[10px] font-black uppercase tracking-widest flex items-center justify-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                Verified Record Immutable
                            </div>
                            
                            <button @click="showSlideOver = false" class="w-full px-6 py-4 bg-gray-50 border border-transparent hover:bg-gray-200 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-3xl transition-all">
                                Close Panel
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </SlideOver>

        <!-- Document Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-white rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">Document Evidence Preview</h3>
                    <button @click="showPreviewModal = false" class="w-8 h-8 rounded-full bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-900 hover:text-white transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-8 flex-grow overflow-y-auto flex items-center justify-center bg-gray-200/20">
                    <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full h-auto rounded-xl shadow-lg border-4 border-white" />
                    <iframe v-else :src="currentPreview" class="w-full h-[70vh] rounded-xl border border-gray-200" frameborder="0"></iframe>
                </div>
                <div class="p-6 bg-white border-t border-gray-100 flex justify-end">
                    <button @click="showPreviewModal = false" class="px-8 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-gray-200">
                        Done Previewing
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
