<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import { ref } from 'vue';

const props = defineProps({
    admins: Array
});

const showAddModal = ref(false);
const showDeleteConfirmModal = ref(false);
const isEditMode = ref(false);
const editId = ref(null);
const deleteId = ref(null);

const form = useForm({
    email: '',
    password: '',
    role_id: 'verifier'
});

const openAddModal = () => {
    isEditMode.value = false;
    editId.value = null;
    form.reset();
    showAddModal.value = true;
};

const openEditModal = (user) => {
    isEditMode.value = true;
    editId.value = user.id;
    form.email = user.email;
    form.password = ''; // Keep password blank unless changing
    form.role_id = user.role_id;
    showAddModal.value = true;
};

const submit = () => {
    const url = isEditMode.value 
        ? route('admin.users.update', editId.value) 
        : route('admin.users.store');
    
    form.post(url, {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};

const deleteEntry = (id) => {
    deleteId.value = id;
    showDeleteConfirmModal.value = true;
};

const confirmDelete = () => {
    router.delete(route('admin.users.destroy', deleteId.value), {
        onSuccess: () => {
            showDeleteConfirmModal.value = false;
            deleteId.value = null;
        }
    });
};
</script>

<template>
    <Head title="Administrative Registry Residents" />

    <AuthenticatedLayout>
        <div class="min-h-screen bg-[#F5F6FA] py-10">
            <div class="max-w-[1400px] mx-auto px-6 lg:px-10 space-y-8">
                
                <!-- ── Administrative Header ── -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 pb-8 border-b border-gray-200">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-3 py-1 rounded-lg uppercase tracking-widest italic">System Governance</span>
                            <span class="text-gray-300">/</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Administrative Vault</span>
                        </div>
                        <h1 class="text-3xl font-black text-gray-900 tracking-tight uppercase">Registry Residents</h1>
                        <p class="text-sm text-gray-500 mt-1 max-w-lg italic font-medium">Managing institutional credentials and clearance levels for the maritime verification ledger.</p>
                    </div>
                    <button @click="openAddModal" class="inline-flex items-center gap-2.5 px-6 py-4 bg-gray-950 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-2xl hover:bg-indigo-600 transition-all active:scale-95 border border-white/5 shadow-indigo-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        Provision New Admin
                    </button>
                </div>

                <!-- ── Admin Registry Table ── -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden ring-1 ring-black/5">
                    <div class="overflow-x-auto">
                        <table class="w-full table-fixed border-collapse">
                            <colgroup>
                                <col class="w-[40%]">
                                <col class="w-[20%]">
                                <col class="w-[20%]">
                                <col class="w-[20%]">
                            </colgroup>
                            <thead>
                                <tr class="text-left border-b border-gray-100 bg-gray-50/50 uppercase tracking-widest">
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400">Electronic Mail Address</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-center">Protocol Role</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-center">Status</th>
                                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="user in admins" :key="user.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gray-950 text-white flex items-center justify-center font-black text-[10px] shadow-lg">
                                                {{ user.email.charAt(0).toUpperCase() }}
                                            </div>
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ user.email }}</p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span 
                                            :class="[
                                                'inline-flex items-center px-4 py-1.5 rounded-lg border text-[9px] font-black uppercase tracking-widest shadow-sm transition-all',
                                                user.role_id === 'super_admin' ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600'
                                            ]"
                                        >
                                            {{ user.role_id.replace('_', ' ') }}
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-gray-50 text-gray-400 text-[9px] font-black uppercase tracking-widest rounded-lg border border-gray-100 italic">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Operational
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openEditModal(user)" class="w-9 h-9 bg-white border border-gray-200 text-gray-400 rounded-xl inline-flex items-center justify-center hover:bg-amber-500 hover:text-white hover:border-amber-600 transition-all active:scale-95 shadow-sm group-hover:scale-105">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button @click="deleteEntry(user.id)" class="w-9 h-9 bg-white border border-gray-200 text-gray-400 rounded-xl inline-flex items-center justify-center hover:bg-rose-500 hover:text-white hover:border-rose-600 transition-all active:scale-95 shadow-sm group-hover:scale-105">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Provisioning SlideOver -->
        <SlideOver :show="showAddModal" @close="showAddModal = false" custom-content>
            <div class="flex flex-col h-full bg-[#0A0B10]">
                <!-- Header: Administrative Header -->
                <div class="px-8 py-10 bg-gradient-to-br from-gray-900 to-black text-white relative overflow-hidden border-b border-white/5">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-500/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                            <span class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-400 italic">{{ isEditMode ? 'Modify Clearance' : 'Secure Provisioning' }}</span>
                        </div>
                        <h3 class="text-2xl font-black uppercase tracking-tight leading-tight">{{ isEditMode ? 'Modify Admin Account' : 'Provision Admin Account' }}</h3>
                        <p class="text-xs text-gray-500 mt-2 font-medium leading-relaxed italic">Adjusting institutional access rights for existing maritime verifiers.</p>
                    </div>
                </div>

                <!-- Form: Governance Input -->
                <form @submit.prevent="submit" class="flex flex-col flex-1">
                    <div class="p-8 flex-1 space-y-8 overflow-y-auto">
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2 px-1">Institutional Email</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input v-model="form.email" type="email" placeholder="adm.central@rayterton.com" class="w-full bg-white/5 border-white/10 text-white rounded-xl py-4 pl-12 pr-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all placeholder:text-gray-700 placeholder:font-normal outline-none" required />
                            </div>
                            <div v-if="form.errors.email" class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 px-1">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2 px-1">Governance Password</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                                <input v-model="form.password" type="password" :placeholder="isEditMode ? 'Leave blank to keep current' : '••••••••'" class="w-full bg-white/5 border-white/10 text-white rounded-xl py-4 pl-12 pr-4 text-sm font-bold focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all placeholder:text-gray-700 placeholder:font-normal outline-none" :required="!isEditMode" />
                            </div>
                            <div v-if="form.errors.password" class="text-rose-500 text-[10px] font-black uppercase tracking-widest mt-2 px-1">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-[0.2em] mb-2 px-1">System Privilege Level</label>
                            <div class="grid grid-cols-1 gap-3">
                                <label v-for="role in [
                                    { id: 'verifier', label: 'Institutional Verifier', desc: 'Audit and clearance of maritime identities' },
                                    { id: 'super_admin', label: 'Super Administration', desc: 'Root access to platform governance' }
                                ]" :key="role.id" :class="['relative flex flex-col p-5 rounded-2xl border transition-all cursor-pointer group', form.role_id === role.id ? 'bg-indigo-600 border-indigo-500 shadow-xl shadow-indigo-900/40' : 'bg-white/5 border-white/10 hover:border-white/20']">
                                    <input type="radio" v-model="form.role_id" :value="role.id" class="hidden" />
                                    <div class="flex items-center justify-between">
                                        <span :class="['text-[11px] font-black uppercase tracking-widest', form.role_id === role.id ? 'text-white' : 'text-gray-300 group-hover:text-white transition-colors']">{{ role.label }}</span>
                                        <div v-if="form.role_id === role.id" class="w-4 h-4 bg-white text-indigo-600 rounded-full flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                        </div>
                                    </div>
                                    <p :class="['text-[9px] mt-1.5 font-medium italic leading-relaxed', form.role_id === role.id ? 'text-indigo-100' : 'text-gray-500 group-hover:text-gray-400 transition-colors']">{{ role.desc }}</p>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8 border-t border-white/5 bg-black/40 flex flex-col gap-4">
                        <button type="submit" :disabled="form.processing" class="w-full py-5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-500 font-black text-xs uppercase tracking-[0.2em] transition-all shadow-2xl active:scale-95 disabled:opacity-50 italic border border-white/10 shadow-indigo-900/40">
                            {{ form.processing ? 'DECRYPTING...' : (isEditMode ? 'APPLY CLEARANCE UPDATES' : 'COMMIT PROVISIONING') }}
                        </button>
                        <button type="button" @click="showAddModal = false" class="w-full py-4 text-gray-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/5 hover:text-white transition-all italic">
                            Abort Operation
                        </button>
                    </div>
                </form>
            </div>
        </SlideOver>

        <!-- Liquidation Confirm Modal -->
        <Modal :show="showDeleteConfirmModal" @close="showDeleteConfirmModal = false" maxWidth="md">
            <div class="p-8 text-center bg-gray-950 text-white rounded-2xl border border-white/10 shadow-2xl">
                <div class="w-16 h-16 bg-rose-500/20 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-6 border border-rose-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <h3 class="text-lg font-black uppercase tracking-widest mb-2">Liquidate Account?</h3>
                <p class="text-xs text-gray-400 font-medium leading-relaxed mb-8">You are about to purge this administrative credential from the system. Access rights will be revoked immediately.</p>
                <div class="flex flex-col gap-3">
                    <button @click="confirmDelete" class="w-full py-4 bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-rose-700 transition-all active:scale-95 shadow-xl shadow-rose-900/20">
                        Confirm Liquidation
                    </button>
                    <button @click="showDeleteConfirmModal = false" class="w-full py-4 bg-white/5 text-gray-400 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-white/10 hover:text-white transition-all">
                        Abort Operation
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Intelligence Toasts -->
        <div class="fixed bottom-8 right-8 z-[100] flex flex-col gap-3 pointer-events-none">
            <TransitionGroup 
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-4 opacity-0 scale-95"
                enter-to-class="translate-y-0 opacity-100 scale-100"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="$page.props.flash?.success" key="success" class="pointer-events-auto flex items-center gap-4 px-6 py-4 bg-emerald-600 text-white rounded-2xl shadow-2xl border border-white/10 min-w-[320px]">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Operation Success</p>
                        <p class="text-xs font-bold">{{ $page.props.flash?.success }}</p>
                    </div>
                </div>

                <div v-if="$page.props.flash?.error" key="error" class="pointer-events-auto flex items-center gap-4 px-6 py-4 bg-rose-600 text-white rounded-2xl shadow-2xl border border-white/10 min-w-[320px]">
                    <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Security Protocol</p>
                        <p class="text-xs font-bold">{{ $page.props.flash?.error }}</p>
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </AuthenticatedLayout>
</template>
