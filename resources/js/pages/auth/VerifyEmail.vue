<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: 'メールアドレスの確認',
        description:
            'お送りしたメールに記載のリンクをクリックして、メールアドレスを確認してください。',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="メールアドレスの確認" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        ご登録のメールアドレスに新しい確認用リンクを送信しました。
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            確認メールを再送信
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            ログアウト
        </TextLink>
    </Form>
</template>
