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
        _method: 'put',
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

const eduForm = useForm({
    institution_name: '',
    degree_program: '',
    graduation_year: '',
    diploma_file: null,
});

const certForm = useForm({
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
                    <div v-if="isVerified" class="flex items-center gap-3">
                        <button 
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
                                    <span>Verified Profile</span>
                                    <span :class="isVerified ? 'text-emerald-400' : 'text-gray-500'">{{ isVerified ? 'ON' : 'OFF' }}</span>
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
                                <h3 class="text-lg font-bold text-gray-900">1. Master Profile Setup</h3>
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
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Academic Records</h3>
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
                                        <p class="text-xs font-black text-gray-900 truncate">{{ edu.institution_name }}</p>
                                        <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase">{{ edu.degree_program }} &bull; {{ edu.graduation_year }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates -->
                        <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Sea Certifications</h3>
                                <button @click="showCertificateModal = true" class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                            <div class="p-6 flex-grow">
                                <div v-if="!profile?.certificates?.length" class="h-32 flex flex-col items-center justify-center text-center">
                                    <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">No Records Found</p>
                                </div>
                                <div v-else class="space-y-4">
                                    <div v-for="cert in profile.certificates" :key="cert.id" class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group hover:border-amber-300 transition-colors">
                                        <p class="text-xs font-black text-gray-900 truncate">{{ cert.cert_name }}</p>
                                        <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-widest">No: {{ cert.cert_number }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sea Service -->
                        <div class="lg:col-span-1 bg-white rounded-3xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">
                            <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em]">Sea Experience</h3>
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
                                        <p class="text-xs font-black text-gray-900 truncate">{{ sea.vessel_name }}</p>
                                        <p class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-widest">{{ sea.company_name }} &bull; {{ sea.duration_months }} Months</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Final Submission -->
                    <div v-if="completeness >= 20" class="bg-indigo-600 rounded-3xl p-10 flex flex-col md:flex-row items-center justify-between gap-8 shadow-2xl shadow-indigo-100 text-white">
                        <div class="max-w-2xl text-center md:text-left">
                            <h3 class="text-2xl font-black uppercase tracking-tight">Institutional Gaurantee Submission</h3>
                            <p class="text-indigo-100 mt-2 font-medium opacity-80 leading-relaxed">By requesting verification, you authorize our institutional teams to digitally cross-reference your submitted academic and maritime records with national registries.</p>
                        </div>
                        <button 
                            @click="submitVerification"
                            :disabled="completeness < 100 || profile?.verification_status !== 'unverified'"
                            class="px-10 py-5 bg-white text-indigo-600 font-black text-xs uppercase tracking-[0.2em] rounded-2xl shadow-xl transition-all hover:scale-105 active:scale-95 disabled:opacity-40"
                        >
                            {{ completeness < 100 ? (100 - completeness) + '% Required' : 'Request Registry Guard' }}
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- Modals (Refined Style) -->
        <Modal :show="showEducationModal" @close="showEducationModal = false">
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900">Add Academic Evidence</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 border-b border-gray-100 pb-6 mb-8">Verification against University/Training registry.</p>
                <form @submit.prevent="submitEdu" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">School / University / Training Center</label>
                        <input v-model="eduForm.institution_name" type="text" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Major / Degree</label>
                            <input v-model="eduForm.degree_program" type="text" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Year Graduated</label>
                            <input v-model="eduForm.graduation_year" type="number" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Diploma Document (Evidence)</label>
                        <input @input="eduForm.diploma_file = $event.target.files[0]" type="file" class="w-full text-xs" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" />
                    </div>
                    <div class="mt-10 flex justify-end gap-4">
                        <button type="button" @click="showEducationModal = false" class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100">Store Evidence</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Certificate/Sea Service Modals similar structure... -->
        <Modal :show="showCertificateModal" @close="showCertificateModal = false">
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900">Add Maritime Certification</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 border-b border-gray-100 pb-6 mb-8">Registry cross-check for STCW/COP certificates.</p>
                <form @submit.prevent="submitCert" class="space-y-6">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Full Name</label>
                        <input v-model="certForm.cert_name" type="text" placeholder="e.g. Basic Safety Training" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Serial Number</label>
                            <input v-model="certForm.cert_number" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Issuing Body</label>
                            <input v-model="certForm.issuing_body" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Issued On</label>
                            <input v-model="certForm.issued_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Expiry Date</label>
                            <input v-model="certForm.expiry_date" type="date" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" />
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Certification Scan (Evidence)</label>
                        <input @input="certForm.cert_file = $event.target.files[0]" type="file" class="w-full text-xs" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" />
                    </div>
                    <div class="mt-10 flex justify-end gap-4">
                        <button type="button" @click="showCertificateModal = false" class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100">Store Evidence</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showSeaServiceModal" @close="showSeaServiceModal = false">
            <div class="p-8">
                <h2 class="text-2xl font-black text-gray-900">Add Sea Experience</h2>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1 border-b border-gray-100 pb-6 mb-8">Verification via Sign on/off Records.</p>
                <form @submit.prevent="submitSea" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Name</label>
                            <input v-model="seaForm.vessel_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Duration (Months)</label>
                            <input v-model="seaForm.duration_months" type="number" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Company / Principal</label>
                        <input v-model="seaForm.company_name" type="text" class="w-full border-gray-200 rounded-xl text-sm font-bold px-4 py-3" required />
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Rank at the time</label>
                            <input v-model="seaForm.rank_at_time" type="text" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Type</label>
                            <input v-model="seaForm.vessel_type" type="text" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Route (Trade Area)</label>
                        <input v-model="seaForm.route" type="text" placeholder="e.g. World Wide / Domestic" class="w-full border-gray-200 focus:ring-0 focus:border-indigo-600 rounded-xl text-sm font-bold px-4 py-3" required />
                    </div>
                    <div class="bg-gray-50 p-6 rounded-2xl border border-dashed border-gray-200">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Contract / Sign On-Off Scan</label>
                        <input @input="seaForm.contract_file = $event.target.files[0]" type="file" class="w-full text-xs" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx" />
                    </div>
                    <div class="mt-10 flex justify-end gap-4">
                        <button type="button" @click="showSeaServiceModal = false" class="px-6 py-3 text-xs font-black text-gray-400 uppercase tracking-widest">Cancel</button>
                        <button type="submit" class="px-8 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100">Store Evidence</button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
