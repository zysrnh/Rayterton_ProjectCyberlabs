<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    profile: { type: Object, default: null }
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

const submit = () => form.post(route('alumni.master-profile.update'), { preserveScroll: true, forceFormData: true });
const submitVerification = () => router.post(route('alumni.verify.submit'), {}, { preserveScroll: true });
const toggleAvailability = () => router.post(route('alumni.toggle_availability'), {}, { preserveScroll: true });

const completeness = computed(() => props.profile?.profile_completeness || 0);
const isVerified = computed(() => props.profile?.verification_status === 'verified');
const isPending = computed(() => ['pending', 'in_review'].includes(props.profile?.verification_status));

const indonesianRegions = [
    'Aceh','Sumatera Utara','Sumatera Barat','Riau','Kepulauan Riau','Jambi','Sumatera Selatan','Bangka Belitung','Bengkulu','Lampung',
    'DKI Jakarta','Jawa Barat','Banten','Jawa Tengah','DI Yogyakarta','Jawa Timur',
    'Bali','Nusa Tenggara Barat','Nusa Tenggara Timur',
    'Kalimantan Barat','Kalimantan Tengah','Kalimantan Selatan','Kalimantan Timur','Kalimantan Utara',
    'Sulawesi Utara','Sulawesi Tengah','Sulawesi Selatan','Sulawesi Tenggara','Gorontalo','Sulawesi Barat',
    'Maluku','Maluku Utara','Papua','Papua Barat','Papua Selatan','Papua Tengah','Papua Pegunungan','Papua Barat Daya'
];

const showEducationModal = ref(false);
const showCertificateModal = ref(false);
const showSeaServiceModal = ref(false);
const showCvPreview = ref(false);
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

const eduForm = useForm({ institution_name: '', degree_program: '', graduation_year: '', diploma_file: null });
const certForm = useForm({ cert_type: 'BST', cert_name: '', cert_number: '', issuing_body: '', issued_date: '', expiry_date: '', cert_file: null });
const seaForm = useForm({ vessel_name: '', vessel_type: '', company_name: '', rank_at_time: '', start_date: '', end_date: '', route: '', contract_file: null, duration_months: 0 });

const submitEdu = () => eduForm.post(route('alumni.educations.store'), { preserveScroll: true, forceFormData: true, onSuccess: () => { showEducationModal.value = false; eduForm.reset(); } });
const submitCert = () => certForm.post(route('alumni.certificates.store'), { preserveScroll: true, forceFormData: true, onSuccess: () => { showCertificateModal.value = false; certForm.reset(); } });
const submitSea = () => seaForm.post(route('alumni.seasearvices.store'), { preserveScroll: true, forceFormData: true, onSuccess: () => { showSeaServiceModal.value = false; seaForm.reset(); } });

const isExpiringSoon = (date) => {
    if (!date) return false;
    const expiry = new Date(date);
    const now = new Date();
    const diff = (expiry - now) / (1000 * 60 * 60 * 24);
    return diff <= 7 && diff > 0;
};

const isExpired = (date) => {
    if (!date) return false;
    return new Date(date) < new Date();
};

const getDaysRemaining = (date) => {
    if (!date) return 0;
    const diffTime = new Date(date) - new Date();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

const groupedCerts = computed(() => {
    if (!props.profile?.certificates) return {};
    return props.profile.certificates.reduce((acc, cert) => {
        if (!acc[cert.cert_type]) acc[cert.cert_type] = [];
        acc[cert.cert_type].push({
            ...cert,
            expiringSoon: isExpiringSoon(cert.expiry_date),
            expired: isExpired(cert.expiry_date),
            daysLeft: getDaysRemaining(cert.expiry_date)
        });
        return acc;
    }, {});
});

const statusMap = {
    unverified:  { label: 'Unverified',  color: 'text-gray-400',   bg: 'bg-gray-100',     dot: 'bg-gray-400' },
    pending:     { label: 'Pending',      color: 'text-amber-600',  bg: 'bg-amber-50',     dot: 'bg-amber-500 animate-pulse' },
    in_review:   { label: 'In Review',   color: 'text-indigo-600', bg: 'bg-indigo-50',    dot: 'bg-indigo-500 animate-bounce' },
    verified:    { label: 'Verified',    color: 'text-emerald-600',bg: 'bg-emerald-50',   dot: 'bg-emerald-500' },
    rejected:    { label: 'Rejected',    color: 'text-rose-600',   bg: 'bg-rose-50',      dot: 'bg-rose-500' },
};
const currentStatus = computed(() => statusMap[props.profile?.verification_status] || statusMap.unverified);
</script>

<template>
    <Head title="Professional Profile" />
    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F6FA] py-10">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-10 space-y-8">

                <!-- ── Page Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-8 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-3 py-1 rounded-lg uppercase tracking-widest">Professional Ledger</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Identity Hub</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight">Living Professional Identity</h1>
                        <p class="text-sm text-gray-500 mt-1 max-w-lg">Verified records acting as an institutional guarantee of candidate readiness for global maritime operations.</p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <button @click="showCvPreview = true" class="inline-flex items-center gap-2.5 px-5 py-3 bg-gray-950 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg hover:bg-indigo-600 transition-all active:scale-95 border border-white/5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Maritime CV
                        </button>
                        <button v-if="isVerified" @click="toggleAvailability" :class="['inline-flex items-center gap-2.5 px-5 py-3 text-[10px] font-black uppercase tracking-widest rounded-xl border-2 transition-all active:scale-95', props.profile?.availability_status === 'open_to_offers' ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg shadow-emerald-200' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-400']">
                            <span :class="['w-2 h-2 rounded-full', props.profile?.availability_status === 'open_to_offers' ? 'bg-emerald-300' : 'bg-gray-300']"></span>
                            {{ props.profile?.availability_status === 'open_to_offers' ? 'Open to Offers' : 'Mark Available' }}
                        </button>
                    </div>
                </div>

                <!-- Verification Shield banner -->
                <div v-if="isVerified" class="p-6 bg-emerald-600 rounded-[2.5rem] text-white flex flex-col md:flex-row items-center justify-between shadow-2xl shadow-emerald-200 group relative overflow-hidden transition-all hover:scale-[1.01]">
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="text-center md:text-left">
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] opacity-80 italic">Rayterton Identity Cleared</p>
                            <h3 class="text-2xl font-black tracking-tight leading-tight">Registry Verification Shield Active</h3>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 relative z-10 mt-4 md:mt-0">
                        <span class="px-5 py-2 bg-white/10 rounded-xl text-[9px] font-black uppercase tracking-widest border border-white/20 backdrop-blur-lg italic">Compliance Secured</span>
                    </div>
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0 100 L100 0 L100 100 Z" fill="white"></path></svg>
                    </div>
                </div>

                <!-- ── Identity Snapshot ── -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Profile Card -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="flex flex-col sm:flex-row">
                            <!-- Avatar -->
                            <div class="sm:w-56 bg-gray-50 border-r border-gray-100 p-8 flex flex-col items-center justify-center gap-6 flex-shrink-0">
                                <div :class="['relative w-32 h-32 rounded-2xl overflow-hidden shadow-xl', isVerified ? 'ring-4 ring-emerald-400/40 ring-offset-2' : '']">
                                    <img v-if="props.profile?.avatar_url" :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full bg-gray-800 flex items-center justify-center text-white text-4xl font-black">{{ props.profile?.full_name?.charAt(0) || '?' }}</div>
                                    <div v-if="isVerified" class="absolute -bottom-2 -right-2 bg-emerald-500 text-white p-1.5 rounded-xl shadow-lg border-4 border-white">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-900 text-indigo-400 rounded-xl py-2.5 text-center text-[10px] font-mono font-black tracking-widest border border-gray-800 shadow-inner">
                                    {{ props.profile?.alumni_code || 'AWAITING ID' }}
                                </div>
                            </div>

                            <!-- Identity Details -->
                            <div class="p-8 flex-grow">
                                <div class="flex flex-wrap items-start gap-3 mb-1">
                                    <h2 class="text-2xl font-black text-gray-900 tracking-tight leading-tight">{{ props.profile?.full_name || 'Complete Your Profile' }}</h2>
                                    <div :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border text-[10px] font-black uppercase tracking-wider', currentStatus.bg, currentStatus.color, 'border-current/20']">
                                        <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', currentStatus.dot]"></span>
                                        {{ currentStatus.label }}
                                    </div>
                                </div>
                                <p class="text-sm font-black text-indigo-500 uppercase tracking-widest">{{ props.profile?.rank || '—' }}</p>

                                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100">
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Region</p>
                                        <p class="text-sm font-bold text-gray-800">{{ props.profile?.region || '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Vessel Type</p>
                                        <p class="text-sm font-bold text-gray-800">{{ props.profile?.preferred_vessel_type || '—' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Email</p>
                                        <p class="text-sm font-bold text-gray-800 truncate">{{ $page.props.auth.user.email }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Phone</p>
                                        <p class="text-sm font-bold text-gray-800">{{ props.profile?.phone || '—' }}</p>
                                    </div>
                                </div>

                                <!-- Status message -->
                                <div v-if="isPending" class="mt-5 flex items-start gap-3 bg-amber-50 border border-amber-100 rounded-xl p-4">
                                    <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-[11px] font-medium text-amber-700 leading-relaxed">
                                        <span v-if="props.profile?.verification_status === 'pending'">Your application is in the national queue. Estimated audit: 48 hours.</span>
                                        <span v-if="props.profile?.verification_status === 'in_review'">Institutional verifier is cross-referencing your maritime records.</span>
                                    </p>
                                </div>
                                <div v-if="isVerified" class="mt-5 flex items-center gap-3 bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                                    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-widest">Profile & supporting documents immutably verified.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- ── Readiness Score Card ── -->
                <div class="bg-gray-950 rounded-2xl p-8 text-white shadow-[0_20px_50px_rgba(0,0,0,0.3)] relative overflow-hidden flex flex-col justify-between group">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-[80px] group-hover:scale-150 transition-transform duration-1000"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2 relative z-10">
                            <span class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.3em] italic">Readiness Level</span>
                            <span class="text-4xl font-black italic tabular-nums">{{ completeness }}<span class="text-2xl text-gray-600">%</span></span>
                        </div>
                        <div class="w-full bg-white/5 rounded-full h-3 overflow-hidden mt-6 relative z-10 border border-white/5 shadow-inner">
                            <div class="bg-gradient-to-r from-indigo-600 via-indigo-400 to-indigo-500 h-full rounded-full transition-all duration-1000 shadow-[0_0_20px_rgba(79,70,229,0.4)]" :style="{ width: completeness + '%' }"></div>
                        </div>
                        <div class="mt-8 space-y-3 relative z-10">
                            <div v-for="(task, idx) in [
                                { label: 'Master Profile', done: props.profile?.full_name },
                                { label: 'Identity Capture', done: props.profile?.avatar_url },
                                { label: 'Academic Evidence', done: props.profile?.educations?.length },
                                { label: 'Maritime Credentials', done: props.profile?.certificates?.length },
                                { label: 'Service History', done: props.profile?.sea_services?.length }
                            ]" :key="idx" :class="['flex items-center gap-3 text-[10px] transition-all duration-500', task.done ? 'text-white translate-x-1' : 'text-gray-600']">
                                <svg :class="['w-3.5 h-3.5', task.done ? 'text-indigo-400' : 'text-gray-700']" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                <span class="font-black uppercase tracking-widest italic">{{ task.label }}</span>
                            </div>
                        </div>
                    </div>
                    <button
                        @click="submitVerification"
                        :disabled="completeness < 100 || props.profile?.verification_status !== 'unverified'"
                        class="mt-10 w-full py-4 bg-white text-gray-900 border border-white hover:bg-transparent hover:text-white disabled:bg-white/5 disabled:border-white/5 disabled:text-gray-600 text-[10px] font-black uppercase tracking-[0.3em] rounded-xl transition-all active:scale-95 disabled:cursor-not-allowed shadow-2xl italic relative z-10"
                    >
                        {{ completeness < 100 ? `SYNC ${100 - completeness}% REMAINING` : props.profile?.verification_status !== 'unverified' ? 'ENCRYPTED & FILED' : 'EXECUTE REGISTRY AUDIT' }}
                    </button>
                </div>
                </div>

                <!-- ── Profile Form ── -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-8 py-5 border-b border-gray-100 flex items-center gap-3 bg-gray-50/60">
                        <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest">01 · Master Profile</h3>
                            <p class="text-[10px] text-gray-400 font-medium">Foundation of your institutional identity</p>
                        </div>
                    </div>

                    <div class="p-8">
                        <form @submit.prevent="submit">
                            <div class="flex flex-col lg:flex-row gap-10">
                                <!-- Avatar Upload -->
                                <div class="flex-shrink-0">
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Photo</label>
                                    <div class="relative group w-40">
                                        <div class="w-40 h-52 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 overflow-hidden relative cursor-pointer hover:border-indigo-400 hover:bg-white transition-all">
                                            <input @input="form.avatar = $event.target.files[0]" type="file" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*" />
                                            <div v-if="!form.avatar && !props.profile?.avatar_url" class="w-full h-full flex flex-col items-center justify-center gap-3 text-center p-4">
                                                <div class="w-12 h-12 rounded-xl bg-white shadow-sm border border-gray-100 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                </div>
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Upload Photo</p>
                                            </div>
                                            <div v-else class="w-full h-full">
                                                <img v-if="form.avatar" :src="URL.createObjectURL(form.avatar)" class="w-full h-full object-cover" />
                                                <img v-else :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                                            </div>
                                        </div>
                                        <div class="absolute -bottom-2.5 -right-2.5 w-9 h-9 bg-gray-900 border-4 border-white text-white rounded-xl flex items-center justify-center shadow-lg z-20 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/></svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fields -->
                                <div class="flex-grow grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="md:col-span-2">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                                        <input v-model="form.full_name" type="text" placeholder="Your institutional full name" class="w-full border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-sm font-bold px-4 py-3.5 transition-all placeholder:text-gray-300 placeholder:font-normal" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Rank / Position</label>
                                        <input v-model="form.rank" type="text" placeholder="e.g. Master Mariner" class="w-full border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-sm font-bold px-4 py-3.5 transition-all placeholder:text-gray-300 placeholder:font-normal" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Region</label>
                                        <input list="regions" v-model="form.region" placeholder="Search your region..." class="w-full border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-sm font-bold px-4 py-3.5 transition-all placeholder:text-gray-300 placeholder:font-normal" required />
                                        <datalist id="regions"><option v-for="r in indonesianRegions" :key="r" :value="r" /></datalist>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Phone Number</label>
                                        <input v-model="form.phone" type="text" placeholder="+62 ..." class="w-full border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-sm font-bold px-4 py-3.5 transition-all placeholder:text-gray-300 placeholder:font-normal" />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Preferred Vessel Type</label>
                                        <input v-model="form.preferred_vessel_type" type="text" placeholder="e.g. VLCC / Container" class="w-full border border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 rounded-xl text-sm font-bold px-4 py-3.5 transition-all placeholder:text-gray-300 placeholder:font-normal" />
                                    </div>
                                    <div class="md:col-span-2 flex justify-end pt-2">
                                        <button type="submit" :disabled="form.processing" class="px-8 py-3.5 bg-indigo-600 text-white text-[11px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95 disabled:opacity-50">
                                            {{ form.processing ? 'Saving...' : 'Save Profile' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- ── Document Vault Stack ── -->
                <div class="space-y-10">
                    
                    <!-- Academic intelligence Table -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ring-1 ring-black/5">
                        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-100 ml-[-4px]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">Academic Intelligence</h3>
                                    <p class="text-[9px] text-indigo-400 font-bold uppercase tracking-widest mt-0.5">Verified Scholastic Records</p>
                                </div>
                            </div>
                            <button @click="showEducationModal = true" class="px-5 py-2.5 bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-700 transition-all flex items-center gap-2 shadow-lg shadow-indigo-50 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                Add Entry
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 bg-gray-50/50">
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[35%]">Institution</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[30%]">Degree / Major</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[20%] text-center">Year</th>
                                        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest w-[15%]">Evidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!props.profile?.educations?.length">
                                        <td colspan="4" class="px-8 py-16 text-center text-[11px] font-bold text-gray-300 uppercase tracking-[0.3em] italic">No academic records synchronized to ledger</td>
                                    </tr>
                                    <tr v-for="edu in props.profile?.educations" :key="edu.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                </div>
                                                <p class="text-sm font-black text-gray-900 uppercase tracking-tight truncate">{{ edu.institution_name }}</p>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-xs font-bold text-gray-500 uppercase tracking-wide truncate">{{ edu.degree_program }}</td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="px-4 py-1.5 bg-indigo-50 text-indigo-600 text-[10px] font-black rounded-lg border border-indigo-100 inline-block">{{ edu.graduation_year }}</span>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <button v-if="edu.diploma_file_url" @click="previewFile(edu.diploma_file_url)" class="w-10 h-10 bg-gray-950 text-white rounded-xl inline-flex items-center justify-center hover:bg-indigo-600 transition-all active:scale-95 shadow-lg group-hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Certificates Vault Table -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ring-1 ring-black/5">
                        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white shadow-lg shadow-amber-100 ml-[-4px]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">Evidence Vault (Certificates)</h3>
                                    <p class="text-[9px] text-amber-500 font-bold uppercase tracking-widest mt-0.5">Digital Competency Matrix</p>
                                </div>
                            </div>
                            <button @click="showCertificateModal = true" class="px-5 py-2.5 bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-amber-600 transition-all flex items-center gap-2 shadow-lg shadow-amber-50 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                New Deposit
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed border-collapse">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 bg-gray-50/50">
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[110px]">Type</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[40%]">Certificate Record</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[35%]">Serial Sequence</th>
                                        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest w-[100px]">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!props.profile?.certificates?.length">
                                        <td colspan="4" class="px-8 py-16 text-center text-[11px] font-bold text-gray-300 uppercase tracking-[0.3em] italic">No institutional credentials filed in vault</td>
                                    </tr>
                                    <template v-for="(certs, type) in groupedCerts" :key="type">
                                        <tr v-for="cert in certs" :key="cert.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                                            <td class="px-8 py-6">
                                                <div :class="[
                                                    'inline-flex items-center justify-center w-10 h-10 rounded-xl border shadow-sm transition-transform group-hover:scale-110',
                                                    cert.cert_type === 'BST' ? 'bg-indigo-50 border-indigo-100 text-indigo-600' :
                                                    cert.cert_type === 'COP' ? 'bg-amber-50 border-amber-100 text-amber-600' :
                                                    cert.cert_type === 'COC' ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-gray-50 border-gray-100 text-gray-600'
                                                ]">
                                                    <svg v-if="cert.cert_type === 'BST'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                    <svg v-else-if="cert.cert_type === 'COP'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.703 2.703 0 00-3 0 2.703 2.703 0 00-3 0 2.703 2.703 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 16v2m3-6v6m3-8v8m-7 0h8a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2z"/></svg>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-2.5">
                                                    <p class="text-sm font-black text-gray-900 uppercase tracking-tight truncate">{{ cert.cert_name }}</p>
                                                    <div v-if="cert.verification_status === 'cleared'" class="text-emerald-500 flex-shrink-0" title="Institution Verified">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    </div>
                                                </div>
                                                <p class="text-[9px] text-gray-400 font-black uppercase mt-0.5 tracking-widest italic truncate">{{ cert.issuing_body }}</p>
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col gap-1">
                                                    <span class="text-[11px] font-mono font-black text-gray-700 tracking-wider">
                                                        #{{ cert.cert_number }}
                                                    </span>
                                                    <div class="flex flex-col gap-0.5 mt-1 pt-1 border-t border-gray-50">
                                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest italic">
                                                            Expiry: {{ cert.expiry_date || 'N/A' }}
                                                        </p>
                                                        <div class="flex items-center gap-1.5">
                                                            <span :class="['w-1.5 h-1.5 rounded-full', cert.expired ? 'bg-rose-600' : (cert.expiringSoon ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500')]"></span>
                                                            <p :class="['text-[9px] font-black uppercase tracking-widest italic', cert.expired ? 'text-rose-600' : (cert.expiringSoon ? 'text-amber-500' : 'text-emerald-500')]">
                                                                Remaining: {{ cert.expired ? 0 : cert.daysLeft }} Days
                                                                <span v-if="cert.expired" class="ml-1 opacity-60">(Security Lapse)</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <button v-if="cert.cert_file_url" @click="previewFile(cert.cert_file_url)" class="w-10 h-10 bg-gray-950 text-white rounded-xl inline-flex items-center justify-center hover:bg-amber-500 transition-all active:scale-95 shadow-lg group-hover:scale-110">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Experience Ledger Table -->
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ring-1 ring-black/5">
                        <div class="px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-100 ml-[-4px]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-[0.2em]">Experience Ledger (Sea Service)</h3>
                                    <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-widest mt-0.5">Maritime History Chronology</p>
                                </div>
                            </div>
                            <button @click="showSeaServiceModal = true" class="px-5 py-2.5 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-50 active:scale-95">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                                Log Voyage
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed">
                                <thead>
                                    <tr class="text-left border-b border-gray-100 bg-gray-50/50">
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[35%]">Vessel Assets</th>
                                        <th class="px-8 py-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest w-[140px]">Audit Period</th>
                                        <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest w-[35%]">Operator / Jurisdiction</th>
                                        <th class="px-8 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest w-[100px]">Evidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!props.profile?.sea_services?.length">
                                        <td colspan="4" class="px-8 py-16 text-center text-[11px] font-bold text-gray-300 uppercase tracking-[0.3em] italic">No historical maritime voyages logged</td>
                                    </tr>
                                    <tr v-for="sea in props.profile?.sea_services" :key="sea.id" class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors group">
                                        <td class="px-8 py-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                                </div>
                                                <div class="truncate">
                                                    <p class="text-sm font-black text-gray-900 uppercase tracking-tight truncate">{{ sea.vessel_name }}</p>
                                                    <span class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] italic">{{ sea.vessel_type }} Class</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center">
                                            <span class="px-4 py-1.5 bg-emerald-50 text-emerald-700 text-[10px] font-black rounded-lg border border-emerald-100 shadow-sm inline-block">{{ sea.duration_months }} MONTHS</span>
                                        </td>
                                        <td class="px-8 py-6">
                                            <p class="text-xs font-bold text-gray-700 uppercase tracking-wide truncate">{{ sea.company_name }}</p>
                                            <p class="text-[9px] text-gray-400 font-black uppercase mt-0.5 tracking-widest italic truncate">{{ sea.route }} Regional Command</p>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <button v-if="sea.contract_file_url" @click="previewFile(sea.contract_file_url)" class="w-10 h-10 bg-gray-950 text-white rounded-xl inline-flex items-center justify-center hover:bg-emerald-600 transition-all active:scale-95 shadow-lg group-hover:scale-110">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
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

        <!-- ── MODALS ── -->

        <!-- Education Modal -->
        <Modal :show="showEducationModal" @close="showEducationModal = false">
            <div class="p-7">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                    </div>
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-wider">Add Academic Record</h3>
                </div>
                <form @submit.prevent="submitEdu" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Institution Name</label>
                        <input v-model="eduForm.institution_name" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Program / Major</label>
                            <input v-model="eduForm.degree_program" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Graduation Year</label>
                            <input v-model="eduForm.graduation_year" type="number" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Diploma File (PDF/Image)</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-indigo-300 transition-colors">
                            <input @input="eduForm.diploma_file = $event.target.files[0]" type="file" class="hidden" id="diploma_file" />
                            <label for="diploma_file" class="cursor-pointer text-[11px] font-bold text-indigo-600 hover:text-indigo-700">
                                {{ eduForm.diploma_file ? eduForm.diploma_file.name : 'Click to upload file' }}
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showEducationModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="eduForm.processing" class="px-7 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-colors disabled:opacity-50">
                            Save Record
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Certificate Modal -->
        <Modal :show="showCertificateModal" @close="showCertificateModal = false">
            <div class="p-7">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-wider">Add Certificate</h3>
                </div>
                <form @submit.prevent="submitCert" class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Type</label>
                        <select v-model="certForm.cert_type" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500">
                            <option value="BST">BST – Basic Safety Training</option>
                            <option value="COP">COP – Certificate of Proficiency</option>
                            <option value="COC">COC – Certificate of Competency</option>
                            <option value="OTHER">Other Credentials</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate Name</label>
                        <input v-model="certForm.cert_name" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Certificate No.</label>
                            <input v-model="certForm.cert_number" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Issuing Authority</label>
                            <input v-model="certForm.issuing_body" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Issued Date</label>
                            <input v-model="certForm.issued_date" type="date" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Expiry Date</label>
                            <input v-model="certForm.expiry_date" type="date" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Digital Copy</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-amber-300 transition-colors">
                            <input @input="certForm.cert_file = $event.target.files[0]" type="file" class="hidden" id="cert_file" />
                            <label for="cert_file" class="cursor-pointer text-[11px] font-bold text-amber-600 hover:text-amber-700">
                                {{ certForm.cert_file ? certForm.cert_file.name : 'Click to upload file' }}
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showCertificateModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="certForm.processing" class="px-7 py-2.5 bg-amber-500 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-amber-100 hover:bg-amber-600 transition-colors disabled:opacity-50">
                            Save Certificate
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Sea Service Modal -->
        <Modal :show="showSeaServiceModal" @close="showSeaServiceModal = false">
            <div class="p-7">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center">
                        <span class="text-base">🚢</span>
                    </div>
                    <h3 class="text-base font-black text-gray-900 uppercase tracking-wider">Log Sea Service</h3>
                </div>
                <form @submit.prevent="submitSea" class="space-y-5 overflow-y-auto max-h-[65vh] pr-1">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Name</label>
                            <input v-model="seaForm.vessel_name" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Vessel Type</label>
                            <input v-model="seaForm.vessel_type" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Company Name</label>
                        <input v-model="seaForm.company_name" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Rank at Time</label>
                            <input v-model="seaForm.rank_at_time" type="text" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Route</label>
                            <input v-model="seaForm.route" type="text" placeholder="Domestic / Intl" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 placeholder:font-normal placeholder:text-gray-300" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sign On</label>
                            <input v-model="seaForm.start_date" type="date" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" required />
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sign Off</label>
                            <input v-model="seaForm.end_date" type="date" class="w-full border border-gray-200 rounded-xl text-sm font-bold px-4 py-3 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Contract / Sea Service Proof</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center hover:border-emerald-300 transition-colors">
                            <input @input="seaForm.contract_file = $event.target.files[0]" type="file" class="hidden" id="sea_file" />
                            <label for="sea_file" class="cursor-pointer text-[11px] font-bold text-emerald-600 hover:text-emerald-700">
                                {{ seaForm.contract_file ? seaForm.contract_file.name : 'Click to upload file' }}
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showSeaServiceModal = false" class="px-5 py-2.5 text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="seaForm.processing" class="px-7 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-colors disabled:opacity-50">
                            Log Service
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- CV Preview Modal -->
        <Modal :show="showCvPreview" @close="showCvPreview = false" maxWidth="3xl">
            <div class="bg-white rounded-2xl overflow-hidden flex flex-col max-h-[92vh]">
                <div class="px-8 py-5 border-b border-gray-200 flex items-center justify-between bg-gray-900 text-white">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-wider">Digital Maritime CV</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ props.profile?.alumni_code }} · Rayterton Unified Ledger</p>
                    </div>
                    <button @click="showCvPreview = false" class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white hover:text-gray-900 flex items-center justify-center transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-10 overflow-y-auto flex-grow bg-white space-y-10">
                    <!-- CV Header -->
                    <div class="flex items-start gap-8 pb-8 border-b border-gray-100">
                        <div class="w-32 h-44 rounded-xl overflow-hidden shadow-xl border-4 border-gray-50 flex-shrink-0">
                            <img v-if="props.profile?.avatar_url" :src="`/storage/${props.profile.avatar_url}`" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-300 text-3xl font-black">?</div>
                        </div>
                        <div class="flex-grow">
                            <h4 class="text-4xl font-black text-gray-900 leading-tight tracking-tight">{{ props.profile?.full_name }}</h4>
                            <p class="text-lg font-black text-indigo-600 mt-1 uppercase tracking-wide">{{ props.profile?.rank }}</p>
                            <div class="grid grid-cols-2 gap-x-8 gap-y-3 mt-6">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 flex-shrink-0">Region</span>
                                    <span class="text-sm font-bold text-gray-700">{{ props.profile?.region || '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 flex-shrink-0">Email</span>
                                    <span class="text-sm font-bold text-gray-700 truncate">{{ $page.props.auth.user.email }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 flex-shrink-0">Phone</span>
                                    <span class="text-sm font-bold text-gray-700">{{ props.profile?.phone || '—' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest w-16 flex-shrink-0">Vessel</span>
                                    <span class="text-sm font-bold text-gray-700">{{ props.profile?.preferred_vessel_type || '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-5 gap-10">
                        <!-- Left: Sea Service + Education -->
                        <div class="col-span-3 space-y-10">
                            <div>
                                <h5 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2 pb-2 border-b border-emerald-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    Sea Service History
                                </h5>
                                <div class="space-y-4">
                                    <div v-if="!props.profile?.sea_services?.length" class="text-[11px] text-gray-300 font-bold">No entries recorded.</div>
                                    <div v-for="sea in props.profile?.sea_services" :key="sea.id" class="relative pl-5 border-l-2 border-gray-100">
                                        <div class="absolute -left-1.5 top-1 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-white"></div>
                                        <p class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ sea.vessel_name }}</p>
                                        <p class="text-[11px] font-semibold text-gray-500 mt-0.5">{{ sea.rank_at_time }} · {{ sea.duration_months }} months</p>
                                        <p class="text-[10px] text-gray-400">{{ sea.company_name }}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h5 class="text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-4 flex items-center gap-2 pb-2 border-b border-indigo-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                                    Academic Background
                                </h5>
                                <div class="space-y-4">
                                    <div v-if="!props.profile?.educations?.length" class="text-[11px] text-gray-300 font-bold">No entries recorded.</div>
                                    <div v-for="edu in props.profile?.educations" :key="edu.id" class="relative pl-5 border-l-2 border-gray-100">
                                        <div class="absolute -left-1.5 top-1 w-2.5 h-2.5 rounded-full bg-indigo-400 border-2 border-white"></div>
                                        <p class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ edu.institution_name }}</p>
                                        <p class="text-[11px] font-semibold text-gray-500 mt-0.5">{{ edu.degree_program }}</p>
                                        <p class="text-[10px] text-gray-400">Class of {{ edu.graduation_year }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Certificates (Grouped) -->
                        <div class="col-span-2 space-y-8">
                            <h5 class="text-[10px] font-black text-amber-600 uppercase tracking-[0.3em] mb-4 flex items-center gap-2 pb-2 border-b border-amber-100 italic">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Evidence Vault
                            </h5>
                            <div v-if="!props.profile?.certificates?.length" class="text-[11px] text-gray-300 font-bold italic">No institutional credentials filed.</div>
                            <div v-else class="space-y-6">
                                <template v-for="(certs, type) in groupedCerts" :key="type">
                                    <div class="space-y-3">
                                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] ml-1">{{ type }} Synchronization</p>
                                        <div class="space-y-2">
                                            <div v-for="cert in certs" :key="cert.id" class="p-4 bg-gray-50 border border-gray-100 rounded-xl group hover:border-amber-300 transition-all">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-3">
                                                        <!-- SVG Icons for CV -->
                                                        <div :class="[
                                                            'w-8 h-8 rounded-lg border shadow-sm flex items-center justify-center flex-shrink-0',
                                                            cert.cert_type === 'BST' ? 'bg-indigo-50 border-indigo-100 text-indigo-500' :
                                                            cert.cert_type === 'COP' ? 'bg-amber-50 border-amber-100 text-amber-500' : 'bg-emerald-50 border-emerald-100 text-emerald-500'
                                                        ]">
                                                            <svg v-if="cert.cert_type === 'BST'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                            <svg v-else-if="cert.cert_type === 'COP'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.703 2.703 0 00-3 0 2.703 2.703 0 00-3 0 2.703 2.703 0 00-3 0 2.701 2.701 0 00-1.5-.454M9 16v2m3-6v6m3-8v8m-7 0h8a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2z"/></svg>
                                                        </div>
                                                        <div>
                                                            <p class="text-[11px] font-black text-gray-900 uppercase tracking-tight">{{ cert.cert_name }}</p>
                                                            <p class="text-[9px] font-mono text-gray-400 font-bold lowercase tracking-tight">{{ cert.cert_number }}</p>
                                                        </div>
                                                    </div>
                                                    <div v-if="cert.verification_status === 'cleared'" class="text-emerald-500">
                                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rayterton Maritime Ledger</p>
                    <button @click="showCvPreview = false" class="px-7 py-3 bg-gray-900 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-600 transition-colors shadow-lg">
                        Close
                    </button>
                </div>
            </div>
        </Modal>

        <!-- File Preview Modal (Clean Inspection) -->
        <Modal :show="showPreviewModal" @close="showPreviewModal = false" maxWidth="3xl">
            <div class="bg-[#0A0B10] rounded-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-white/10 shadow-2xl">
                <!-- Header: Intelligence Bar -->
                <div class="px-7 py-4 border-b border-white/5 flex items-center justify-between bg-gray-900 text-white relative">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-white/90">Document Preview</h3>
                    </div>
                    <button @click="showPreviewModal = false" class="w-8 h-8 rounded-lg bg-white/5 hover:bg-white/10 text-white/40 hover:text-white flex items-center justify-center transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Content: Preview -->
                <div class="flex-grow p-6 bg-gray-950 flex items-center justify-center overflow-hidden relative">
                    <img v-if="isImage(currentPreview)" 
                         :src="currentPreview" 
                         class="max-w-full max-h-[60vh] object-contain rounded-lg shadow-2xl border border-white/10" />
                    
                    <iframe v-else 
                            :src="currentPreview" 
                            class="w-full h-[60vh] rounded-lg border border-white/10 bg-white"></iframe>
                </div>

                <!-- Footer: Controls -->
                <div class="px-7 py-4 bg-[#0E0F15] border-t border-white/5 flex items-center justify-between">
                    <p class="text-[9px] font-black text-white/20 uppercase tracking-widest">Digital Evidence Inspection</p>
                    <a :href="currentPreview" download class="px-5 py-2.5 bg-white text-gray-900 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-indigo-500 hover:text-white transition-all shadow-xl active:scale-95 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 16l5 5m0 0l5-5m-5 5V3"/></svg>
                        Download
                    </a>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>