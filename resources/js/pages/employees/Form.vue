<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Employee } from '@/types';

// 登録と編集で同じページを使う。employee があれば編集
const props = defineProps<{
    employee?: Employee;
    departments: string[];
    positions: string[];
}>();

const title = props.employee ? '社員を編集' : '社員を登録';

setLayoutProps({
    breadcrumbs: [
        { title: '社員', href: EmployeeController.index() },
        ...(props.employee
            ? [
                  {
                      title: props.employee.name,
                      href: EmployeeController.show(props.employee.id),
                  },
              ]
            : []),
        { title: props.employee ? '編集' : '登録', href: '#' },
    ],
});

const form = useForm({
    employee_code: props.employee?.employee_code ?? '',
    name: props.employee?.name ?? '',
    email: props.employee?.email ?? '',
    department: props.employee?.department ?? '',
    position: props.employee?.position ?? '',
    hired_on: props.employee?.hired_on ?? '',
});

const submit = () => {
    form.submit(
        props.employee
            ? EmployeeController.update(props.employee.id)
            : EmployeeController.store(),
    );
};

const cancelUrl = props.employee
    ? EmployeeController.show(props.employee.id)
    : EmployeeController.index();
</script>

<template>
    <Head :title="title" />

    <div class="max-w-2xl p-4 md:p-6">
        <Heading :title="title" description="* は必須項目です" />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="employee_code">社員番号 *</Label>
                    <Input
                        id="employee_code"
                        v-model="form.employee_code"
                        placeholder="E00001"
                    />
                    <InputError :message="form.errors.employee_code" />
                </div>

                <div class="grid gap-2">
                    <Label for="hired_on">入社日</Label>
                    <Input id="hired_on" v-model="form.hired_on" type="date" />
                    <InputError :message="form.errors.hired_on" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="name">氏名 *</Label>
                <Input id="name" v-model="form.name" placeholder="山田 太郎" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">メールアドレス *</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    placeholder="taro@example.com"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="department">部署 *</Label>
                    <NativeSelect id="department" v-model="form.department">
                        <option value="" disabled>選択してください</option>
                        <option
                            v-for="name in departments"
                            :key="name"
                            :value="name"
                        >
                            {{ name }}
                        </option>
                    </NativeSelect>
                    <InputError :message="form.errors.department" />
                </div>

                <div class="grid gap-2">
                    <Label for="position">役職 *</Label>
                    <NativeSelect id="position" v-model="form.position">
                        <option value="" disabled>選択してください</option>
                        <option
                            v-for="name in positions"
                            :key="name"
                            :value="name"
                        >
                            {{ name }}
                        </option>
                    </NativeSelect>
                    <InputError :message="form.errors.position" />
                </div>
            </div>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ employee ? '更新する' : '登録する' }}
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="cancelUrl">キャンセル</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
