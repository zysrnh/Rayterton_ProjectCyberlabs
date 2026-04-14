<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    options: Array,
    placeholder: String,
    label: String
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const container = ref(null);

const toggle = () => isOpen.value = !isOpen.value;
const close = () => isOpen.value = false;

const selectOption = (option) => {
    emit('update:modelValue', option);
    close();
};

const handleClickOutside = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        close();
    }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));

</script>

<template>
    <div class="relative min-w-[220px]" ref="container">
        <!-- Trigger Label -->
        <p v-if="label" class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-2 px-1">{{ label }}</p>
        
        <!-- Dropdown Trigger -->
        <button 
            @click="toggle"
            type="button"
            :class="[
                'w-full bg-gray-50/50 border border-transparent rounded-[1.2rem] px-6 py-4 text-[10px] font-black uppercase tracking-widest text-left transition-all flex items-center justify-between group',
                isOpen ? 'bg-white border-indigo-600 shadow-xl shadow-indigo-100 ring-4 ring-indigo-50' : 'hover:bg-gray-100'
            ]"
        >
            <span :class="modelValue ? 'text-gray-900' : 'text-gray-400'">
                {{ modelValue || placeholder }}
            </span>
            <svg 
                :class="['w-4 h-4 text-gray-300 transition-transform duration-300', isOpen ? 'rotate-180 text-indigo-600' : 'group-hover:text-gray-400']" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition-all ease-out duration-300 transform"
            enter-from-class="opacity-0 scale-95 -translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all ease-in duration-200 transform"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1"
        >
            <div 
                v-if="isOpen"
                class="absolute z-30 w-full mt-3 bg-white border border-gray-100 rounded-[1.5rem] shadow-[0_30px_60px_-15px_rgba(0,0,0,0.1)] overflow-hidden py-2"
            >
                <button 
                    @click="selectOption('')"
                    class="w-full px-6 py-3.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50 hover:text-indigo-600 transition-all border-b border-gray-50 mb-1"
                >
                    Show: All Options
                </button>
                <div class="max-h-60 overflow-y-auto">
                    <button 
                        v-for="opt in options" 
                        :key="opt"
                        @click="selectOption(opt)"
                        :class="[
                            'w-full px-6 py-3.5 text-left text-[10px] font-black uppercase tracking-widest transition-all',
                            modelValue === opt ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-indigo-600'
                        ]"
                    >
                        {{ opt }}
                    </button>
                </div>
            </div>
        </Transition>
    </div>
</template>
