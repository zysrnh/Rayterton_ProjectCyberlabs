<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import SlideOver from '@/Components/SlideOver.vue';
import { ref } from 'vue';

const props = defineProps({
    admins: Array
});

const showAddModal = ref(false);

const form = useForm({
    email: '',
    password: '',
    role_id: 'verifier'
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        }
    });
};
</script>

<template>
    <Head title="Manage Admin Accounts" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 md:p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-gray-200">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                Manage Admin Accounts
                            </h2>
                            <p class="text-gray-500 mt-1">Tambahkan akun Super Admin atau Verifikator baru.</p>
                        </div>
                        <button @click="showAddModal = true" class="px-5 py-2.5 bg-indigo-600 text-white font-bold rounded-lg shadow hover:bg-indigo-700 transition">
                            + Tambah Akun
                        </button>
                    </div>
                </div>

                <!-- Table of Admins -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="p-4 font-semibold">Email</th>
                                <th class="p-4 font-semibold">Role</th>
                                <th class="p-4 font-semibold">Status</th>
                                <th class="p-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in admins" :key="user.id" class="border-b border-gray-100 hover:bg-gray-50 transition">
                                <td class="p-4 text-gray-800 font-medium">{{ user.email }}</td>
                                <td class="p-4">
                                    <span 
                                        class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                                        :class="user.role_id === 'super_admin' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700'"
                                    >
                                        {{ user.role_id.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-1 rounded">Active</span>
                                </td>
                                <td class="p-4 text-right">
                                    <button class="text-sm text-blue-600 font-semibold hover:text-blue-800 transition">Edit</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add SlideOver -->
        <SlideOver :show="showAddModal" @close="showAddModal = false" title="Tambah Akun Admin Baru">
            <div class="flex flex-col h-full pb-6">
                <form @submit.prevent="submit" class="flex flex-col h-full">
                    <div class="space-y-4 flex-1">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input v-model="form.email" type="email" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required />
                            <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input v-model="form.password" type="password" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required />
                            <div v-if="form.errors.password" class="text-red-500 text-sm mt-1">{{ form.errors.password }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select v-model="form.role_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="verifier">Verifier (Can review and approve alumni profile)</option>
                                <option value="super_admin">Super Admin (Full access, system configuration)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex flex-col gap-3 justify-end border-t pt-6">
                        <button type="submit" :disabled="form.processing" class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold transition shadow-sm disabled:opacity-50">
                            Simpan Akun
                        </button>
                        <button type="button" @click="showAddModal = false" class="w-full px-4 py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 font-semibold transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </SlideOver>
    </AuthenticatedLayout>
</template>
