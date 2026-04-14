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
        <div class="py-10 bg-gray-50/50 min-h-screen">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-8">
                
                <!-- Page Title -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Living Professional Identity</h1>
                        <p class="text-sm font-medium text-gray-500 mt-1">Verified records that act as an institutional guarantee of candidate readiness.</p>
                    </div>
                    <div class="flex items-center gap-3">
                         <button 
                            @click="showCvPreview = true"
                            class="px-6 py-2.5 bg-gray-900 text-white rounded-xl text-sm font-bold shadow-xl hover:bg-black transition-all flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            View Maritime CV
                        </button>
                        <button 
                            v-if="isVerified"
                            @click="toggleAvailability"
                            :class="[
                                'px-6 py-2.5 rounded-xl text-sm font-bold shadow-sm transition-all border',
                                props.profile?.availability_status === 'open_to_offers' 
                                    ? 'bg-emerald-600 text-white border-emerald-500 hover:bg-emerald-700' 
                                    : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                            ]"
                        >
                            {{ props.profile?.availability_status === 'open_to_offers' ? '✓ Open to Offers' : 'Mark as Available' }}
                        </button>
                    </div>
                </div>

                <!-- Snapshot Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200 shadow-sm overflow-hidden flex flex-col md:flex-row">
                        <div class="p-8 border-r border-gray-100 bg-gray-50/30 flex flex-col items-center justify-center min-w-[200px]">
                            <div class="relative group">
                                <div v-if="props.profile?.avatar_url" class="w-32 h-32 rounded-2xl overflow-hidden shadow-lg border-4 border-white">
                                    <img :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                                </div>
                                <div v-else class="w-32 h-32 rounded-2xl bg-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-lg border-4 border-white">
                                    {{ props.profile?.full_name?.charAt(0) || '?' }}
                                </div>
                                <div v-if="isVerified" class="absolute -bottom-2 -right-2 bg-white p-1 rounded-full border border-gray-100 shadow-sm">
                                    <div class="bg-emerald-500 rounded-full p-1">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 text-center">
                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-1">Institutional ID</p>
                                <div class="px-4 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-mono font-bold tracking-wider shadow-sm">
                                    {{ props.profile?.alumni_code || 'SN: PENDING' }}
                                </div>
                            </div>
                        </div>
                        <div class="p-8 flex-grow">
                            <div class="flex items-center gap-3">
                                <h2 class="text-3xl font-black text-gray-900 leading-none">{{ props.profile?.full_name || 'Incomplete Profile' }}</h2>
                                <span v-if="isVerified" class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-widest border border-emerald-100">Certified</span>
                            </div>
                            <p class="text-gray-500 font-bold mt-2 text-lg">{{ props.profile?.rank || 'Rank not established' }}</p>
                            
                            <div class="grid grid-cols-2 gap-8 mt-8">
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Region</p>
                                    <p class="text-sm font-bold text-gray-800">{{ props.profile?.region || '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Preference</p>
                                    <p class="text-sm font-bold text-gray-800">{{ props.profile?.preferred_vessel_type || '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-6 mt-8 pt-8 border-t border-gray-100">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600">{{ $page.props.auth.user.email }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="text-xs font-bold text-gray-600">{{ props.profile?.phone || '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-gray-900 rounded-3xl p-8 shadow-xl text-white">
                            <div class="flex items-center justify-between mb-6">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Strength Point</span>
                                <span class="text-xl font-black text-indigo-400">{{ completeness }}%</span>
                            </div>
                            <div class="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-indigo-500 h-2 rounded-full transition-all duration-1000" :style="{ width: completeness + '%' }"></div>
                            </div>
                            <p class="mt-6 text-xs font-medium text-gray-400 leading-relaxed">Higher scores increase your visibility to verified companies and priority hiring queues.</p>
                            
                            <div class="mt-8 space-y-4">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest p-3 bg-white/5 rounded-xl border border-white/10">
                                    <span>Verified Status</span>
                                    <span :class="isVerified ? 'text-emerald-400' : 'text-gray-500'">{{ props.profile?.verification_status.toUpperCase() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="props.profile?.verification_status !== 'unverified'" class="bg-white rounded-3xl border border-gray-200 p-8 shadow-sm group">
                             <div class="flex items-center gap-3">
                                <div :class="['w-3 h-3 rounded-full', statusColor.replace('text', 'bg')]"></div>
                                <span :class="['text-xs font-black uppercase tracking-widest', statusColor]">
                                    {{ props.profile?.verification_status.replace('_', ' ') }}
                                </span>
                             </div>
                             <p class="mt-4 text-xs font-medium text-gray-500 leading-relaxed">
                                <span v-if="props.profile?.verification_status === 'pending'">Application is currently in the national verification queue. Review usually takes 1-2 business days.</span>
                                <span v-if="props.profile?.verification_status === 'in_review'">Institutional verifier is currently cross-referencing your sea service certificates.</span>
                                <span v-if="props.profile?.verification_status === 'verified'">Master profile and supporting digital evidence have been approved and recorded on the ledger.</span>
                             </p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
                    
                    <!-- Section 1: Identity & Preferences -->
                    <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm">
                        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 text-indigo-600">1. Master Profile Setup</h3>
                                <p class="text-xs font-medium text-gray-500 mt-0.5">Define your professional identity and working preferences.</p>
                            </div>
                        </div>
                        <div class="p-8">
                            <form @submit.prevent="submit" class="space-y-8">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                    <!-- Avatar Upload -->
                                    <div class="md:col-span-1">
                                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Passport Photo (Standard)</label>
                                        <div class="flex flex-col items-center">
                                            <div class="w-full aspect-[3/4] rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center overflow-hidden hover:border-indigo-400 transition-colors cursor-pointer relative">
                                                <input @input="form.avatar = $event.target.files[0]" type="file" class="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                                <div v-if="!form.avatar && !props.profile?.avatar_url" class="text-center p-4">
                                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Upload JPEG/PNG</p>
                                                </div>
                                                <div v-else class="w-full h-full">
                                                     <img v-if="form.avatar" :src="URL.createObjectURL(form.avatar)" class="w-full h-full object-cover" />
                                                     <img v-else :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                                                </div>
                                            </div>
                                            <p class="mt-2 text-[10px] text-gray-400">Max size: 2MB. Clear background preferred.</p>
                                        </div>
                                    </div>

                                    <!-- Form Inputs -->
                                    <div class="md:col-span-2 space-y-6">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Display Name (Full Name)</label>
                                            <input v-model="form.full_name" type="text" placeholder="As written in STCW Certificates" class="w-full border-gray-200 focus:border-indigo-600 focus:ring-0 rounded-xl text-sm font-bold px-4 py-3 placeholder:text-gray-300" required />
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Current Rank</label>
                                                <input v-model="form.rank" type="text" placeholder="e.g. Chief Officer" class="w-full border-gray-200 focus:border-indigo-600 focus:ring-0 rounded-xl text-sm font-bold px-4 py-3" required />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Current Region</label>
                                                <input list="regions" v-model="form.region" placeholder="Select or type region" class="w-full border-gray-200 focus:border-indigo-600 focus:ring-0 rounded-xl text-sm font-bold px-4 py-3" required />
                                                <datalist id="regions">
                                                    <option v-for="r in indonesianRegions" :key="r" :value="r" />
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Phone No (WA)</label>
                                                <input v-model="form.phone" type="text" class="w-full border-gray-200 focus:border-indigo-600 focus:ring-0 rounded-xl text-sm font-bold px-4 py-3" />
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Vessel Preference</label>
                                                <input v-model="form.preferred_vessel_type" type="text" class="w-full border-gray-200 focus:border-indigo-600 focus:ring-0 rounded-xl text-sm font-bold px-4 py-3" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end pt-6 border-t border-gray-100">
                                    <button type="submit" :disabled="form.processing" class="px-10 py-4 bg-gray-900 border border-transparent rounded-2xl font-black text-xs text-white uppercase tracking-widest hover:bg-black transition shadow-xl shadow-gray-200 disabled:opacity-50">
                                        {{ form.processing ? 'Syncing...' : 'Update Master Profile' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Verified Modules Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" :class="{'opacity-40 grayscale pointer-events-none': completeness < 20}">
                        
                        <!-- Education -->
                        <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] text-indigo-600">Academic Records</h3>
                                <button @click="showEducationModal = true" class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-6 flex-grow">
                                <div v-if="!profile?.educations?.length" class="h-32 flex flex-col items-center justify-center text-center">
                                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">No Evidence Submitted</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="edu in profile.educations" :key="edu.id" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-indigo-300 transition-colors">
                                        <p class="text-xs font-black text-gray-900 truncate uppercase">{{ edu.institution_name }}</p>
                                        <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase">{{ edu.degree_program }} &bull; {{ edu.graduation_year }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Vault -->
                        <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-amber-50/20">
                                <div>
                                    <h3 class="text-xs font-black text-amber-600 uppercase tracking-[0.2em]">Document Vault</h3>
                                    <p class="text-[8px] font-bold text-amber-500 uppercase tracking-widest mt-0.5">BST / COP / COC</p>
                                </div>
                                <button @click="showCertificateModal = true" class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-6 flex-grow">
                                <div v-if="!profile?.certificates?.length" class="h-32 flex flex-col items-center justify-center text-center">
                                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">Vault is Empty</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <template v-for="(certs, type) in groupedCerts" :key="type">
                                        <p class="text-[8px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2 px-1">{{ type }} Collections</p>
                                        <div v-for="cert in certs" :key="cert.id" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-amber-300 transition-colors mb-3">
                                            <div class="flex items-center gap-3">
                                                <span class="text-lg">{{ getCertIcon(cert.cert_type) }}</span>
                                                <div class="flex-grow min-w-0">
                                                    <p class="text-xs font-black text-gray-900 truncate uppercase">{{ cert.cert_name }}</p>
                                                    <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-widest">{{ cert.cert_number }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Sea Service -->
                        <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] text-emerald-600">Sea Experience</h3>
                                <button @click="showSeaServiceModal = true" class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-6 flex-grow">
                                <div v-if="!profile?.sea_services?.length" class="h-32 flex flex-col items-center justify-center text-center">
                                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">No Sea Service Logs</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="sea in profile.sea_services" :key="sea.id" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-emerald-300 transition-colors">
                                        <p class="text-xs font-black text-gray-900 truncate uppercase">{{ sea.vessel_name }}</p>
                                        <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-widest">{{ sea.company_name }} &bull; {{ sea.duration_months }} Months</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Final Submission -->
                    <div v-if="completeness >= 20" class="bg-gray-900 rounded-[3rem] p-12 flex flex-col md:flex-row items-center justify-between gap-10 shadow-2xl text-white relative overflow-hidden">
                        <div class="absolute -right-20 -top-20 opacity-10">
                            <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 6c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 12.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                        </div>
                        <div class="max-w-2xl text-center md:text-left z-10">
                            <h3 class="text-3xl font-black uppercase tracking-tight italic">Registry Guard Protocol</h3>
                            <p class="text-gray-400 mt-4 font-medium leading-relaxed uppercase text-xs tracking-widest">Initiate institutional cross-reference for national maritime records. Verification issues an immutable digital readiness badge.</p>
                        </div>
                        <button 
                            @click="submitVerification"
                            :disabled="completeness < 100 || profile?.verification_status !== 'unverified'"
                            class="px-12 py-6 bg-white text-gray-900 font-black text-xs uppercase tracking-[0.3em] rounded-2xl shadow-2xl transition-all hover:scale-105 active:scale-95 disabled:opacity-30 z-10"
                        >
                            {{ completeness < 100 ? (100 - completeness) + '% To Unlock' : 'Request Verification' }}
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
                                    <div v-for="sea in profile?.sea_services" :key="sea.id" class="relative pl-6 border-l-2 border-gray-100">
                                        <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-emerald-500"></div>
                                        <p class="text-sm font-black text-gray-900 uppercase">{{ sea.vessel_name }}</p>
                                        <p class="text-[11px] font-bold text-gray-500 mt-1">{{ sea.rank_at_time }} &bull; {{ sea.duration_months }} Months</p>
                                        <p class="text-[10px] font-medium text-gray-400 mt-0.5">{{ sea.company_name }}</p>
                                    </div>
                                </div>
                             </div>

                             <div>
                                <h5 class="text-xs font-black text-indigo-600 flex items-center gap-2 mb-6 uppercase tracking-widest border-b border-indigo-100 pb-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                                    Academic Background
                                </h5>
                                <div class="space-y-6">
                                    <div v-for="edu in profile?.educations" :key="edu.id" class="relative pl-6 border-l-2 border-gray-100">
                                        <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-indigo-500"></div>
                                        <p class="text-sm font-black text-gray-900 uppercase">{{ edu.institution_name }}</p>
                                        <p class="text-[11px] font-bold text-gray-500 mt-1">{{ edu.degree_program }}</p>
                                        <p class="text-[10px] font-medium text-gray-400 mt-0.5">Class of {{ edu.graduation_year }}</p>
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

    </AuthenticatedLayout>
</template>
