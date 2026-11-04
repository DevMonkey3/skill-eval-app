<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { PaginationLink } from '@/types';

defineProps<{
    links: PaginationLink[];
}>();

// Laravel のラベルは "&laquo; 前へ" のような HTML 文字参照を含むので、記号に置き換えて表示する
const labelText = (label: string): string =>
    label.replace('&laquo;', '‹').replace('&raquo;', '›');
</script>

<template>
    <nav
        v-if="links.length > 3"
        class="flex flex-wrap gap-1"
        aria-label="ページ送り"
    >
        <!-- ページが多いと "..." が 2 回出るので、key はラベルではなく位置にする -->
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                preserve-scroll
                class="rounded-md border px-3 py-1 text-sm hover:bg-accent"
                :class="{
                    'border-primary bg-primary text-primary-foreground hover:bg-primary/90':
                        link.active,
                }"
                :aria-current="link.active ? 'page' : undefined"
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
