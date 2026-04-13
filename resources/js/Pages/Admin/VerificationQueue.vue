<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SlideOver from '@/Components/SlideOver.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    queue: Array
});

const selectedProfile = ref(null);
const showSlideOver = ref(false);

const reviewProfile = (profile) => {
    selectedProfile.value = profile;
    showSlideOver.value = true;
    
    // Mark as reviewing if it was pending
    if (profile.verification_status === 'pending') {
        router.post(route('admin.verifications.in_review', profile.id), {}, {
            preserveScroll: true,
            preserveState: true,
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

</script>

<template>
    <Head title="Verification Queue" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 flex items-center justify-between border-b border-gray-200">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Verification Queue
                            </h2>
                            <p class="text-gray-500 mt-1">Daftar alumni yang menunggu persetujuan kelengkapan berkas.</p>
                        </div>
                    </div>
                    
                    <div v-if="!queue?.length" class="p-12 text-center text-gray-500 font-semibold">
                        🎉 Hebat! Tidak ada verifikasi yang tertunda saat ini.
                    </div>

                    <table v-else class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="p-4 font-semibold">Alumni</th>
                                <th class="p-4 font-semibold">Pangkat & Region</th>
                                <th class="p-4 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="profile in queue" :key="profile.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-800 font-medium my-auto flex flex-col justify-center">
                                    <span class="text-lg font-bold">{{ profile.full_name }}</span>
                                    <span class="text-sm text-gray-500">{{ profile.alumni_code }}</span>
                                </td>
                                <td class="p-4">
                                    <div>{{ profile.rank }}</div>
                                    <div class="text-sm text-gray-500">{{ profile.region }}</div>
                                </td>
                                <td class="p-4 text-right">
                                    <button @click="reviewProfile(profile)" class="px-4 py-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 font-bold rounded-lg shadow-sm transition">
                                        Review
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Slide Over Panel -->
        <SlideOver :show="showSlideOver" @close="showSlideOver = false" title="Review Alumni Profile">
            <div v-if="selectedProfile" class="flex flex-col h-full space-y-6 pb-6">
                
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Master Data</h3>
                    <div class="mt-3 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <div class="mb-2">
                            <span class="block text-xs font-medium text-gray-500">Nama Lengkap</span>
                            <span class="block text-sm font-bold text-gray-800">{{ selectedProfile.full_name }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="block text-xs font-medium text-gray-500">Pangkat Dasar</span>
                            <span class="block text-sm font-bold text-gray-800">{{ selectedProfile.rank }}</span>
                        </div>
                        <div class="mb-2">
                            <span class="block text-xs font-medium text-gray-500">Regional Domisili</span>
                            <span class="block text-sm font-bold text-gray-800">{{ selectedProfile.region }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <span class="block text-xs font-medium text-gray-500">Pref Kapal</span>
                                <span class="block text-sm font-semibold text-indigo-700">{{ selectedProfile.preferred_vessel_type }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-gray-500">Pref Rute</span>
                                <span class="block text-sm font-semibold text-indigo-700">{{ selectedProfile.preferred_route }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 mt-6 pt-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Dokumen Terlampir</h3>
                    
                    <div class="space-y-6">
                        <!-- Education -->
                        <div v-if="selectedProfile.educations?.length">
                            <h4 class="text-xs font-bold text-indigo-600 uppercase mb-3 flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-2"></span> Pendidikan
                            </h4>
                            <div class="flex flex-col gap-3">
                                <div v-for="edu in selectedProfile.educations" :key="edu.id" class="flex items-center justify-between p-3 bg-white border border-gray-200 shadow-sm rounded-xl hover:border-indigo-300 transition-colors">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ edu.institution_name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ edu.degree_program }} &bull; {{ edu.graduation_year }}</p>
                                        </div>
                                    </div>
                                    <a v-if="edu.diploma_file_url" :href="`/storage/${edu.diploma_file_url}`" target="_blank" class="flex-shrink-0 ml-3 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Certificates -->
                        <div v-if="selectedProfile.certificates?.length">
                            <h4 class="text-xs font-bold text-amber-500 uppercase mb-3 flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-2"></span> Sertifikat
                            </h4>
                            <div class="flex flex-col gap-3">
                                <div v-for="cert in selectedProfile.certificates" :key="cert.id" class="flex items-center justify-between p-3 bg-white border border-gray-200 shadow-sm rounded-xl hover:border-amber-300 transition-colors">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="flex-shrink-0 w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ cert.cert_name }}</p>
                                            <p class="text-xs text-gray-500 truncate">No. {{ cert.cert_number }} &bull; Exp: {{ cert.expiry_date || 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <a v-if="cert.cert_file_url" :href="`/storage/${cert.cert_file_url}`" target="_blank" class="flex-shrink-0 ml-3 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Sea Service (Pengalaman) -->
                        <div v-if="selectedProfile.sea_services?.length">
                            <h4 class="text-xs font-bold text-emerald-600 uppercase mb-3 flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span> Sea Service
                            </h4>
                            <div class="flex flex-col gap-3">
                                <div v-for="sea in selectedProfile.sea_services" :key="sea.id" class="flex items-center justify-between p-3 bg-white border border-gray-200 shadow-sm rounded-xl hover:border-emerald-300 transition-colors">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="flex-shrink-0 w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-sm font-bold text-gray-800 truncate">{{ sea.vessel_name }} <span class="text-xs font-normal text-gray-500">({{ sea.vessel_type }})</span></p>
                                            <p class="text-xs text-gray-500 truncate">{{ sea.company_name }} &bull; {{ sea.rank_at_time }} ({{ sea.duration_months }} bln)</p>
                                        </div>
                                    </div>
                                    <a v-if="sea.contract_file_url" :href="`/storage/${sea.contract_file_url}`" target="_blank" class="flex-shrink-0 ml-3 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Buka
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 mt-6 pt-6">
                    <p class="text-xs text-gray-500 mb-6">Pastikan seluruh data dan dokumen otentik sebelum Anda memberikan Approve. Data terverifikasi akan terlihat oleh pihak Perusahaan.</p>
                    
                    <div class="flex flex-col gap-3">
                        <button @click="approve" class="w-full flex items-center justify-center px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Approve Profile
                        </button>
                        <button @click="showSlideOver = false" class="w-full px-4 py-3 bg-gray-100 hover:bg-gray-300 text-gray-700 font-bold rounded-lg transition">
                            Tutup & Review Nanti
                        </button>
                    </div>
                </div>

            </div>
        </SlideOver>

    </AuthenticatedLayout>
</template>
