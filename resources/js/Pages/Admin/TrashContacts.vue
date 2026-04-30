<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    messages: Array
});

const showRestoreConfirmModal = ref(false);
const showPurgeConfirmModal = ref(false);
const restoreId = ref(null);
const purgeId = ref(null);

const restoreEntry = (id) => {
    restoreId.value = id;
    showRestoreConfirmModal.value = true;
};

const purgeEntry = (id) => {
    purgeId.value = id;
    showPurgeConfirmModal.value = true;
};

const confirmRestore = () => {
    router.post(route('admin.contacts.restore', restoreId.value), {}, {
        onSuccess: () => {
            showRestoreConfirmModal.value = false;
            restoreId.value = null;
        }
    });
};

const confirmPurge = () => {
    router.delete(route('admin.contacts.purge', purgeId.value), {
        onSuccess: () => {
            showPurgeConfirmModal.value = false;
            purgeId.value = null;
        }
    });
};
</script>

<template>
    <Head title="Trashed Contact Messages" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F6FA] py-10">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-10 space-y-8">
                
                <!-- ── Administrative Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-8 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold text-rose-500 bg-rose-50 px-3 py-1 rounded-lg uppercase tracking-widest italic">Communications</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Trash</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Trashed Messages</h1>
                        <p class="text-sm text-gray-500 mt-1 max-w-lg italic font-medium">Deleted inquiries pending permanent purge or restoration.</p>
                    </div>
                </div>

                <!-- ── Messages Table ── -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ring-1 ring-black/5">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed border-collapse">
                            <colgroup>
                                <col class="w-[30%]">
                                <col class="w-[30%]">
                                <col class="w-[20%]">
                                <col class="w-[20%]">
                            </colgroup>
                            <thead>
                                <tr class="text-left border-b border-gray-100 bg-gray-50/50 uppercase tracking-widest">
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400">Sender Details</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400">Subject</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-center">Deleted At</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="msg in messages" :key="msg.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gray-950 text-white flex items-center justify-center font-black text-[10px] shadow-lg opacity-60">
                                                {{ msg.first_name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="opacity-60">
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ msg.first_name }} {{ msg.last_name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ msg.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 opacity-60">
                                        <p class="text-sm text-gray-700 truncate font-medium">{{ msg.subject || 'No Subject' }}</p>
                                        <p class="text-xs text-gray-400 mt-1 truncate">{{ new Date(msg.created_at).toLocaleString() }}</p>
                                    </td>
                                    <td class="px-8 py-6 text-center text-sm font-medium text-gray-600">
                                        {{ new Date(msg.deleted_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="restoreEntry(msg.id)" class="w-9 h-9 bg-white border border-gray-200 text-emerald-500 rounded-xl inline-flex items-center justify-center hover:bg-emerald-500 hover:text-white hover:border-emerald-600 transition-all active:scale-95 shadow-sm group-hover:scale-105" title="Restore">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                            </button>
                                            <button @click="purgeEntry(msg.id)" class="w-9 h-9 bg-white border border-gray-200 text-rose-500 rounded-xl inline-flex items-center justify-center hover:bg-rose-600 hover:text-white hover:border-rose-600 transition-all active:scale-95 shadow-sm group-hover:scale-105" title="Purge Permanently">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="messages.length === 0">
                                    <td colspan="4" class="px-8 py-10 text-center text-gray-400 text-sm font-medium italic">No trashed messages found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirm Modal -->
        <Modal :show="showRestoreConfirmModal" @close="showRestoreConfirmModal = false" maxWidth="md">
            <div class="p-8 text-center bg-gray-950 text-white rounded-2xl border border-white/10 shadow-2xl">
                <div class="w-16 h-16 bg-emerald-500/20 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-emerald-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                </div>
                <h3 class="text-lg font-black uppercase tracking-widest mb-2">Restore Message?</h3>
                <p class="text-xs text-gray-400 font-medium leading-relaxed mb-8">This message will be moved back to the main inbox.</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmRestore" class="w-full py-4 bg-emerald-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-700 transition-all active:scale-95 shadow-xl shadow-emerald-900/20">
                        Confirm Restore
                    </button>
                    <button @click="showRestoreConfirmModal = false" class="w-full py-4 bg-white/5 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/10 hover:text-white transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Purge Confirm Modal -->
        <Modal :show="showPurgeConfirmModal" @close="showPurgeConfirmModal = false" maxWidth="md">
            <div class="p-8 text-center bg-gray-950 text-white rounded-2xl border border-white/10 shadow-2xl">
                <div class="w-16 h-16 bg-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-rose-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-black uppercase tracking-widest mb-2">Purge Permanently?</h3>
                <p class="text-xs text-gray-400 font-medium leading-relaxed mb-8">This action is irreversible. The message will be destroyed forever.</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmPurge" class="w-full py-4 bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 transition-all active:scale-95 shadow-xl shadow-rose-900/20">
                        Confirm Purge
                    </button>
                    <button @click="showPurgeConfirmModal = false" class="w-full py-4 bg-white/5 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/10 hover:text-white transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
