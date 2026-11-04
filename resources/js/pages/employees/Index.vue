<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from '@lucide/vue';
import { ref, watch } from 'vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import Heading from '@/components/Heading.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Employee, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: '社員', href: EmployeeController.index() }],
    },
});

const props = defineProps<{
    employees: Paginated<Employee>;
    filters: { search: string; department: string };
    departments: string[];
}>();

const search = ref(props.filters.search);
const department = ref(props.filters.department);

// 入力が止まって 300ms 後に一覧を読み直す (1文字ごとにリクエストしない)
let timer: ReturnType<typeof setTimeout> | undefined;

watch([search, department], () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get(
            EmployeeController.index(),
            {
                search: search.value || undefined,
                department: department.value || undefined,
            },
            { preserveState: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="社員一覧" />

    <div class="flex flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <Heading
                title="社員一覧"
                :description="`全 ${employees.total} 名`"
            />
            <Button as-child>
                <Link :href="EmployeeController.create()">
                    <Plus />
                    社員を登録
                </Link>
            </Button>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative sm:w-72">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    type="search"
                    placeholder="名前・社員番号で検索"
                    class="pl-9"
                />
            </div>
            <NativeSelect
                v-model="department"
                class="sm:w-48"
                aria-label="部署で絞り込み"
            >
                <option value="">すべての部署</option>
                <option v-for="name in departments" :key="name" :value="name">
                    {{ name }}
                </option>
            </NativeSelect>
        </div>

        <div class="overflow-x-auto rounded-xl border">
            <table class="w-full text-left text-sm">
                <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3 font-medium">社員番号</th>
                        <th class="px-4 py-3 font-medium">氏名</th>
                        <th class="px-4 py-3 font-medium">部署</th>
                        <th class="px-4 py-3 font-medium">役職</th>
                        <th class="px-4 py-3 text-right font-medium">
                            スキル数
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="employee in employees.data"
                        :key="employee.id"
                        class="border-t hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ employee.employee_code }}
                        </td>
                        <td class="px-4 py-3">
                            <Link
                                :href="EmployeeController.show(employee.id)"
                                class="font-medium text-primary hover:underline"
                            >
                                {{ employee.name }}
                            </Link>
                            <p class="text-xs text-muted-foreground">
                                {{ employee.email }}
                            </p>
                        </td>
                        <td class="px-4 py-3">{{ employee.department }}</td>
                        <td class="px-4 py-3">{{ employee.position }}</td>
                        <td class="px-4 py-3 text-right">
                            {{ employee.skills_count }}
                        </td>
                    </tr>
                    <tr v-if="employees.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-12 text-center text-muted-foreground"
                        >
                            該当する社員がいません。
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :links="employees.links" />
    </div>
</template>
