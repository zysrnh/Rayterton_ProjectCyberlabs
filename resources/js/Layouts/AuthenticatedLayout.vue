<script setup>
import { ref } from 'vue';
import Sidebar from '@/Components/Sidebar.vue';
import Navbar from '@/Components/Navbar.vue';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Component -->
        <Sidebar :showing="showingNavigationDropdown" @close="showingNavigationDropdown = false" />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 bg-gray-50 overflow-hidden">
            <!-- Navbar Component -->
            <Navbar 
                :auth="$page.props.auth" 
                :notifications="$page.props.notifications"
                @toggle-sidebar="showingNavigationDropdown = !showingNavigationDropdown"
            >
                <template #header v-if="$slots.header">
                    <slot name="header" />
                </template>
            </Navbar>

            <!-- Page Content -->
            <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">
                <slot />
            </main>
        </div>
        
        <!-- Overlay -->
        <div v-if="showingNavigationDropdown" @click="showingNavigationDropdown = false" class="fixed inset-0 z-20 bg-gray-900/60 backdrop-blur-sm lg:hidden transition-all duration-300"></div>
    </div>
</template>
