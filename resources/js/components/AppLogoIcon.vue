<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Anzar Water Droplet -->
        <path
            d="M12 2.25C12 2.25 18.25 8.65 18.25 13.75C18.25 17.2 15.45 20 12 20C8.55 20 5.75 17.2 5.75 13.75C5.75 8.65 12 2.25 12 2.25Z"
            fill="currentColor"
        />
        <!-- Internal Light Reflection Wave -->
        <path
            d="M10.2 7.2C11.6 8.7 13.2 10.8 13.2 13.2"
            fill="none"
            stroke="rgba(255, 255, 255, 0.65)"
            stroke-width="1.6"
            stroke-linecap="round"
        />
    </svg>
</template>

