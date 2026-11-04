<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from '@lucide/vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import EvaluationController from '@/actions/App/Http/Controllers/EvaluationController';
import Heading from '@/components/Heading.vue';
import Pagination from '@/components/Pagination.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Button } from '@/components/ui/button';
import type { Evaluation, Labels, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: '評価', href: EvaluationController.index() }],
    },
});

defineProps<{
    evaluations: Paginated<Evaluation>;
    periods: string[];
    filters: { period: string };
    scoreLabels: Labels;
}>();

const page = usePage();

// ボタンを出すかどうかの判定だけ。本当の権限チェックはサーバー側の EvaluationPolicy が行う
const isMine = (evaluation: Evaluation): boolean =>
    evaluation.evaluator_id === page.props.auth.user.id;

const filterBy = (period: string) => {
    router.get(EvaluationController.index(), period ? { period } : {}, {
        preserveState: true,
    });
};

const deleteEvaluation = (evaluation: Evaluation) => {
    if (
        confirm(
            `${evaluation.employee?.name} さんの ${evaluation.period} の評価を削除しますか？`,
        )
    ) {
        router.delete(EvaluationController.destroy(evaluation.id), {
            preserveScroll: true,
        });
    }
};

const chipClass = (active: boolean): string =>
    active
        ? 'border-primary bg-primary text-primary-foreground'
        : 'hover:bg-accent';

const formatDate = (value: string): string =>
    new Date(value).toLocaleDateString('ja-JP');
</script>

<template>
    <Head title="評価" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading title="評価" :description="`${evaluations.total} 件`" />
            <Button as-child>
                <Link :href="EvaluationController.create()">
                    <Plus />
                    評価を登録
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                class="rounded-full border px-3 py-1 text-sm"
                :class="chipClass(filters.period === '')"
                :aria-pressed="filters.period === ''"
                @click="filterBy('')"
            >
                すべて
            </button>
            <button
                v-for="period in periods"
                :key="period"
                type="button"
                class="rounded-full border px-3 py-1 text-sm"
                :class="chipClass(filters.period === period)"
                :aria-pressed="filters.period === period"
                @click="filterBy(period)"
            >
                {{ period }}
            </button>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">期間</th>
                        <th class="px-4 py-3 font-medium">社員</th>
                        <th class="px-4 py-3 font-medium">スコア</th>
                        <th class="px-4 py-3 font-medium">コメント</th>
                        <th class="px-4 py-3 font-medium">評価者</th>
                        <th class="px-4 py-3 font-medium">登録日</th>
                        <th class="px-4 py-3">
                            <span class="sr-only">操作</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="evaluation in evaluations.data"
                        :key="evaluation.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-medium whitespace-nowrap">
                            {{ evaluation.period }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <Link
                                v-if="evaluation.employee"
                                :href="
                                    EmployeeController.show(
                                        evaluation.employee.id,
                                    )
                                "
                                class="font-medium text-primary hover:underline"
                            >
                                {{ evaluation.employee.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <ScoreBadge
                                :score="evaluation.score"
                                :labels="scoreLabels"
                            />
                        </td>
                        <td
                            class="max-w-xs truncate px-4 py-3 text-muted-foreground"
                        >
                            {{ evaluation.comment ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ evaluation.evaluator?.name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            {{ formatDate(evaluation.created_at) }}
                        </td>
                        <td class="px-4 py-3">
                            <div
                                v-if="isMine(evaluation)"
                                class="flex justify-end"
                            >
                                <Button variant="ghost" size="icon-sm" as-child>
                                    <Link
                                        :href="
                                            EvaluationController.edit(
                                                evaluation.id,
                                            )
                                        "
                                        aria-label="編集"
                                    >
                                        <Pencil />
                                    </Link>
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    aria-label="削除"
                                    @click="deleteEvaluation(evaluation)"
                                >
                                    <Trash2 />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="evaluations.data.length === 0">
                        <td
                            colspan="7"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            評価がありません。
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="evaluations.links" />
    </div>
</template>
