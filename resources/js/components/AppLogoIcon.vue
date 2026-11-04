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
    <!-- 地平線から昇る太陽 (アプリ名 Hinata = 日向) -->
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 40"
        :class="className"
        v-bind="$attrs"
    >
        <path fill="currentColor" d="M8 29a12 12 0 0 1 24 0Z" />
        <g
            fill="none"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
        >
            <path d="M20 5v6" />
            <path d="m7.3 10.3 4.2 4.2" />
            <path d="m32.7 10.3-4.2 4.2" />
            <path d="M2.5 22.5h4.5" />
            <path d="M33 22.5h4.5" />
        </g>
        <rect x="3" y="31" width="34" height="3" rx="1.5" fill="currentColor" />
    </svg>
</template>
