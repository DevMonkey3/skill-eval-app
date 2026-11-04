<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import SkillController from '@/actions/App/Http/Controllers/SkillController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import NativeSelect from '@/components/NativeSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Skill } from '@/types';

const props = defineProps<{
    skill?: Skill;
    categories: string[];
}>();

const title = props.skill ? 'スキルを編集' : 'スキルを登録';

setLayoutProps({
    breadcrumbs: [
        { title: 'スキル', href: SkillController.index() },
        { title: props.skill ? '編集' : '登録', href: '#' },
    ],
});

const form = useForm({
    name: props.skill?.name ?? '',
    category: props.skill?.category ?? '',
});

const submit = () => {
    form.submit(
        props.skill
            ? SkillController.update(props.skill.id)
            : SkillController.store(),
    );
};
</script>

<template>
    <Head :title="title" />

    <div class="max-w-xl p-4 md:p-6">
        <Heading :title="title" />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">スキル名 *</Label>
                <Input id="name" v-model="form.name" placeholder="Laravel" />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="category">カテゴリ *</Label>
                <NativeSelect id="category" v-model="form.category">
                    <option value="" disabled>選択してください</option>
                    <option
                        v-for="category in categories"
                        :key="category"
                        :value="category"
                    >
                        {{ category }}
                    </option>
                </NativeSelect>
                <InputError :message="form.errors.category" />
            </div>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ skill ? '更新する' : '登録する' }}
                </Button>
                <Button variant="outline" as-child>
                    <Link :href="SkillController.index()">キャンセル</Link>
                </Button>
            </div>
        </form>
    </div>
</template>
