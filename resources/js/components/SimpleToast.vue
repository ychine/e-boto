<script setup lang="ts">
import { ref, onMounted } from 'vue';

interface Props {
    message: string;
    type?: 'error' | 'success' | 'info';
    duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'error',
    duration: 3000,
});

const show = ref(true);

onMounted(() => {
    if (props.duration > 0) {
        setTimeout(() => {
            show.value = false;
        }, props.duration);
    }
});

const bgColor = {
    error: 'bg-red-500',
    success: 'bg-green-500',
    info: 'bg-green-500',
}[props.type];
</script>

<template>
    <Transition>
        <div
            v-if="show"
            :class="[
                bgColor,
                'fixed top-4 right-4 z-50 rounded-lg px-4 py-3 text-white shadow-lg',
            ]"
        >
            {{ message }}
        </div>
    </Transition>
</template>

<style scoped>
.v-enter-active,
.v-leave-active {
    transition: opacity 0.2s;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}
</style>

