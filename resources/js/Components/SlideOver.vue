<script setup>
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Panel Details',
    },
    customContent: {
        type: Boolean,
        default: false,
    }
});

const emit = defineEmits(['close']);

const close = () => {
    emit('close');
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = null;
        }
    }
);
</script>

<template>
    <teleport to="body">
        <transition leave-active-class="duration-200">
            <div v-show="show" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Backdrop -->
                    <transition
                        enter-active-class="ease-in-out duration-500"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="ease-in-out duration-500"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-show="show" class="absolute inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" @click="close"></div>
                    </transition>

                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <!-- Slide-over panel -->
                        <transition
                            enter-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                            enter-from-class="translate-x-full"
                            enter-to-class="translate-x-0"
                            leave-active-class="transform transition ease-in-out duration-500 sm:duration-700"
                            leave-from-class="translate-x-0"
                            leave-to-class="translate-x-full"
                        >
                            <div v-show="show" class="pointer-events-auto relative w-screen max-w-4xl">
                                <!-- Close button corner -->
                                <div v-show="show" class="absolute left-0 top-0 -ml-16 flex pr-10 pt-10 sm:-ml-20">
                                    <button @click="close" type="button" class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-xl text-white hover:bg-rose-500 hover:scale-110 transition-all shadow-2xl flex items-center justify-center border border-white/10 group">
                                        <svg class="h-8 w-8 group-hover:rotate-90 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <div :class="['flex h-full flex-col overflow-y-auto bg-white shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)]', customContent ? 'bg-[#0A0B10]' : '']">
                                    <div v-if="!customContent" class="px-12 py-12 bg-indigo-600 shadow-2xl relative overflow-hidden">
                                        <div class="absolute -right-10 -top-10 opacity-10">
                                             <svg class="w-48 h-48" fill="white" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                        </div>
                                        <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white relative z-10" id="slide-over-title">{{ title }}</h2>
                                        <p class="text-[10px] font-black text-indigo-200 uppercase tracking-[0.3em] mt-2 relative z-10">Secure Verification Environment</p>
                                    </div>
                                    <div class="relative flex-1">
                                        <!-- Slot for content -->
                                        <slot />
                                    </div>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>
