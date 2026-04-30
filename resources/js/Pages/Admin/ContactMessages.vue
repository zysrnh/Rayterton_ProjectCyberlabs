<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';

const props = defineProps({
    messages: Array
});

const showViewModal = ref(false);
const showDeleteConfirmModal = ref(false);
const selectedMessage = ref(null);
const deleteId = ref(null);

const openViewModal = (message) => {
    selectedMessage.value = message;
    showViewModal.value = true;
    
    // Mark as read if it's unread
    if (!message.is_read) {
        router.post(route('admin.contacts.read', message.id), {}, { preserveScroll: true });
    }
};

const deleteEntry = (id) => {
    deleteId.value = id;
    showDeleteConfirmModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('admin.contacts.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteConfirmModal.value = false;
            deleteId.value = null;
        }
    });
};
</script>

<template>
    <Head title="Contact Messages" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F6FA] py-10">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-10 space-y-8">
                
                <!-- ── Administrative Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-8 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-3 py-1 rounded-lg uppercase tracking-widest italic">Communications</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Inbox</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Contact Messages</h1>
                        <p class="text-sm text-gray-500 mt-1 max-w-lg italic font-medium">Managing incoming inquiries and messages from public contact form.</p>
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
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-center">Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="msg in messages" :key="msg.id" class="hover:bg-gray-50/50 transition-colors group" :class="{'bg-indigo-50/30': !msg.is_read}">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gray-950 text-white flex items-center justify-center font-black text-[10px] shadow-lg">
                                                {{ msg.first_name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ msg.first_name }} {{ msg.last_name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ msg.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <p class="text-sm text-gray-700 truncate font-medium">{{ msg.subject || 'No Subject' }}</p>
                                        <p class="text-xs text-gray-400 mt-1 truncate">{{ new Date(msg.created_at).toLocaleString() }}</p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span 
                                            :class="[
                                                'inline-flex items-center px-4 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-widest shadow-sm transition-all',
                                                msg.is_read ? 'bg-emerald-50 border-emerald-100 text-emerald-600' : 'bg-amber-50 border-amber-100 text-amber-600'
                                            ]"
                                        >
                                            <span v-if="!msg.is_read" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-2"></span>
                                            {{ msg.is_read ? 'READ' : 'UNREAD' }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openViewModal(msg)" class="w-9 h-9 bg-white border border-gray-200 text-gray-400 rounded-xl inline-flex items-center justify-center hover:bg-indigo-500 hover:text-white hover:border-indigo-600 transition-all active:scale-95 shadow-sm group-hover:scale-105">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            </button>
                                            <button @click="deleteEntry(msg.id)" class="w-9 h-9 bg-white border border-gray-200 text-gray-400 rounded-xl inline-flex items-center justify-center hover:bg-rose-500 hover:text-white hover:border-rose-600 transition-all active:scale-95 shadow-sm group-hover:scale-105">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="messages.length === 0">
                                    <td colspan="4" class="px-8 py-10 text-center text-gray-400 text-sm font-medium italic">No contact messages found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Message Modal -->
        <Modal :show="showViewModal" @close="showViewModal = false" maxWidth="lg">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden" v-if="selectedMessage">
                <div class="px-8 py-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-gray-900">Message Details</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ new Date(selectedMessage.created_at).toLocaleString() }}</p>
                    </div>
                    <button @click="showViewModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">From</p>
                        <p class="text-sm font-bold text-gray-900">{{ selectedMessage.first_name }} {{ selectedMessage.last_name }} &lt;{{ selectedMessage.email }}&gt;</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Subject</p>
                        <p class="text-sm font-medium text-gray-800">{{ selectedMessage.subject || 'No Subject' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Message</p>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                            {{ selectedMessage.message }}
                        </div>
                    </div>
                </div>
                <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button @click="showViewModal = false" class="px-6 py-2.5 bg-white border border-gray-200 text-gray-600 text-xs font-bold rounded-lg hover:bg-gray-50 transition-colors">Close</button>
                </div>
            </div>
        </Modal>

        <!-- Liquidation Confirm Modal -->
        <Modal :show="showDeleteConfirmModal" @close="showDeleteConfirmModal = false" maxWidth="md">
            <div class="p-8 text-center bg-gray-950 text-white rounded-2xl border border-white/10 shadow-2xl">
                <div class="w-16 h-16 bg-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-rose-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-black uppercase tracking-widest mb-2">Move to Trash?</h3>
                <p class="text-xs text-gray-400 font-medium leading-relaxed mb-8">This message will be moved to the trash center.</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmDelete" class="w-full py-4 bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 transition-all active:scale-95 shadow-xl shadow-rose-900/20">
                        Confirm Deletion
                    </button>
                    <button @click="showDeleteConfirmModal = false" class="w-full py-4 bg-white/5 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/10 hover:text-white transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
