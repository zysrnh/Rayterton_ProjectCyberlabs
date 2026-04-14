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
            onSuccess: () => { selectedProfile.value.verification_status = 'in_review'; }
        });
    }
};

const approve = () => {
    if (selectedProfile.value) {
        router.post(route('admin.verifications.approve', selectedProfile.value.id), {}, {
            preserveScroll: true,
            onSuccess: () => { showSlideOver.value = false; selectedProfile.value = null; }
        });
    }
};

const stats = computed(() => ({
    unverified: props.queue?.filter(p => p.verification_status === 'unverified').length || 0,
    pending: props.queue?.filter(p => p.verification_status === 'pending').length || 0,
    in_review: props.queue?.filter(p => p.verification_status === 'in_review').length || 0,
    verified: props.queue?.filter(p => p.verification_status === 'verified').length || 0,
    rejected: props.queue?.filter(p => p.verification_status === 'rejected').length || 0,
}));

const isImage = (url) => {
    if (!url) return false;
    return ['jpg', 'jpeg', 'png', 'gif'].includes(url.split('.').pop().toLowerCase());
};

const statusConfig = {
    unverified: { label: 'Dormant', bg: 'bg-gray-100', text: 'text-gray-500', dot: 'bg-gray-400', badge: 'bg-gray-50 border-gray-200 text-gray-500' },
    pending: { label: 'Pending', bg: 'bg-amber-50', text: 'text-amber-700', dot: 'bg-amber-500 animate-pulse', badge: 'bg-amber-50 border-amber-200 text-amber-700' },
    in_review: { label: 'In Review', bg: 'bg-blue-900', text: 'text-white', dot: 'bg-blue-300 animate-bounce', badge: 'bg-blue-900 border-blue-700 text-white' },
    verified: { label: 'Verified', bg: 'bg-emerald-50', text: 'text-emerald-700', dot: 'bg-emerald-500', badge: 'bg-emerald-50 border-emerald-200 text-emerald-700' },
    rejected: { label: 'Rejected', bg: 'bg-rose-50', text: 'text-rose-700', dot: 'bg-rose-500', badge: 'bg-rose-50 border-rose-200 text-rose-700' },
};

const getStatus = (status) => statusConfig[status] || statusConfig.unverified;
</script>

<template>
    <Head title="Registry Control Center" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F6FA]">
            <div class="max-w-[1600px] mx-auto px-6 lg:px-10 py-10 space-y-8">

                <!-- ── Page Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.25em]">Institutional Command</span>
                            <span class="text-gray-300 text-xs">/</span>
                            <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-[0.25em]">Master Registry Control</span>
                        </div>
                        <h1 class="text-4xl font-black text-gray-900 tracking-tight leading-none">Master Registry Intelligence</h1>
                        <p class="text-sm text-gray-500 mt-2">Cross-platform digital ledger verification for maritime professional assets.</p>
                    </div>

                    <!-- Search -->
                    <div class="relative flex-shrink-0 w-full lg:w-[380px]">
                        <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search name, code, rank..."
                            class="w-full bg-white border border-gray-200 rounded-xl text-sm pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 shadow-sm transition-all placeholder:text-gray-400 font-medium"
                        />
                    </div>
                </div>

                <!-- ── Stats Overview ── -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                    <!-- Total -->
                    <div
                        @click="statusFilter = 'all'"
                        :class="['relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200 select-none group', statusFilter === 'all' ? 'bg-gray-900 border-gray-900 shadow-xl shadow-gray-900/20' : 'bg-white border-gray-100 hover:border-gray-300 shadow-sm']"
                    >
                        <p :class="['text-3xl font-black tracking-tight', statusFilter === 'all' ? 'text-white' : 'text-gray-900']">{{ props.queue?.length || 0 }}</p>
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-1', statusFilter === 'all' ? 'text-gray-400' : 'text-gray-400']">All Registry</p>
                        <div :class="['absolute top-4 right-4 w-2 h-2 rounded-full', statusFilter === 'all' ? 'bg-indigo-400' : 'bg-gray-200 group-hover:bg-gray-400 transition-colors']"></div>
                    </div>
                    <!-- Dormant -->
                    <div
                        @click="statusFilter = 'unverified'"
                        :class="['relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200 select-none group', statusFilter === 'unverified' ? 'bg-gray-800 border-gray-800 shadow-xl shadow-gray-800/20' : 'bg-white border-gray-100 hover:border-gray-300 shadow-sm']"
                    >
                        <p :class="['text-3xl font-black tracking-tight', statusFilter === 'unverified' ? 'text-white' : 'text-gray-900']">{{ stats.unverified }}</p>
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-1', statusFilter === 'unverified' ? 'text-gray-400' : 'text-gray-400']">Dormant</p>
                        <div :class="['absolute top-4 right-4 w-2 h-2 rounded-full', statusFilter === 'unverified' ? 'bg-gray-400' : 'bg-gray-200 group-hover:bg-gray-400 transition-colors']"></div>
                    </div>
                    <!-- Pending -->
                    <div
                        @click="statusFilter = 'pending'"
                        :class="['relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200 select-none', statusFilter === 'pending' ? 'bg-amber-500 border-amber-500 shadow-xl shadow-amber-500/25' : 'bg-white border-gray-100 hover:border-amber-200 shadow-sm group']"
                    >
                        <p :class="['text-3xl font-black tracking-tight', statusFilter === 'pending' ? 'text-white' : 'text-gray-900']">{{ stats.pending }}</p>
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-1', statusFilter === 'pending' ? 'text-amber-100' : 'text-gray-400']">Pending</p>
                        <div :class="['absolute top-4 right-4 w-2 h-2 rounded-full', statusFilter === 'pending' ? 'bg-amber-200 animate-pulse' : 'bg-amber-200 group-hover:bg-amber-400 transition-colors']"></div>
                    </div>
                    <!-- Screening -->
                    <div
                        @click="statusFilter = 'in_review'"
                        :class="['relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200 select-none', statusFilter === 'in_review' ? 'bg-indigo-600 border-indigo-600 shadow-xl shadow-indigo-600/25' : 'bg-white border-gray-100 hover:border-indigo-200 shadow-sm group']"
                    >
                        <p :class="['text-3xl font-black tracking-tight', statusFilter === 'in_review' ? 'text-white' : 'text-gray-900']">{{ stats.in_review }}</p>
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-1', statusFilter === 'in_review' ? 'text-indigo-200' : 'text-gray-400']">Screening</p>
                        <div :class="['absolute top-4 right-4 w-2 h-2 rounded-full', statusFilter === 'in_review' ? 'bg-indigo-300 animate-bounce' : 'bg-indigo-200 group-hover:bg-indigo-400 transition-colors']"></div>
                    </div>
                    <!-- Verified -->
                    <div
                        @click="statusFilter = 'verified'"
                        :class="['relative cursor-pointer rounded-2xl p-5 border-2 transition-all duration-200 select-none col-span-2 sm:col-span-1', statusFilter === 'verified' ? 'bg-emerald-600 border-emerald-600 shadow-xl shadow-emerald-600/25' : 'bg-white border-gray-100 hover:border-emerald-200 shadow-sm group']"
                    >
                        <p :class="['text-3xl font-black tracking-tight', statusFilter === 'verified' ? 'text-white' : 'text-gray-900']">{{ stats.verified }}</p>
                        <p :class="['text-[10px] font-bold uppercase tracking-widest mt-1', statusFilter === 'verified' ? 'text-emerald-100' : 'text-gray-400']">Verified</p>
                        <div :class="['absolute top-4 right-4', statusFilter === 'verified' ? 'text-emerald-300' : 'text-gray-300 group-hover:text-emerald-400 transition-colors']">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- ── Deep Filters ── -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm relative z-20">
                    <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 bg-gray-50/60">
                        <div class="w-7 h-7 rounded-lg bg-gray-900 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                            </svg>
                        </div>
                        <span class="text-xs font-black text-gray-800 uppercase tracking-[0.2em]">Deep Insight Filters</span>
                        <button
                            @click="rankFilter = ''; regionFilter = ''; certTypeFilter = ''"
                            class="ml-auto text-[10px] font-bold text-gray-400 hover:text-indigo-600 uppercase tracking-widest transition-colors px-3 py-1 rounded-lg hover:bg-indigo-50"
                        >
                            Reset All
                        </button>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                        <div class="p-5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Expertise / Rank</label>
                            <CustomSelect v-model="rankFilter" :options="ranks" placeholder="All Ranks" />
                        </div>
                        <div class="p-5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Operational Region</label>
                            <CustomSelect v-model="regionFilter" :options="regions" placeholder="All Regions" />
                        </div>
                        <div class="p-5">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Type</label>
                            <CustomSelect v-model="certTypeFilter" :options="certTypes" placeholder="All Types" />
                        </div>
                    </div>
                </div>

                <!-- ── Main Table ── -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <!-- Table Header -->
                    <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Active Registry Records</h3>
                            <span class="text-[10px] text-gray-400 font-medium">— Live from secure maritime ledger</span>
                        </div>
                        <div class="flex items-center gap-2 bg-gray-900 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            {{ filteredQueue.length }} Matches
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!filteredQueue.length" class="py-32 flex flex-col items-center justify-center gap-5 text-center">
                        <div class="w-20 h-20 rounded-3xl bg-gray-50 border-2 border-dashed border-gray-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest">No records found</p>
                            <p class="text-xs text-gray-300 mt-1">Try adjusting your filters or search query</p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/50">
                                    <th class="text-left px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] w-[40%]">Maritime Agent</th>
                                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Role / Region</th>
                                    <th class="text-left px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Status</th>
                                    <th class="text-right px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="profile in filteredQueue"
                                    :key="profile.id"
                                    class="border-b border-gray-50 hover:bg-gray-50/60 transition-colors group"
                                >
                                    <!-- Agent Identity -->
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-5">
                                            <div class="flex-shrink-0 w-14 h-14 rounded-2xl overflow-hidden bg-gray-100 shadow-sm ring-2 ring-white group-hover:ring-indigo-100 transition-all">
                                                <img v-if="profile.avatar_url" :src="`/storage/${profile.avatar_url}`" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center text-white font-black text-xl">
                                                    {{ profile.full_name?.charAt(0) }}
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-[15px] font-black text-gray-900 leading-tight tracking-tight">{{ profile.full_name }}</p>
                                                <div class="flex items-center gap-3 mt-1.5">
                                                    <span class="text-[10px] font-mono font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-lg uppercase tracking-wider">{{ profile.alumni_code }}</span>
                                                    <span class="text-[10px] text-gray-400 font-medium">📂 {{ profile.certificates?.length || 0 }} certs</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Role / Region -->
                                    <td class="px-6 py-5">
                                        <p class="text-xs font-black text-gray-800 uppercase tracking-tight leading-none">{{ profile.rank }}</p>
                                        <p class="text-[11px] text-gray-400 font-medium mt-1.5 uppercase tracking-widest">{{ profile.region }}</p>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-6 py-5">
                                        <div :class="['inline-flex items-center gap-2 px-4 py-2 rounded-xl border text-[10px] font-black uppercase tracking-wider', getStatus(profile.verification_status).badge]">
                                            <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', getStatus(profile.verification_status).dot]"></span>
                                            {{ getStatus(profile.verification_status).label }}
                                        </div>
                                    </td>

                                    <!-- Action -->
                                    <td class="px-8 py-5 text-right">
                                        <button
                                            @click="reviewProfile(profile)"
                                            class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-white border-2 border-gray-200 hover:border-gray-900 hover:bg-gray-900 hover:text-white text-gray-700 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-200 active:scale-95 group/btn shadow-sm"
                                        >
                                            <svg class="w-3.5 h-3.5 opacity-60 group-hover/btn:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            Audit
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- ── Slide Over Panel ── -->
        <SlideOver :show="showSlideOver" @close="showSlideOver = false" title="Institutional Audit Command">
            <div v-if="selectedProfile" class="flex flex-col h-full pb-28 space-y-8">

                <!-- Profile Hero -->
                <div class="mx-4 mt-3 bg-gray-900 rounded-2xl p-7 text-white relative overflow-hidden">
                    <div class="absolute inset-0 opacity-5">
                        <svg viewBox="0 0 400 200" class="w-full h-full" fill="currentColor">
                            <circle cx="350" cy="30" r="120"/><circle cx="50" cy="180" r="80"/>
                        </svg>
                    </div>
                    <div class="relative flex items-center gap-6 z-10">
                        <div
                            class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-white/10 flex-shrink-0 cursor-pointer hover:scale-105 transition-transform shadow-2xl"
                            @click="selectedProfile.avatar_url ? previewFile(selectedProfile.avatar_url) : null"
                        >
                            <img v-if="selectedProfile.avatar_url" :src="`/storage/${selectedProfile.avatar_url}`" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-white/10 flex items-center justify-center font-black text-3xl text-white/30">{{ selectedProfile.full_name?.charAt(0) }}</div>
                        </div>
                        <div class="min-w-0 flex-grow">
                            <h4 class="text-2xl font-black leading-tight tracking-tight truncate">{{ selectedProfile.full_name }}</h4>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span class="text-[10px] font-mono font-bold text-indigo-300 bg-white/10 border border-white/10 px-3 py-1 rounded-lg uppercase tracking-wider">{{ selectedProfile.alumni_code }}</span>
                                <span v-if="selectedProfile.verification_status === 'verified'" class="text-[10px] font-black text-emerald-400 border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 rounded-lg uppercase tracking-wider">✓ Authorized</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-8 px-6">

                    <!-- 01 Identity Proof -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">01</span>
                            <span class="text-[10px] font-black text-gray-800 uppercase tracking-[0.2em]">Identity Proof</span>
                            <span class="flex-grow h-px bg-gray-100"></span>
                        </div>
                        <div class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-xl hover:border-indigo-300 transition-colors group shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-black text-gray-900 uppercase">Digital Passport Record</p>
                                    <p class="text-[10px] text-gray-400 font-medium mt-0.5">Institutional Capture</p>
                                </div>
                            </div>
                            <button v-if="selectedProfile.avatar_url" @click="previewFile(selectedProfile.avatar_url)" class="px-4 py-2 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-lg hover:bg-indigo-600 transition-colors shadow-md">
                                Inspect
                            </button>
                        </div>
                    </div>

                    <!-- 02 Academic Intelligence -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">02</span>
                            <span class="text-[10px] font-black text-gray-800 uppercase tracking-[0.2em]">Academic Intelligence</span>
                            <span class="flex-grow h-px bg-gray-100"></span>
                        </div>
                        <div v-if="selectedProfile.educations?.length" class="space-y-3">
                            <div
                                v-for="edu in selectedProfile.educations" :key="edu.id"
                                class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-xl hover:border-indigo-300 transition-colors group shadow-sm"
                            >
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-600 group-hover:text-white group-hover:border-indigo-600 transition-all flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-black text-gray-900 uppercase truncate">{{ edu.institution_name }}</p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <p class="text-[10px] text-gray-400 font-medium truncate">{{ edu.degree_program }}</p>
                                            <span class="text-[10px] font-bold text-indigo-500 flex-shrink-0">{{ edu.graduation_year }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-indigo-600 transition-colors shadow-md flex-shrink-0 ml-3 active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div v-else class="py-8 border-2 border-dashed border-gray-100 rounded-xl text-center">
                            <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest">No Academic Records Filed</p>
                        </div>
                    </div>

                    <!-- 03 Evidence Collection -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">03</span>
                            <span class="text-[10px] font-black text-gray-800 uppercase tracking-[0.2em]">Evidence Collection</span>
                            <span class="flex-grow h-px bg-gray-100"></span>
                        </div>

                        <!-- Certificates -->
                        <div v-if="selectedProfile.certificates?.length" class="space-y-3">
                            <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-8 h-px bg-amber-300"></span>Competency Certificates
                            </p>
                            <div
                                v-for="cert in selectedProfile.certificates" :key="cert.id"
                                class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-xl hover:border-amber-300 transition-colors group shadow-sm"
                            >
                                <div class="min-w-0 flex-grow">
                                    <div class="flex items-center gap-2.5 mb-1.5">
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded-md bg-gray-900 text-white uppercase tracking-widest flex-shrink-0">{{ cert.cert_type }}</span>
                                        <p class="text-xs font-black text-gray-900 truncate uppercase tracking-tight">{{ cert.cert_name }}</p>
                                    </div>
                                    <p class="text-[10px] font-mono text-gray-400">No. {{ cert.cert_number }}</p>
                                </div>
                                <button v-if="cert.cert_file_url" @click="previewFile(cert.cert_file_url)" class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-amber-500 transition-colors shadow-md flex-shrink-0 ml-3 active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Sea Service -->
                        <div v-if="selectedProfile.sea_services?.length" class="space-y-3 mt-4">
                            <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-2">
                                <span class="w-8 h-px bg-emerald-300"></span>Sea Service History
                            </p>
                            <div
                                v-for="sea in selectedProfile.sea_services" :key="sea.id"
                                class="flex items-center justify-between p-5 bg-white border border-gray-200 rounded-xl hover:border-emerald-300 transition-colors group shadow-sm"
                            >
                                <div>
                                    <p class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ sea.vessel_name }}</p>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-100 px-2.5 py-0.5 rounded-lg uppercase tracking-widest">{{ sea.duration_months }} Months</span>
                                        <span class="text-[10px] text-gray-400 font-medium uppercase">{{ sea.route }} Route</span>
                                    </div>
                                </div>
                                <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-colors shadow-md flex-shrink-0 ml-3 active:scale-90">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── Audit Action Commands ── -->
                    <div class="pt-6 pb-10 space-y-3">
                        <template v-if="selectedProfile.verification_status !== 'verified'">
                            <button
                                v-if="selectedProfile.verification_status === 'pending'"
                                @click="markAsReviewing"
                                class="w-full px-6 py-4 bg-white border-2 border-indigo-200 hover:border-indigo-600 hover:bg-indigo-50 text-indigo-700 text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl transition-all shadow-sm"
                            >
                                Activate Audit Protocol
                            </button>
                            <div v-else class="w-full px-6 py-4 bg-indigo-900 text-white text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl text-center flex items-center justify-center gap-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                                Screening Priority Live
                            </div>
                            <button
                                @click="approve"
                                class="w-full px-6 py-4 bg-gray-900 hover:bg-black text-white text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl shadow-xl shadow-gray-900/30 transition-all active:scale-[0.98] border-2 border-indigo-500/30 hover:border-indigo-500/60"
                            >
                                Authenticate Registry Asset
                            </button>
                        </template>
                        <div v-else class="w-full px-6 py-5 bg-emerald-600 text-white text-[11px] font-black uppercase tracking-[0.3em] rounded-2xl text-center flex items-center justify-center gap-3 border-2 border-emerald-400/40 shadow-xl shadow-emerald-600/25">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            Immutably Verified Record
                        </div>
                        <button
                            @click="showSlideOver = false"
                            class="w-full px-6 py-3.5 bg-gray-50 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-2xl hover:bg-gray-100 hover:text-gray-700 transition-colors"
                        >
                            Close Panel
                        </button>
                    </div>

                </div>
            </div>
        </SlideOver>

        <!-- ── Preview Modal ── -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-white rounded-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-900 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em]">Vault Asset Inspection</h3>
                    </div>
                    <button @click="showPreviewModal = false" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-10 flex-grow overflow-y-auto flex items-center justify-center bg-gray-950/5">
                    <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full h-auto rounded-2xl shadow-2xl border-8 border-white" />
                    <iframe v-else :src="currentPreview" class="w-full h-[70vh] rounded-xl border-4 border-white shadow-xl bg-white" frameborder="0"></iframe>
                </div>
                <div class="px-8 py-5 bg-white border-t border-gray-100 flex justify-end">
                    <button @click="showPreviewModal = false" class="px-8 py-3 bg-gray-900 text-white rounded-xl text-[11px] font-black uppercase tracking-[0.3em] hover:bg-indigo-600 transition-colors shadow-lg">
                        Close Preview
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>