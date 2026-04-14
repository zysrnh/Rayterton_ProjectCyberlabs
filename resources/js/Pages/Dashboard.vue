<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    profile: {
        type: Object,
        default: null
    }
});

const form = useForm({
    full_name: props.profile?.full_name || '',
    rank: props.profile?.rank || '',
    phone: props.profile?.phone || '',
    region: props.profile?.region || '',
    preferred_vessel_type: props.profile?.preferred_vessel_type || '',
    preferred_route: props.profile?.preferred_route || '',
    avatar: null,
});

const submit = () => {
    form.post(route('alumni.master-profile.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};

const submitVerification = () => {
    router.post(route('alumni.verify.submit'), {}, { preserveScroll: true });
};

const toggleAvailability = () => {
    router.post(route('alumni.toggle_availability'), {}, { preserveScroll: true });
};

const completeness = computed(() => props.profile?.profile_completeness || 0);
const isVerified = computed(() => props.profile?.verification_status === 'verified');
const statusColor = computed(() => {
    if (props.profile?.verification_status === 'verified') return 'text-emerald-600';
    if (props.profile?.verification_status === 'pending' || props.profile?.verification_status === 'in_review') return 'text-amber-600';
    return 'text-gray-400';
});

// Region Assistant Data
const indonesianRegions = [
    'Aceh', 'Sumatera Utara', 'Sumatera Barat', 'Riau', 'Kepulauan Riau', 'Jambi', 'Sumatera Selatan', 'Bangka Belitung', 'Bengkulu', 'Lampung',
    'DKI Jakarta', 'Jawa Barat', 'Banten', 'Jawa Tengah', 'DI Yogyakarta', 'Jawa Timur',
    'Bali', 'Nusa Tenggara Barat', 'Nusa Tenggara Timur',
    'Kalimantan Barat', 'Kalimantan Tengah', 'Kalimantan Selatan', 'Kalimantan Timur', 'Kalimantan Utara',
    'Sulawesi Utara', 'Sulawesi Tengah', 'Sulawesi Selatan', 'Sulawesi Tenggara', 'Gorontalo', 'Sulawesi Barat',
    'Maluku', 'Maluku Utara', 'Papua', 'Papua Barat', 'Papua Selatan', 'Papua Tengah', 'Papua Pegunungan', 'Papua Barat Daya'
];

// Modal states
const showEducationModal = ref(false);
const showCertificateModal = ref(false);
const showSeaServiceModal = ref(false);
const showCvPreview = ref(false);

// Preview states
const currentPreview = ref(null);
const showPreviewModal = ref(false);

const previewFile = (url) => {
    currentPreview.value = url.startsWith('http') ? url : `/storage/${url}`;
    showPreviewModal.value = true;
};

const isImage = (url) => {
    if (!url) return false;
    return ['jpg', 'jpeg', 'png', 'gif'].includes(url.split('.').pop().toLowerCase());
};

const eduForm = useForm({
    institution_name: '',
    degree_program: '',
    graduation_year: '',
    diploma_file: null,
});

const certForm = useForm({
    cert_type: 'BST',
    cert_name: '',
    cert_number: '',
    issuing_body: '',
    issued_date: '',
    expiry_date: '',
    cert_file: null,
});

const seaForm = useForm({
    vessel_name: '',
    vessel_type: '',
    company_name: '',
    rank_at_time: '',
    start_date: '',
    end_date: '',
    route: '',
    contract_file: null,
    duration_months: 0
});

const submitEdu = () => {
    eduForm.post(route('alumni.educations.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showEducationModal.value = false;
            eduForm.reset();
        }
    });
};

const submitCert = () => {
    certForm.post(route('alumni.certificates.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showCertificateModal.value = false;
            certForm.reset();
        }
    });
};

const submitSea = () => {
    seaForm.post(route('alumni.seasearvices.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            showSeaServiceModal.value = false;
            seaForm.reset();
        }
    });
};

const getCertIcon = (type) => {
    switch(type) {
        case 'BST': return '⚓';
        case 'COP': return '📜';
        case 'COC': return '🎫';
        default: return '📄';
    }
};

const groupedCerts = computed(() => {
    if (!props.profile?.certificates) return {};
    return props.profile.certificates.reduce((acc, cert) => {
        if (!acc[cert.cert_type]) acc[cert.cert_type] = [];
        acc[cert.cert_type].push(cert);
        return acc;
    }, {});
});

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F8F9FD] py-12">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-10 space-y-10">
                
                <!-- ── Strategic Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8 border-b border-gray-100 pb-12">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] bg-indigo-50 px-3 py-1 rounded-lg">Professional Ledger</span>
                            <span class="text-gray-300 text-xs">/</span>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em]">Identity Hub</span>
                        </div>
                        <h1 class="text-5xl font-black text-gray-900 tracking-tighter leading-none">Living Professional Identity</h1>
                        <p class="text-sm font-medium text-gray-500 max-w-xl">Verified records acting as an institutional guarantee of candidate readiness for global maritime operations.</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                         <button 
                            @click="showCvPreview = true"
                            class="px-8 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] shadow-2xl shadow-gray-200 hover:bg-black hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-3 italic"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Explore Maritime CV
                        </button>
                        <button 
                            v-if="isVerified"
                            @click="toggleAvailability"
                            :class="[
                                'px-8 py-4 rounded-2xl text-[10px] font-black uppercase tracking-[0.2em] transition-all border-2 active:scale-95 italic shadow-xl',
                                props.profile?.availability_status === 'open_to_offers' 
                                    ? 'bg-emerald-600 border-emerald-600 text-white shadow-emerald-100 hover:bg-emerald-700' 
                                    : 'bg-white border-gray-100 text-gray-400 hover:border-gray-900 hover:text-gray-900 shadow-gray-100'
                            ]"
                        >
                            {{ props.profile?.availability_status === 'open_to_offers' ? '✓ Open to Offers' : 'Mark Available' }}
                        </button>
                    </div>
                </div>

                <!-- ── Identity & Metrics Snapshot ── -->
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Master Asset Card -->
                    <div class="lg:col-span-3 bg-white rounded-[2.5rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col md:flex-row relative">
                        <!-- BG Decoration -->
                        <div class="absolute right-0 top-0 w-64 h-64 bg-indigo-50/30 rounded-full -mr-20 -mt-20 blur-3xl pointer-events-none"></div>
                        
                        <!-- Profile Image Section -->
                        <div class="p-10 bg-gray-50/50 border-r border-gray-50 flex flex-col items-center justify-center min-w-[280px] relative z-10">
                            <div class="relative">
                                <div :class="['w-44 h-44 rounded-3xl overflow-hidden shadow-2xl border-8 border-white group transition-all duration-500', isVerified ? 'ring-4 ring-emerald-500/20' : '']">
                                    <img v-if="props.profile?.avatar_url" :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                                    <div v-else class="w-full h-full bg-slate-900 flex items-center justify-center text-white text-5xl font-black italic">
                                        {{ props.profile?.full_name?.charAt(0) || '?' }}
                                    </div>
                                </div>
                                <div v-if="isVerified" class="absolute -bottom-4 -right-4 bg-emerald-500 text-white p-3 rounded-2xl shadow-xl border-4 border-white">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            
                            <div class="mt-10 text-center w-full">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.4em] mb-3 italic">Identity Serial</p>
                                <div class="px-6 py-2.5 bg-gray-950 text-indigo-400 rounded-xl text-[11px] font-mono font-black tracking-widest shadow-2xl border border-white/10">
                                    {{ props.profile?.alumni_code || 'SN: AWAITING_LEDGER' }}
                                </div>
                            </div>
                        </div>

                        <!-- Core Identity Details -->
                        <div class="p-12 flex-grow flex flex-col justify-center relative z-10">
                            <div class="flex flex-wrap items-center gap-4">
                                <h2 class="text-5xl font-black text-gray-900 tracking-tighter leading-none">{{ props.profile?.full_name || 'Incomplete Asset' }}</h2>
                                <span v-if="isVerified" class="px-4 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-[0.2em] border border-emerald-100 shadow-sm shadow-emerald-50">Verified Record</span>
                            </div>
                            <p class="text-xl font-black text-gray-400 mt-4 uppercase italic tracking-tight">{{ props.profile?.rank || 'Rank Not Synchronized' }}</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-10 mt-12 bg-gray-50/40 p-10 rounded-[2rem] border border-gray-50/50 shadow-inner">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Operational Region</p>
                                    <p class="text-lg font-black text-gray-800 tracking-tight">{{ props.profile?.region || 'Registry Empty' }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">Vessel Specialization</p>
                                    <p class="text-lg font-black text-gray-800 tracking-tight">{{ props.profile?.preferred_vessel_type || 'Registry Empty' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-8 mt-12 pt-8 border-t border-gray-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-300 border border-gray-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-xs font-black text-gray-600 uppercase tracking-tight">{{ $page.props.auth.user.email }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-gray-300 border border-gray-50">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-xs font-black text-gray-600 uppercase tracking-tight">{{ props.profile?.phone || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Readiness Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-gray-950 rounded-[2.5rem] p-8 shadow-2xl text-white relative overflow-hidden group">
                            <!-- Background Pulse -->
                            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-600/20 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
                            
                            <div class="flex items-center justify-between mb-8 relative z-10">
                                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em]">Readiness Score</span>
                                <span class="text-3xl font-black italic">{{ completeness }}%</span>
                            </div>
                            <div class="w-full bg-white/5 rounded-full h-3 overflow-hidden relative z-10 border border-white/5">
                                <div class="bg-gradient-to-r from-indigo-600 to-indigo-400 h-full rounded-full transition-all duration-1000 shadow-[0_0_20px_rgba(79,70,229,0.5)]" :style="{ width: completeness + '%' }"></div>
                            </div>
                            <p class="mt-8 text-[11px] font-medium text-gray-400 leading-relaxed uppercase tracking-widest relative z-10">Complete your profile to increase visibility to global fleets.</p>
                            
                            <div class="mt-10 pt-8 border-t border-white/5 relative z-10">
                                <div :class="['flex items-center justify-between p-4 rounded-2xl border transition-all', isVerified ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400 shadow-lg shadow-emerald-500/5' : 'bg-white/5 border-white/10 text-gray-500']">
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em]">Registry Level</span>
                                    <span class="text-[10px] font-black italic tracking-widest">{{ props.profile?.verification_status.toUpperCase() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="props.profile?.verification_status !== 'unverified'" class="bg-white rounded-[2.5rem] border border-gray-100 p-8 shadow-sm group hover:border-indigo-100 transition-all">
                             <div class="flex items-center gap-4">
                                <div :class="['w-2.5 h-2.5 rounded-full shadow-lg', statusColor.replace('text', 'bg'), props.profile?.verification_status === 'pending' ? 'animate-pulse' : '']"></div>
                                <span :class="['text-[10px] font-black uppercase tracking-[0.3em] italic', statusColor]">
                                    {{ props.profile?.verification_status.replace('_', ' ') }}
                                </span>
                             </div>
                             <p class="mt-6 text-[11px] font-medium text-gray-400 leading-relaxed uppercase tracking-widest italic">
                                <span v-if="props.profile?.verification_status === 'pending'">Vault application is in the national queue. Deployment audit estimated: 48h.</span>
                                <span v-if="props.profile?.verification_status === 'in_review'">Institutional verifier is cross-referencing global maritime services.</span>
                                <span v-if="props.profile?.verification_status === 'verified'">Master profile and supporting evidence are immutably synchronized.</span>
                             </p>
                        </div>
                    </div>
                </div>

                <!-- ── Dynamic Content Command Area ── -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- Main Profile Form -->
                    <div class="lg:col-span-12 bg-white rounded-[3rem] border border-gray-100 overflow-hidden shadow-sm">
                        <div class="px-12 py-8 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                            <div>
                                <h3 class="text-xs font-black text-indigo-600 uppercase tracking-[0.3em] italic">01 // Unified Master Profile</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Foundation of your institutional identity</p>
                            </div>
                        </div>
                        <div class="p-12">
                            <form @submit.prevent="submit" class="space-y-12">
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                                    <!-- Passport Upload -->
                                    <div class="lg:col-span-3">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-6 italic">Professional Capture</label>
                                        <div class="relative group">
                                            <div class="w-full aspect-[3/4] rounded-[2rem] bg-gray-50 border-4 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden hover:border-indigo-400 hover:bg-white transition-all cursor-pointer relative shadow-inner">
                                                <input @input="form.avatar = $event.target.files[0]" type="file" class="absolute inset-0 opacity-0 cursor-pointer z-20" accept="image/*" />
                                                <div v-if="!form.avatar && !props.profile?.avatar_url" class="text-center p-6 transition-all group-hover:scale-110">
                                                    <div class="w-16 h-16 rounded-full bg-white shadow-sm flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                                    </div>
                                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em]">Upload Source</p>
                                                </div>
                                                <div v-else class="w-full h-full">
                                                     <img v-if="form.avatar" :src="URL.createObjectURL(form.avatar)" class="w-full h-full object-cover" />
                                                     <img v-else :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-3 -right-3 w-12 h-12 bg-gray-900 border-4 border-white text-white rounded-2xl flex items-center justify-center shadow-xl group-hover:scale-110 transition-transform">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Fields -->
                                    <div class="lg:col-span-9 grid grid-cols-1 md:grid-cols-2 gap-10">
                                        <div class="md:col-span-2">
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic">Formal Display Name</label>
                                            <input v-model="form.full_name" type="text" placeholder="Institutional Name Record" class="w-full border-gray-100 bg-gray-50/30 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 rounded-2xl text-[13px] font-black px-6 py-4 transition-all placeholder:text-gray-300 uppercase tracking-tight" required />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic">Synchronization Rank</label>
                                            <input v-model="form.rank" type="text" placeholder="e.g. MASTER MARINER" class="w-full border-gray-100 bg-gray-50/30 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 rounded-2xl text-[13px] font-black px-6 py-4 transition-all placeholder:text-gray-300 uppercase tracking-tight" required />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic">Operational Logistics Region</label>
                                            <input list="regions" v-model="form.region" placeholder="Search Regional Hub..." class="w-full border-gray-100 bg-gray-50/30 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 rounded-2xl text-[13px] font-black px-6 py-4 transition-all placeholder:text-gray-300 uppercase tracking-tight" required />
                                            <datalist id="regions">
                                                <option v-for="r in indonesianRegions" :key="r" :value="r" />
                                            </datalist>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic">Direct Encrypted Comms</label>
                                            <input v-model="form.phone" type="text" placeholder="+62 ..." class="w-full border-gray-100 bg-gray-50/30 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 rounded-2xl text-[13px] font-black px-6 py-4 transition-all placeholder:text-gray-300" />
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 ml-1 italic">Primary Vessel Specialization</label>
                                            <input v-model="form.preferred_vessel_type" type="text" placeholder="e.g. VLCC / CONTAINER" class="w-full border-gray-100 bg-gray-50/30 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 rounded-2xl text-[13px] font-black px-6 py-4 transition-all placeholder:text-gray-300 uppercase tracking-tight" />
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-10 border-t border-gray-50">
                                    <button type="submit" :disabled="form.processing" class="px-14 py-5 bg-indigo-600 border border-transparent rounded-2xl font-black text-[11px] text-white uppercase tracking-[0.3em] hover:bg-indigo-700 shadow-2xl shadow-indigo-100 transition-all active:scale-95 italic disabled:opacity-50">
                                        {{ form.processing ? 'Syncing Ledger...' : 'Commit Profile Update' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Verified Module Vaults -->
                    <div class="lg:col-span-12 grid grid-cols-1 lg:grid-cols-3 gap-8" :class="{'opacity-30 grayscale pointer-events-none transition-all duration-700': completeness < 20}">
                        
                        <!-- Academic Records -->
                        <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm flex flex-col group/vault">
                            <div class="px-10 py-6 border-b border-gray-50 flex items-center justify-between bg-white group-hover/vault:bg-indigo-50/30 transition-colors">
                                <h3 class="text-[10px] font-black text-indigo-500 uppercase tracking-[0.3em] italic">Academic Intelligence</h3>
                                <button @click="showEducationModal = true" class="w-10 h-10 rounded-xl bg-gray-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-inner border border-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-8 flex-grow">
                                <div v-if="!profile?.educations?.length" class="h-40 flex flex-col items-center justify-center text-center opacity-30 italic">
                                     <svg class="w-10 h-10 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em]">No Evidence Filed</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="edu in profile.educations" :key="edu.id" class="p-5 bg-white rounded-2xl border border-gray-50 shadow-sm hover:border-indigo-400 group/item transition-all flex items-center justify-between">
                                        <div class="min-w-0 pr-4">
                                            <p class="text-[11px] font-black text-gray-900 truncate uppercase tracking-tight">{{ edu.institution_name }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-widest italic">{{ edu.degree_program }} &bull; {{ edu.graduation_year }}</p>
                                        </div>
                                        <button 
                                            v-if="edu.diploma_file_url" 
                                            @click="previewFile(edu.diploma_file_url)"
                                            class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-indigo-600 transition-all shadow-md active:scale-90"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </button>
                                        <div v-else class="w-1.5 h-1.5 rounded-full bg-indigo-200"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Vault -->
                        <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm flex flex-col group/vault">
                            <div class="px-10 py-6 border-b border-gray-50 flex items-center justify-between bg-white group-hover/vault:bg-amber-50/30 transition-colors">
                                <h3 class="text-[10px] font-black text-amber-500 uppercase tracking-[0.3em] italic">Evidence Vault</h3>
                                <button @click="showCertificateModal = true" class="w-10 h-10 rounded-xl bg-gray-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-inner border border-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-8 flex-grow overflow-y-auto max-h-[400px]">
                                <div v-if="!profile?.certificates?.length" class="h-40 flex flex-col items-center justify-center text-center opacity-30 italic">
                                     <svg class="w-10 h-10 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em]">Vault Status: Empty</p>
                                </div>
                                <div v-else class="space-y-6">
                                    <template v-for="(certs, type) in groupedCerts" :key="type">
                                        <div class="space-y-3">
                                            <p class="text-[8px] font-black text-gray-400 uppercase tracking-[0.3em] ml-1">{{ type }} Synchronization</p>
                                            <div v-for="cert in certs" :key="cert.id" class="p-5 bg-white rounded-2xl border border-gray-50 shadow-sm hover:border-amber-400 group/item transition-all flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-xl bg-gray-50 text-lg flex items-center justify-center group-hover/item:scale-110 transition-transform">{{ getCertIcon(cert.cert_type) }}</div>
                                                <div class="flex-grow min-w-0">
                                                    <p class="text-[11px] font-black text-gray-900 truncate uppercase tracking-tight">{{ cert.cert_name }}</p>
                                                    <p class="text-[9px] font-mono font-bold text-gray-400 mt-1 tracking-widest italic">{{ cert.cert_number }}</p>
                                                </div>
                                                <button 
                                                    v-if="cert.cert_file_url" 
                                                    @click="previewFile(cert.cert_file_url)"
                                                    class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-amber-600 transition-all shadow-md active:scale-90 flex-shrink-0"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Sea Service -->
                        <div class="bg-white rounded-[2.5rem] border border-gray-100 overflow-hidden shadow-sm flex flex-col group/vault">
                            <div class="px-10 py-6 border-b border-gray-50 flex items-center justify-between bg-white group-hover/vault:bg-emerald-50/30 transition-colors">
                                <h3 class="text-[10px] font-black text-emerald-500 uppercase tracking-[0.3em] italic">Experience Ledger</h3>
                                <button @click="showSeaServiceModal = true" class="w-10 h-10 rounded-xl bg-gray-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-inner border border-gray-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-8 flex-grow overflow-y-auto max-h-[400px]">
                                <div v-if="!profile?.sea_services?.length" class="h-40 flex flex-col items-center justify-center text-center opacity-30 italic">
                                     <svg class="w-10 h-10 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                    <p class="text-[9px] font-black uppercase tracking-[0.3em]">No Historical Service</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="sea in profile.sea_services" :key="sea.id" class="p-5 bg-white rounded-2xl border border-gray-50 shadow-sm hover:border-emerald-400 group/item transition-all flex items-center justify-between">
                                        <div class="min-w-0 pr-4">
                                            <p class="text-[11px] font-black text-gray-900 truncate uppercase tracking-tight">{{ sea.vessel_name }}</p>
                                            <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-widest italic">{{ sea.company_name }} &bull; {{ sea.duration_months }} Mos Logged</p>
                                        </div>
                                        <button 
                                            v-if="sea.contract_file_url" 
                                            @click="previewFile(sea.contract_file_url)"
                                            class="w-9 h-9 bg-gray-900 text-white rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-all shadow-md active:scale-90 flex-shrink-0"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        </button>
                                        <div v-else class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-[10px] font-black">🚢</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Readiness Shield (Final Submission) -->
                    <div v-if="completeness >= 20" class="lg:col-span-12 bg-gray-950 rounded-[4rem] p-16 flex flex-col lg:flex-row items-center justify-between gap-12 shadow-[0_50px_100px_-20px_rgba(0,0,0,0.4)] text-white relative overflow-hidden group">
                        <!-- BG Pulse -->
                        <div class="absolute -right-20 -top-20 w-[400px] h-[400px] bg-indigo-600/10 rounded-full blur-[100px] group-hover:scale-125 transition-transform duration-2000"></div>
                        <div class="absolute inset-0 opacity-5 pointer-events-none">
                            <svg class="w-full h-full" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none"><defs><linearGradient id="g" x1="0" x2="1" y1="0" y2="1"><stop stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#fff" stop-opacity=".1"/></linearGradient></defs><path d="M0 0h100v100H0z" fill="url(#g)"/></svg>
                        </div>
                        
                        <div class="max-w-3xl text-center lg:text-left relative z-10 space-y-5">
                             <div class="flex items-center justify-center lg:justify-start gap-4">
                                <div class="w-12 h-px bg-indigo-500"></div>
                                <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.5em] italic">Institutional Control</span>
                             </div>
                            <h3 class="text-5xl font-black uppercase tracking-tighter leading-none italic">Registry Guard Protocol</h3>
                            <p class="text-gray-400 font-medium leading-relaxed uppercase text-[11px] tracking-[0.2em] max-w-xl">Initiate institutional cross-reference for national maritime records. Verification issues an immutable digital readiness badge upon audit completion.</p>
                        </div>
                        
                        <button 
                            @click="submitVerification"
                            :disabled="completeness < 100 || profile?.verification_status !== 'unverified'"
                            class="relative z-10 px-16 py-8 bg-white text-gray-900 font-black text-[11px] uppercase tracking-[0.4em] rounded-[2rem] shadow-3xl transition-all hover:scale-105 active:scale-95 disabled:opacity-20 flex items-center gap-4 italic group/btn"
                        >
                            <span v-if="completeness < 100" class="text-indigo-600 block mr-2">{{ 100 - completeness }}% REMAINING</span>
                            {{ completeness < 100 ? 'Incomplete Assets' : 'Execute Registry Audit' }}
                            <svg class="w-5 h-5 group-hover/btn:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- MODALS -->

        <!-- Education Modal -->
        <Modal :show="showEducationModal" @close="showEducationModal = false">
            <div class="p-8">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest mb-6">Add Academic Evidence</h3>
                <form @submit.prevent="submitEdu" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Institution Name</label>
                        <input v-model="eduForm.institution_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Program / Major</label>
                            <input v-model="eduForm.degree_program" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Graduation Year</label>
                            <input v-model="eduForm.graduation_year" type="number" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Digital Diploma (PDF/Image)</label>
                        <input @input="eduForm.diploma_file = $event.target.files[0]" type="file" class="w-full text-xs font-bold text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                    </div>
                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showEducationModal = false" class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100">Store Evidence</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Certificate Modal -->
        <Modal :show="showCertificateModal" @close="showCertificateModal = false">
            <div class="p-8">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest mb-6">Vault Deposit: New Certificate</h3>
                <form @submit.prevent="submitCert" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Type</label>
                        <select v-model="certForm.cert_type" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4">
                            <option value="BST">BST (Basic Safety Training)</option>
                            <option value="COP">COP (Certificate of Proficiency)</option>
                            <option value="COC">COC (Certificate of Competency)</option>
                            <option value="OTHER">Other Credentials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Name</label>
                        <input v-model="certForm.cert_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate No.</label>
                            <input v-model="certForm.cert_number" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Issuing Authority</label>
                            <input v-model="certForm.issuing_body" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Date Issued</label>
                            <input v-model="certForm.issued_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Expiry Date</label>
                            <input v-model="certForm.expiry_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Digital Copy</label>
                        <input @input="certForm.cert_file = $event.target.files[0]" type="file" class="w-full text-xs font-bold text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
                    </div>
                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showCertificateModal = false" class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-amber-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-amber-100">Lock in Vault</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Sea Service Modal -->
        <Modal :show="showSeaServiceModal" @close="showSeaServiceModal = false">
            <div class="p-8">
                <h3 class="text-lg font-black text-gray-900 uppercase tracking-widest mb-6">Log Sea Experience</h3>
                <form @submit.prevent="submitSea" class="space-y-4 overflow-y-auto max-h-[70vh] px-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Name</label>
                            <input v-model="seaForm.vessel_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Type</label>
                            <input v-model="seaForm.vessel_type" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Company Name</label>
                        <input v-model="seaForm.company_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Rank at Time</label>
                            <input v-model="seaForm.rank_at_time" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Service Route</label>
                            <input v-model="seaForm.route" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" placeholder="e.g. Domestic / International" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sign On Date</label>
                            <input v-model="seaForm.start_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sign Off Date</label>
                            <input v-model="seaForm.end_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold p-4" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Contract/Sea Service Proof</label>
                        <input @input="seaForm.contract_file = $event.target.files[0]" type="file" class="w-full text-xs font-bold text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100" />
                    </div>
                    <div class="flex justify-end gap-3 mt-8">
                        <button type="button" @click="showSeaServiceModal = false" class="px-6 py-3 text-xs font-bold text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-100">Log Service</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- CV Preview Modal -->
        <Modal :show="showCvPreview" @close="showCvPreview = false" maxWidth="4xl">
            <div class="bg-white rounded-[3rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                 <div class="px-10 py-8 border-b border-gray-100 flex items-center justify-between bg-gray-900 text-white">
                    <div>
                         <h3 class="text-xl font-black uppercase tracking-[0.2em] italic">Digital Maritime CV</h3>
                         <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Institutional Verified Record &bull; {{ props.profile?.alumni_code }}</p>
                    </div>
                    <button @click="showCvPreview = false" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-white hover:text-gray-900 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div class="p-12 overflow-y-auto bg-white flex-grow space-y-12">
                     <!-- Header -->
                     <div class="flex items-start gap-10 border-b border-gray-100 pb-12">
                        <div class="w-40 h-52 rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-50 flex-shrink-0">
                             <img v-if="props.profile?.avatar_url" :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                             <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center font-black text-4xl text-gray-300">?</div>
                        </div>
                        <div class="flex-grow pt-4">
                             <h4 class="text-5xl font-black text-gray-900 leading-tight">{{ props.profile?.full_name }}</h4>
                             <p class="text-2xl font-bold text-indigo-600 mt-2 uppercase tracking-tight">{{ props.profile?.rank }}</p>
                             
                             <div class="grid grid-cols-2 gap-y-4 mt-8">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-20">Region</span>
                                    <span class="text-sm font-bold text-gray-800">{{ props.profile?.region }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-20">Email</span>
                                    <span class="text-sm font-bold text-gray-800">{{ $page.props.auth.user.email }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-20">Phone</span>
                                    <span class="text-sm font-bold text-gray-800">{{ props.profile?.phone }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-20">Vessel</span>
                                    <span class="text-sm font-bold text-gray-800">{{ props.profile?.preferred_vessel_type }}</span>
                                </div>
                             </div>
                        </div>
                     </div>

                     <!-- Sections -->
                     <div class="grid grid-cols-2 gap-12">
                        <!-- Left Col: Sea Service & Education -->
                        <div class="space-y-12">
                             <div>
                                <h5 class="text-xs font-black text-emerald-600 flex items-center gap-2 mb-6 uppercase tracking-widest border-b border-emerald-100 pb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                    Sea Service History
                                </h5>
                                <div class="space-y-6">
                                    <div v-for="sea in profile?.sea_services" :key="sea.id" class="relative pl-6 border-l-2 border-gray-100 group">
                                        <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500 group-hover:scale-150 transition-transform"></div>
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-sm font-black text-gray-900 uppercase">{{ sea.vessel_name }}</p>
                                                <p class="text-[11px] font-bold text-gray-500 mt-1">{{ sea.rank_at_time }} &bull; {{ sea.duration_months }} Months</p>
                                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">{{ sea.company_name }}</p>
                                            </div>
                                            <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="text-emerald-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                             </div>

                             <div>
                                <h5 class="text-xs font-black text-indigo-600 flex items-center gap-2 mb-6 uppercase tracking-widest border-b border-indigo-100 pb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                                    Academic Background
                                </h5>
                                <div class="space-y-6">
                                    <div v-for="edu in profile?.educations" :key="edu.id" class="relative pl-6 border-l-2 border-gray-100 group">
                                        <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-indigo-500 group-hover:scale-150 transition-transform"></div>
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <p class="text-sm font-black text-gray-900 uppercase">{{ edu.institution_name }}</p>
                                                <p class="text-[11px] font-bold text-gray-500 mt-1">{{ edu.degree_program }}</p>
                                                <p class="text-[10px] font-medium text-gray-400 mt-0.5">Class of {{ edu.graduation_year }}</p>
                                            </div>
                                            <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="text-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                             </div>
                        </div>

                        <!-- Right Col: Document Vault -->
                        <div>
                             <h5 class="text-xs font-black text-amber-600 flex items-center gap-2 mb-6 uppercase tracking-widest border-b border-amber-100 pb-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Document Vault (Certificates)
                             </h5>
                             <div class="space-y-4">
                                <div v-for="cert in profile?.certificates" :key="cert.id" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 flex items-center justify-between">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-gray-900 text-white uppercase">{{ cert.cert_type }}</span>
                                            <p class="text-xs font-black text-gray-900 uppercase truncate max-w-[150px]">{{ cert.cert_name }}</p>
                                        </div>
                                        <p class="text-[10px] font-mono font-bold text-gray-400 mt-1 lowercase tracking-tight">{{ cert.cert_number }}</p>
                                    </div>
                                    <div v-if="cert.verification_status === 'cleared'" class="text-emerald-500">
                                        <svg class="w-5 h-5 shadow-sm" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    </div>
                                </div>
                             </div>
                        </div>
                     </div>
                </div>

                <div class="p-8 bg-gray-50 border-t border-gray-100 flex justify-between items-center anonymous-cv-footer">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Rayterton Unified Maritime Ledger &bull; Proof of Identity</p>
                    <button @click="showCvPreview = false" class="px-10 py-4 bg-gray-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl">
                        Terminate Session
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Institutional File Preview Modal -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="4xl">
            <div class="bg-gray-900 rounded-[2.5rem] overflow-hidden shadow-2xl border border-white/10 flex flex-col max-h-[90vh]">
                <div class="px-8 py-5 border-b border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <h3 class="text-[10px] font-black text-white uppercase tracking-[0.3em] italic">Evidence Inspection</h3>
                    </div>
                    <button @click="showPreviewModal = false" class="text-white/40 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="flex-grow p-4 bg-slate-900 overflow-hidden flex items-center justify-center bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-slate-800 to-slate-900">
                    <div v-if="currentPreview" class="w-full h-full flex items-center justify-center">
                        <img v-if="isImage(currentPreview)" :src="currentPreview" class="max-w-full max-h-[75vh] object-contain rounded-xl shadow-2xl" />
                        <iframe v-else :src="currentPreview" class="w-full h-[75vh] rounded-xl border border-white/5 shadow-2xl bg-white"></iframe>
                    </div>
                </div>
                <div class="px-10 py-6 bg-black flex justify-between items-center border-t border-white/5">
                    <div class="flex items-center gap-4">
                        <p class="text-[9px] font-black text-white/30 uppercase tracking-[0.2em]">Institutional Asset Audit</p>
                    </div>
                    <a :href="currentPreview" download class="px-8 py-3 bg-white text-gray-900 text-[10px] font-black uppercase tracking-[0.3em] rounded-xl hover:bg-indigo-400 hover:text-white transition-all shadow-xl italic">
                        Download Master File
                    </a>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
