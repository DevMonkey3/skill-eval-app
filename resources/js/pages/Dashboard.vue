<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ClipboardCheck, Plus, Sparkles, Star, Users } from '@lucide/vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import EvaluationController from '@/actions/App/Http/Controllers/EvaluationController';
import SkillController from '@/actions/App/Http/Controllers/SkillController';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { dashboard } from '@/routes';
import type { Evaluation, Labels, Skill } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'ダッシュボード', href: dashboard() }],
    },
});

const props = defineProps<{
    currentPeriod: string;
    stats: {
        employees: number;
        skills: number;
        evaluations: number;
        averageScore: number;
    };
    topSkills: Skill[];
    recentEvaluations: Evaluation[];
    scoreLabels: Labels;
}>();

const userName = usePage().props.auth.user.name;

const cards = [
    {
        label: '社員数',
        value: props.stats.employees,
        icon: Users,
        href: EmployeeController.index(),
    },
    {
        label: 'スキル数',
        value: props.stats.skills,
        icon: Sparkles,
        href: SkillController.index(),
    },
    {
        label: `今期 (${props.currentPeriod}) の評価`,
        value: props.stats.evaluations,
        icon: ClipboardCheck,
        href: EvaluationController.index({
            query: { period: props.currentPeriod },
        }),
    },
    {
        label: '今期の平均スコア',
        value: props.stats.averageScore || '-',
        icon: Star,
        href: EvaluationController.index({
            query: { period: props.currentPeriod },
        }),
    },
];

const maxCount = Math.max(
    1,
    ...props.topSkills.map((skill) => skill.employees_count ?? 0),
);
</script>

<template>
    <Head title="ダッシュボード" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- あいさつのバナー -->
        <section
            class="relative overflow-hidden rounded-xl bg-gradient-to-r from-orange-500 via-orange-400 to-amber-400 p-6 text-white shadow-sm"
        >
            <div
                class="pointer-events-none absolute -top-16 -right-10 size-56 rounded-full bg-white/15"
            />
            <div
                class="pointer-events-none absolute right-32 -bottom-20 size-40 rounded-full bg-white/10"
            />
            <div
                class="relative flex flex-wrap items-center justify-between gap-4"
            >
                <div>
                    <p class="text-sm text-white/85">
                        {{ currentPeriod }} ・ 今期
                    </p>
                    <h1 class="mt-1 text-2xl font-bold">
                        こんにちは、{{ userName }} さん
                    </h1>
                    <p class="mt-1 text-sm text-white/85">
                        社員のスキルと今期の評価の状況をひと目で確認できます。
                    </p>
                </div>
                <Link
                    :href="EvaluationController.create()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-orange-600 shadow-sm transition hover:bg-orange-50"
                >
                    <Plus class="size-4" />
                    評価を登録
                </Link>
            </div>
        </section>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Link
                v-for="card in cards"
                :key="card.label"
                :href="card.href"
                class="group rounded-xl border p-5 transition hover:border-primary/50 hover:shadow-sm"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        {{ card.label }}
                    </p>
                    <span
                        class="flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary transition group-hover:bg-primary group-hover:text-primary-foreground"
                    >
                        <component :is="card.icon" class="size-5" />
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ card.value }}</p>
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border p-6">
                <h2 class="mb-4 font-semibold">保有者が多いスキル</h2>
                <p
                    v-if="topSkills.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    スキルはまだ登録されていません。
                </p>
                <ul class="space-y-3">
                    <li v-for="skill in topSkills" :key="skill.id">
                        <div class="mb-1 flex justify-between text-sm">
                            <span class="font-medium">{{ skill.name }}</span>
                            <span class="text-muted-foreground"
                                >{{ skill.employees_count }} 名</span
                            >
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-primary"
                                :style="{
                                    width: `${((skill.employees_count ?? 0) / maxCount) * 100}%`,
                                }"
                            />
                        </div>
                    </li>
                </ul>
            </section>

            <section class="rounded-xl border p-6">
                <h2 class="mb-4 font-semibold">最近の評価</h2>
                <p
                    v-if="recentEvaluations.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    評価はまだありません。
                </p>
                <ul class="divide-y">
                    <li
                        v-for="evaluation in recentEvaluations"
                        :key="evaluation.id"
                        class="flex items-center gap-3 py-3"
                    >
                        <div class="min-w-0 flex-1">
                            <Link
                                v-if="evaluation.employee"
                                :href="
                                    EmployeeController.show(
                                        evaluation.employee.id,
                                    )
                                "
                                class="font-medium hover:underline"
                            >
                                {{ evaluation.employee.name }}
                            </Link>
                            <p class="text-xs text-muted-foreground">
                                {{ evaluation.period }} ・ 評価者
                                {{ evaluation.evaluator?.name }}
                            </p>
                        </div>
                        <ScoreBadge
                            :score="evaluation.score"
                            :labels="scoreLabels"
                        />
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
