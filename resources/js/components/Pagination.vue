<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types';

defineProps<{
    links: PaginationLink[];
}>();

// Laravel のラベルは "&laquo; Previous" のような HTML なので、記号に置き換えて表示する
const labelText = (label: string): string =>
    label
        .replace('&laquo; Previous', '‹ 前へ')
        .replace('Next &raquo;', '次へ ›');
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap gap-1"
        aria-label="ページ送り"
    >
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-md border px-3 py-1 text-sm hover:bg-accent"
                :class="{
                    'border-primary bg-primary text-primary-foreground hover:bg-primary/90':
                        link.active,
                }"
            >
                {{ labelText(link.label) }}
            </Link>
            <span
                v-else
                class="rounded-md border px-3 py-1 text-sm text-muted-foreground opacity-50"
            >
                {{ labelText(link.label) }}
            </span>
        </template>
    </nav>
</template>
