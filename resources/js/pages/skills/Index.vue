<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2, Users } from '@lucide/vue';
import { computed } from 'vue';
import SkillController from '@/actions/App/Http/Controllers/SkillController';
import Heading from '@/components/Heading.vue';
import LevelBar from '@/components/LevelBar.vue';
import { Button } from '@/components/ui/button';
import { useIsAdmin } from '@/composables/useIsAdmin';
import type { Skill } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'スキル', href: SkillController.index() }],
    },
});

const props = defineProps<{
    skills: Skill[];
    categories: string[];
}>();

const isAdmin = useIsAdmin();

// カテゴリごとにまとめる (順番は Skill::CATEGORIES の順)
const groups = computed(() =>
    props.categories
        .map((category) => ({
            category,
            skills: props.skills.filter((skill) => skill.category === category),
        }))
        .filter((group) => group.skills.length > 0),
);

const averageLevel = (skill: Skill): number => Number(skill.average_level ?? 0);

const deleteSkill = (skill: Skill) => {
    if (
        confirm(
            `${skill.name} を削除しますか？社員に付いているこのスキルも外れます。`,
        )
    ) {
        router.delete(SkillController.destroy(skill.id));
    }
};
</script>

<template>
    <Head title="スキル" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading title="スキル" :description="`全 ${skills.length} 件`" />
            <Button v-if="isAdmin" as-child>
                <Link :href="SkillController.create()">
                    <Plus />
                    スキルを登録
                </Link>
            </Button>
        </div>

        <p
            v-if="skills.length === 0"
            class="rounded-xl border p-12 text-center text-muted-foreground"
        >
            スキルが登録されていません。
        </p>

        <section v-for="group in groups" :key="group.category">
            <h2 class="mb-3 text-sm font-semibold text-muted-foreground">
                {{ group.category }}（{{ group.skills.length }}）
            </h2>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="skill in group.skills"
                    :key="skill.id"
                    class="rounded-xl border p-5"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-lg font-semibold">
                                {{ skill.name }}
                            </p>
                            <p
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                            >
                                <Users class="size-3.5" />
                                保有者 {{ skill.employees_count }} 名
                            </p>
                        </div>
                        <div v-if="isAdmin" class="flex">
                            <Button variant="ghost" size="icon-sm" as-child>
                                <Link
                                    :href="SkillController.edit(skill.id)"
                                    :aria-label="`${skill.name} を編集`"
                                >
                                    <Pencil />
                                </Link>
                            </Button>
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                :aria-label="`${skill.name} を削除`"
                                @click="deleteSkill(skill)"
                            >
                                <Trash2 />
                            </Button>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-xs">
                            <span class="text-muted-foreground"
                                >平均レベル</span
                            >
                            <span class="font-medium"
                                >{{ averageLevel(skill).toFixed(1) }} / 5</span
                            >
                        </div>
                        <LevelBar :level="averageLevel(skill)" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
