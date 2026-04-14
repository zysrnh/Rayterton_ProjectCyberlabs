<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
});

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
        '64': 'w-64',
        '80': 'w-80',
        '96': 'w-96',
    }[props.width.toString()] || 'w-48';
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right start-0';
    } else if (props.align === 'right') {
        return 'ltr:origin-top-right rtl:origin-top-left end-0';
    } else {
        return 'origin-top';
    }
});

const open = ref(false);

const toggleDropdown = () => {
    open.value = !open.value;
};

</script>

<template>
    <div class="relative">
        <div @click="toggleDropdown">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay (Click outside to close) -->
        <div
            v-if="open"
            class="fixed inset-0 z-40 bg-black/5"
            @click="open = false"
        ></div>

        <Transition
            enter-active-class="transition-all ease-out duration-300 transform"
            enter-from-class="opacity-0 scale-90 -translate-y-2 blur-sm"
            enter-to-class="opacity-100 scale-100 translate-y-0 blur-0"
            leave-active-class="transition-all ease-in duration-200 transform"
            leave-from-class="opacity-100 scale-100 translate-y-0 blur-0"
            leave-to-class="opacity-0 scale-95 -translate-y-1 blur-sm"
        >
            <div
                v-if="open"
                class="absolute z-50 mt-4"
                :class="[widthClass, alignmentClasses]"
                @click="open = false"
            >
                <div
                    class="rounded-[2rem] ring-1 ring-black ring-opacity-5 shadow-[0_30px_60px_-15px_rgba(0,0,0,0.15)] overflow-hidden"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
