<script setup lang="ts">
import type { Labels } from '@/types';

const { score, labels } = defineProps<{
    score: number;
    labels: Labels;
}>();

const colors: Record<number, string> = {
    1: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    2: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
    3: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    4: 'bg-orange-100 text-orange-700 dark:bg-orange-950 dark:text-orange-300',
    5: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
        :class="colors[score]"
    >
        {{ score }}
        <span class="font-normal">{{ labels[score] }}</span>
    </span>
</template>
