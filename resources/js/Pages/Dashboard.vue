<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
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
});

const submit = () => {
    form.put(route('alumni.master-profile.update'), {
        preserveScroll: true,
    });
};

const submitVerification = () => {
    router.post(route('alumni.verify.submit'), {}, { preserveScroll: true });
};

const completeness = computed(() => props.profile?.profile_completeness || 0);

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
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Alumni Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8 space-y-6">
                
                <!-- Welcome & Profile Completeness -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                            <div>
                                <h3 class="text-xl font-semibold mb-1">Welcome Back!</h3>
                                <p class="text-gray-600 text-sm">Please complete your master profile to unlock the full potential of the platform.</p>
                                <div v-if="$page.props.flash?.success" class="mt-3 px-3 py-1.5 bg-green-50 text-green-700 rounded text-sm font-medium border border-green-200 inline-block">
                                    {{ $page.props.flash.success }}
                                </div>
                            </div>
                            
                            <div class="w-full md:w-64 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">Profile Completeness</span>
                                    <span class="text-sm font-bold text-indigo-600">{{ completeness }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500 ease-out" :style="{ width: completeness + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Master Profile Setup -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Step 1. Lengkapi Master Profile
                            </h3>
                            <p class="text-gray-500 mt-1 text-sm">Masukan detail rank, domisili, dan preferensi kerja Anda.</p>
                        </div>
                        <div v-if="props.profile?.alumni_code" class="px-3 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded font-mono text-sm">
                            ID: {{ props.profile.alumni_code }}
                        </div>
                    </div>

                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Full Name -->
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap (Sesuai Sertifikat)</label>
                                <input id="full_name" v-model="form.full_name" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cth: Budi Santoso" required />
                                <div v-if="form.errors.full_name" class="text-red-600 text-sm mt-1">{{ form.errors.full_name }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Rank -->
                                <div>
                                    <label for="rank" class="block text-sm font-medium text-gray-700 mb-1">Rank / Jabatan Terakhir</label>
                                    <input id="rank" v-model="form.rank" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cth: Chief Officer" required />
                                    <div v-if="form.errors.rank" class="text-red-600 text-sm mt-1">{{ form.errors.rank }}</div>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon (WhatsApp)</label>
                                    <input id="phone" v-model="form.phone" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="0812..." required />
                                    <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">{{ form.errors.phone }}</div>
                                </div>
                                
                                <!-- Region -->
                                <div>
                                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Provinsi / Wilayah</label>
                                    <input id="region" v-model="form.region" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cth: Jawa Timur" required />
                                    <div v-if="form.errors.region" class="text-red-600 text-sm mt-1">{{ form.errors.region }}</div>
                                </div>

                                <!-- Preferred Vessel -->
                                <div>
                                    <label for="preferred_vessel_type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Kapal Favorit</label>
                                    <input id="preferred_vessel_type" v-model="form.preferred_vessel_type" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cth: Tanker" required />
                                    <div v-if="form.errors.preferred_vessel_type" class="text-red-600 text-sm mt-1">{{ form.errors.preferred_vessel_type }}</div>
                                </div>
                                
                                <!-- Preferred Route -->
                                <div class="md:col-span-2">
                                    <label for="preferred_route" class="block text-sm font-medium text-gray-700 mb-1">Rute Pelayaran Favorit</label>
                                    <input id="preferred_route" v-model="form.preferred_route" type="text" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Cth: Domestik" required />
                                    <div v-if="form.errors.preferred_route" class="text-red-600 text-sm mt-1">{{ form.errors.preferred_route }}</div>
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Simpan Master Profile</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 3: Tambah Pendidikan -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg" :class="{'opacity-50 pointer-events-none': completeness < 20}">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Step 2. Tambah Pendidikan
                            </h3>
                            <p class="text-gray-500 mt-1 text-sm">Masukan data institusi, program studi, tahun lulus, dan unggah (upload) diploma.</p>
                        </div>
                    </div>
                    <div class="p-6 bg-gray-50 text-center border-b border-gray-200" v-if="completeness < 20">
                        <p class="text-sm text-gray-500 italic">Silakan simpan Master Profile terlebih dahulu untuk membuka kunci fitur ini.</p>
                    </div>
                    <div class="p-6" v-else>
                        <!-- Education List -->
                        <div v-if="profile?.educations?.length > 0" class="mb-4">
                            <ul class="space-y-3">
                                <li v-for="edu in profile.educations" :key="edu.id" class="p-4 border rounded-md flex justify-between items-center bg-gray-50">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ edu.institution_name }}</p>
                                        <p class="text-sm text-gray-500">{{ edu.degree_program }} ({{ edu.graduation_year }})</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded bg-indigo-100 text-indigo-800" v-if="edu.diploma_file_url">File Attached</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mb-4 text-sm text-gray-500 pt-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                            <span v-if="!profile?.educations?.length">Belum ada data pendidikan yang ditambahkan.</span>
                            <span v-else></span>
                            <button @click="showEducationModal = true" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                + Tambah Pendidikan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Input Sertifikat -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg" :class="{'opacity-50 pointer-events-none': completeness < 20}">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Step 3. Input Sertifikat Kompetensi
                            </h3>
                            <p class="text-gray-500 mt-1 text-sm">Masukan nomor sertifikat, tanggal kadaluarsa (expiry), dan unggah file sertifikat.</p>
                        </div>
                    </div>
                    <div class="p-6" v-if="completeness >= 20">
                        <div v-if="profile?.certificates?.length > 0" class="mb-4">
                            <ul class="space-y-3">
                                <li v-for="cert in profile.certificates" :key="cert.id" class="p-4 border rounded-md flex justify-between items-center bg-gray-50">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ cert.cert_name }}</p>
                                        <p class="text-sm text-gray-500">No: {{ cert.cert_number }} | Exp: {{ cert.expiry_date || 'N/A' }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded bg-indigo-100 text-indigo-800" v-if="cert.cert_file_url">File Attached</span>
                                </li>
                            </ul>
                        </div>
                        <div class="text-sm text-gray-500 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mt-4">
                            <span v-if="!profile?.certificates?.length">Belum ada sertifikat.</span>
                            <span v-else></span>
                            <button @click="showCertificateModal = true" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                + Tambah Sertifikat
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Input Sea Service -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg" :class="{'opacity-50 pointer-events-none': completeness < 20}">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">
                                Step 4. Pengalaman Berlayar (Sea Service)
                            </h3>
                            <p class="text-gray-500 mt-1 text-sm">Masukan rincian nama kapal, perusahaan, tanggal berlayar, dan unggah kontrak.</p>
                        </div>
                    </div>
                    <div class="p-6" v-if="completeness >= 20">
                        <div v-if="profile?.sea_services?.length > 0" class="mb-4">
                            <ul class="space-y-3">
                                <li v-for="sea in profile.sea_services" :key="sea.id" class="p-4 border rounded-md flex justify-between items-center bg-gray-50">
                                    <div>
                                        <p class="font-bold text-gray-800">{{ sea.vessel_name }} ({{ sea.company_name }})</p>
                                        <p class="text-sm text-gray-500">{{ sea.rank_at_time }} | {{ sea.duration_months }} bulan | Route: {{ sea.route }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 rounded bg-indigo-100 text-indigo-800" v-if="sea.contract_file_url">File Attached</span>
                                </li>
                            </ul>
                        </div>
                        <div class="text-sm text-gray-500 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mt-4">
                            <span v-if="!profile?.sea_services?.length">Belum ada pengalaman berlayar.</span>
                            <span v-else></span>
                            <button @click="showSeaServiceModal = true" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 rounded-md text-sm font-semibold transition-colors">
                                + Tambah Sea Service
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Submit Verification -->
                <div class="overflow-hidden bg-blue-50 border border-blue-100 shadow-sm sm:rounded-lg" v-if="completeness >= 20">
                    <div class="p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-blue-900">
                                Final Step: Submit untuk Verifikasi
                            </h3>
                            <p class="text-blue-700 mt-1 text-sm">Jika seluruh data (Master Profil, Pendidikan, Sertifikat, dan Sea Service) sudah lengkap, ajukan profil Anda untuk dikonfirmasi oleh verifikator.</p>
                            
                            <p v-if="profile?.verification_status === 'pending'" class="text-amber-600 mt-2 font-bold text-sm">Status: ⏳ Menunggu Verifikasi Admin.</p>
                            <p v-if="profile?.verification_status === 'verified'" class="text-emerald-600 mt-2 font-bold text-sm">Status: ✅ Profil Terverifikasi.</p>
                        </div>
                        <button 
                            @click="submitVerification"
                            :disabled="completeness < 100 || profile?.verification_status !== 'unverified'"
                            class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow transition-colors whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
                            v-if="profile?.verification_status === 'unverified'"
                        >
                            {{ completeness < 100 ? 'Lengkapi Data (100%)' : 'Kirim Verifikasi Profil' }}
                        </button>
                        <button 
                            disabled
                            class="px-6 py-3 bg-gray-400 text-white font-bold rounded-lg shadow whitespace-nowrap cursor-not-allowed"
                            v-else
                        >
                            Verifikasi Diajukan
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modals -->
        <Modal :show="showEducationModal" @close="showEducationModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambahkan Data Pendidikan</h2>
                <form @submit.prevent="submitEdu" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Institusi / Universitas</label>
                        <input v-model="eduForm.institution_name" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Program Studi / Jurusan</label>
                        <input v-model="eduForm.degree_program" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Lulus</label>
                        <input v-model="eduForm.graduation_year" type="number" min="1950" max="2099" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Ijazah (PDF/JPG)</label>
                        <input @input="eduForm.diploma_file = $event.target.files[0]" type="file" class="w-full mt-1 text-sm" accept=".pdf,.png,.jpg,.jpeg" />
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showEducationModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Pendidikan</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showCertificateModal" @close="showCertificateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambahkan Data Sertifikat Kompetensi</h2>
                <form @submit.prevent="submitCert" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Sertifikat</label>
                        <input v-model="certForm.cert_name" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor Sertifikat</label>
                        <input v-model="certForm.cert_number" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Lembaga Penerbit</label>
                        <input v-model="certForm.issuing_body" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Terbit</label>
                            <input v-model="certForm.issued_date" type="date" class="w-full mt-1 border-gray-300 rounded-md" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Ekspirasi (Optional)</label>
                            <input v-model="certForm.expiry_date" type="date" class="w-full mt-1 border-gray-300 rounded-md" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Sertifikat (PDF/JPG)</label>
                        <input @input="certForm.cert_file = $event.target.files[0]" type="file" class="w-full mt-1 text-sm" accept=".pdf,.png,.jpg,.jpeg" />
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showCertificateModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Sertifikat</button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="showSeaServiceModal" @close="showSeaServiceModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Tambahkan Data Sea Service</h2>
                <form @submit.prevent="submitSea" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Kapal</label>
                        <input v-model="seaForm.vessel_name" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tipe Kapal</label>
                            <input v-model="seaForm.vessel_type" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Rank/Posisi</label>
                            <input v-model="seaForm.rank_at_time" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Perusahaan / Agency</label>
                        <input v-model="seaForm.company_name" type="text" class="w-full mt-1 border-gray-300 rounded-md" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Sign On</label>
                            <input v-model="seaForm.start_date" type="date" class="w-full mt-1 border-gray-300 rounded-md" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Sign Off</label>
                            <input v-model="seaForm.end_date" type="date" class="w-full mt-1 border-gray-300 rounded-md" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">File Kontrak PKL / Buku Pelaut</label>
                        <input @input="seaForm.contract_file = $event.target.files[0]" type="file" class="w-full mt-1 text-sm" accept=".pdf,.png,.jpg,.jpeg" />
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showSeaServiceModal = false" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Simpan Sea Service</button>
                    </div>
                </form>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
