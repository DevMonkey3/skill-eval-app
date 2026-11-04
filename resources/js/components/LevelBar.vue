<script setup lang="ts">
const { level, max = 5 } = defineProps<{
    level: number;
    max?: number;
}>();
</script>

<template>
    <div
        class="h-2 w-full overflow-hidden rounded-full bg-muted"
        role="meter"
        :aria-valuenow="level"
        aria-valuemin="0"
        :aria-valuemax="max"
    >
        <div
            class="h-full rounded-full bg-primary transition-all duration-500"
            :style="{ width: `${Math.min(100, (level / max) * 100)}%` }"
        />
    </div>
</template>
