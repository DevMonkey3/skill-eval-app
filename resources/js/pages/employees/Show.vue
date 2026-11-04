<script setup lang="ts">
import { Head, Link, router, setLayoutProps, useForm } from '@inertiajs/vue3';
import { Mail, Pencil, Plus, Star, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import EmployeeSkillController from '@/actions/App/Http/Controllers/EmployeeSkillController';
import EvaluationController from '@/actions/App/Http/Controllers/EvaluationController';
import InputError from '@/components/InputError.vue';
import LevelBar from '@/components/LevelBar.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import ScoreBadge from '@/components/ScoreBadge.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Employee, Labels, Skill } from '@/types';

const props = defineProps<{
    employee: Employee;
    availableSkills: Skill[];
    levelLabels: Labels;
    scoreLabels: Labels;
}>();

setLayoutProps({
    breadcrumbs: [
        { title: '社員', href: EmployeeController.index() },
        {
            title: props.employee.name,
            href: EmployeeController.show(props.employee.id),
        },
    ],
});

const skills = computed(() => props.employee.skills ?? []);
const evaluations = computed(() => props.employee.evaluations ?? []);

const averageLevel = computed(() => {
    if (skills.value.length === 0) {
        return '-';
    }

    const total = skills.value.reduce(
        (sum, skill) => sum + (skill.pivot?.level ?? 0),
        0,
    );

    return (total / skills.value.length).toFixed(1);
});

// スキル追加フォーム
const addForm = useForm({
    skill_id: props.availableSkills[0]?.id ?? null,
    level: 3,
});

const addSkill = () => {
    addForm.submit(EmployeeSkillController.store(props.employee.id), {
        preserveScroll: true,
        onSuccess: () => {
            // 追加したスキルは選択肢から消えるので、次の候補を選び直す
            addForm.skill_id = props.availableSkills[0]?.id ?? null;
        },
    });
};

// レベル変更は選んだ瞬間に送る (同じ store エンドポイントがレベル更新も兼ねる)
const changeLevel = (skill: Skill, event: Event) => {
    const level = Number((event.target as HTMLSelectElement).value);

    router.post(
        EmployeeSkillController.store(props.employee.id),
        { skill_id: skill.id, level },
        { preserveScroll: true },
    );
};

const removeSkill = (skill: Skill) => {
    if (confirm(`${skill.name} を外しますか？`)) {
        router.delete(
            EmployeeSkillController.destroy([props.employee.id, skill.id]),
            {
                preserveScroll: true,
            },
        );
    }
};

const deleteEmployee = () => {
    if (
        confirm(
            `${props.employee.name} を削除しますか？スキルと評価もすべて削除されます。`,
        )
    ) {
        router.delete(EmployeeController.destroy(props.employee.id));
    }
};
</script>

<template>
    <Head :title="employee.name" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <!-- プロフィール -->
        <section class="overflow-hidden rounded-xl border">
            <div
                class="h-24 bg-gradient-to-r from-orange-500 via-amber-400 to-orange-300"
            />
            <div
                class="flex flex-wrap items-end justify-between gap-4 px-6 pb-6"
            >
                <div class="flex items-end gap-4">
                    <div
                        class="-mt-10 flex size-20 shrink-0 items-center justify-center rounded-full bg-primary text-2xl font-bold text-primary-foreground ring-4 ring-background"
                    >
                        {{ employee.name.charAt(0) }}
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">{{ employee.name }}</h1>
                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                        >
                            <Badge variant="secondary">{{
                                employee.department
                            }}</Badge>
                            <Badge variant="outline">{{
                                employee.position
                            }}</Badge>
                            <span class="font-mono text-xs">{{
                                employee.employee_code
                            }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Button as-child>
                        <Link
                            :href="
                                EvaluationController.create({
                                    query: { employee_id: employee.id },
                                })
                            "
                        >
                            <Star />
                            評価する
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="EmployeeController.edit(employee.id)">
                            <Pencil />
                            編集
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="deleteEmployee">
                        <Trash2 />
                        削除
                    </Button>
                </div>
            </div>

            <dl class="grid gap-4 border-t px-6 py-4 text-sm sm:grid-cols-4">
                <div class="sm:col-span-2">
                    <dt class="text-muted-foreground">メールアドレス</dt>
                    <dd class="flex items-center gap-1.5 font-medium">
                        <Mail class="size-4" />{{ employee.email }}
                    </dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">入社日</dt>
                    <dd class="font-medium">{{ employee.hired_on ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-muted-foreground">平均スキルレベル</dt>
                    <dd class="font-medium">{{ averageLevel }}</dd>
                </div>
            </dl>
        </section>

        <div class="grid gap-6 lg:grid-cols-5">
            <!-- 保有スキル -->
            <section class="rounded-xl border p-6 lg:col-span-3">
                <h2 class="mb-4 font-semibold">
                    保有スキル（{{ skills.length }}）
                </h2>

                <p
                    v-if="skills.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    スキルが登録されていません。下のフォームから追加できます。
                </p>

                <ul class="space-y-4">
                    <li
                        v-for="skill in skills"
                        :key="skill.id"
                        class="flex flex-wrap items-center gap-3"
                    >
                        <div class="w-36">
                            <p class="font-medium">{{ skill.name }}</p>
                            <p class="text-xs text-muted-foreground">
                                {{ skill.category }}
                            </p>
                        </div>
                        <LevelBar
                            :level="skill.pivot?.level ?? 0"
                            class="min-w-24 flex-1"
                        />
                        <NativeSelect
                            :model-value="skill.pivot?.level"
                            class="h-8 w-36 text-xs md:text-xs"
                            :aria-label="`${skill.name} のレベル`"
                            @change="changeLevel(skill, $event)"
                        >
                            <option
                                v-for="(label, level) in levelLabels"
                                :key="level"
                                :value="Number(level)"
                            >
                                Lv.{{ level }} {{ label }}
                            </option>
                        </NativeSelect>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            :aria-label="`${skill.name} を外す`"
                            @click="removeSkill(skill)"
                        >
                            <Trash2 />
                        </Button>
                    </li>
                </ul>

                <form
                    v-if="availableSkills.length > 0"
                    class="mt-6 rounded-lg border border-dashed p-4"
                    @submit.prevent="addSkill"
                >
                    <p class="mb-3 text-sm font-medium">スキルを追加</p>
                    <div class="flex flex-wrap gap-3">
                        <NativeSelect
                            v-model="addForm.skill_id"
                            class="flex-1"
                            aria-label="スキル"
                        >
                            <option
                                v-for="skill in availableSkills"
                                :key="skill.id"
                                :value="skill.id"
                            >
                                {{ skill.name }}（{{ skill.category }}）
                            </option>
                        </NativeSelect>
                        <NativeSelect
                            v-model="addForm.level"
                            class="w-40"
                            aria-label="レベル"
                        >
                            <option
                                v-for="(label, level) in levelLabels"
                                :key="level"
                                :value="Number(level)"
                            >
                                Lv.{{ level }} {{ label }}
                            </option>
                        </NativeSelect>
                        <Button type="submit" :disabled="addForm.processing">
                            <Plus />
                            追加
                        </Button>
                    </div>
                    <InputError
                        class="mt-2"
                        :message="
                            addForm.errors.skill_id ?? addForm.errors.level
                        "
                    />
                </form>
            </section>

            <!-- 評価履歴 -->
            <section class="rounded-xl border p-6 lg:col-span-2">
                <h2 class="mb-4 font-semibold">
                    評価履歴（{{ evaluations.length }}）
                </h2>

                <p
                    v-if="evaluations.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    評価はまだありません。
                </p>

                <ol
                    class="space-y-4 border-l-2 border-dashed border-primary/30 pl-4"
                >
                    <li v-for="evaluation in evaluations" :key="evaluation.id">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-semibold">{{
                                evaluation.period
                            }}</span>
                            <ScoreBadge
                                :score="evaluation.score"
                                :labels="scoreLabels"
                            />
                            <span
                                class="ml-auto text-xs text-muted-foreground"
                                >{{ evaluation.evaluator?.name }}</span
                            >
                        </div>
                        <p
                            v-if="evaluation.comment"
                            class="mt-1 rounded-md bg-muted/50 p-3 text-sm"
                        >
                            {{ evaluation.comment }}
                        </p>
                    </li>
                </ol>
            </section>
        </div>
    </div>
</template>
