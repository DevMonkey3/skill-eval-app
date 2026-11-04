<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import EvaluationController from '@/actions/App/Http/Controllers/EvaluationController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Employee, Evaluation, Labels } from '@/types';

const props = defineProps<{
    evaluation?: Evaluation;
    employees: Pick<Employee, 'id' | 'employee_code' | 'name'>[];
    scoreLabels: Labels;
    defaultPeriod: string;
    selectedEmployeeId: number | null;
}>();

const title = props.evaluation ? '評価を編集' : '評価を登録';

setLayoutProps({
    breadcrumbs: [
        { title: '評価', href: EvaluationController.index() },
        { title: props.evaluation ? '編集' : '登録', href: '#' },
    ],
});

const form = useForm({
    employee_id: props.selectedEmployeeId,
    period: props.defaultPeriod,
    score: props.evaluation?.score ?? 3,
    comment: props.evaluation?.comment ?? '',
});

const submit = () => {
    form.submit(
        props.evaluation
            ? EvaluationController.update(props.evaluation.id)
            : EvaluationController.store(),
    );
};
</script>

<template>
    <Head :title="title" />

    <div class="max-w-2xl p-4 md:p-6">
        <Heading :title="title" />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="grid gap-2 sm:col-span-2">
                    <Label for="employee_id">社員 *</Label>
                    <NativeSelect id="employee_id" v-model="form.employee_id">
                        <option :value="null" disabled>選択してください</option>
                        <option
                            v-for="employee in employees"
                            :key="employee.id"
                            :value="employee.id"
                        >
                            {{ employee.employee_code }} {{ employee.name }}
                        </option>
                    </NativeSelect>
                    <InputError :message="form.errors.employee_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="period">評価期間 *</Label>
                    <Input
                        id="period"
                        v-model="form.period"
                        placeholder="2026-H1"
                    />
                    <InputError :message="form.errors.period" />
                </div>
            </div>

            <fieldset class="grid gap-2">
                <legend class="mb-2 text-sm font-medium">スコア *</legend>
                <div class="grid grid-cols-5 gap-2">
                    <label
                        v-for="(label, score) in scoreLabels"
                        :key="score"
                        class="flex cursor-pointer flex-col items-center rounded-lg border p-3 text-center transition hover:bg-accent has-[:checked]:border-primary has-[:checked]:bg-primary/10"
                    >
                        <input
                            v-model="form.score"
                            type="radio"
                            name="score"
                            :value="Number(score)"
                            class="sr-only"
                        />
                        <span class="text-2xl font-bold">{{ score }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            label
                        }}</span>
                    </label>
                </div>
                <InputError :message="form.errors.score" />
            </fieldset>

            <div class="grid gap-2">
                <Label for="comment">コメント</Label>
                <textarea
                    id="comment"
                    v-model="form.comment"
                    rows="5"
                    maxlength="1000"
                    class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 dark:bg-input/30"
                    placeholder="良かった点・期待する点など"
                />
                <p class="text-right text-xs text-muted-foreground">
                    {{ form.comment.length }} / 1000
                </p>
                <InputError :message="form.errors.comment" />
            </div>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ evaluation ? '更新する' : '登録する' }}
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="EvaluationController.index()">キャンセル</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
