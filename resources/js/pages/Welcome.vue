<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowRight,
    ClipboardCheck,
    FolderGit2,
    KeyRound,
    Sparkles,
    Users,
} from '@lucide/vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

const features = [
    {
        icon: Users,
        title: '社員管理',
        text: '社員の登録・検索・部署での絞り込み。保有スキルと評価履歴を 1 画面で確認できます。',
    },
    {
        icon: Sparkles,
        title: 'スキルの見える化',
        text: 'スキルごとの保有者数と平均レベルをカテゴリ別に一覧。レベルは 1〜5 の 5 段階です。',
    },
    {
        icon: ClipboardCheck,
        title: '半期ごとの評価',
        text: '4 月始まりの上期 / 下期で評価を記録。編集・削除できるのは評価を書いた本人だけです。',
    },
    {
        icon: KeyRound,
        title: 'REST API',
        text: 'Sanctum のトークン認証で、社員と評価を外部システムから取得・登録できます。',
    },
];

const stack = [
    'Laravel 13',
    'Vue 3',
    'TypeScript',
    'Inertia.js',
    'PostgreSQL',
    'Tailwind CSS',
    'Sanctum',
    'GitHub Actions',
];
</script>

<template>
    <Head title="ようこそ" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header
            class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4 px-6 py-5"
        >
            <div class="flex items-center gap-2">
                <span
                    class="flex size-9 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-amber-400 text-white shadow-sm"
                >
                    <AppLogoIcon class="size-6" />
                </span>
                <span class="text-lg font-semibold">Hinata</span>
            </div>
            <nav class="flex items-center gap-2">
                <Button v-if="$page.props.auth.user" as-child>
                    <Link :href="dashboard()">ダッシュボード</Link>
                </Button>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">ログイン</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">新規登録</Link>
                    </Button>
                </template>
            </nav>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-6">
            <section class="py-16 text-center md:py-24">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full border bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground"
                >
                    <Sparkles class="size-3.5" />
                    社員スキル・評価管理システム
                </span>
                <h1 class="mt-6 text-4xl font-bold tracking-tight md:text-5xl">
                    社員の
                    <span
                        class="bg-gradient-to-r from-orange-500 to-amber-400 bg-clip-text text-transparent"
                        >スキル</span
                    >と
                    <span
                        class="bg-gradient-to-r from-orange-500 to-amber-400 bg-clip-text text-transparent"
                        >評価</span
                    >を、<br class="hidden sm:inline" />ひとつの場所で。
                </h1>
                <p
                    class="mx-auto mt-5 max-w-2xl text-base text-muted-foreground md:text-lg"
                >
                    誰がどのスキルをどのレベルで持っているか、今期の評価はどうだったか。
                    Hinata
                    はチームの「いま」を日向に出して、育成と配置の判断を助けます。
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <Button v-if="$page.props.auth.user" size="lg" as-child>
                        <Link :href="dashboard()">
                            ダッシュボードへ
                            <ArrowRight />
                        </Link>
                    </Button>
                    <template v-else>
                        <Button size="lg" as-child>
                            <Link :href="login()">
                                ログインして始める
                                <ArrowRight />
                            </Link>
                        </Button>
                        <Button size="lg" variant="outline" as-child>
                            <Link :href="register()">アカウントを作成</Link>
                        </Button>
                    </template>
                </div>
            </section>

            <section class="grid gap-4 pb-16 sm:grid-cols-2">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-xl border bg-card p-6 transition hover:border-primary/50 hover:shadow-sm"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary"
                    >
                        <component :is="feature.icon" class="size-5" />
                    </span>
                    <h2 class="mt-4 font-semibold">{{ feature.title }}</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ feature.text }}
                    </p>
                </div>
            </section>

            <section class="pb-16 text-center">
                <h2 class="text-sm font-medium text-muted-foreground">
                    使用技術
                </h2>
                <ul class="mt-3 flex flex-wrap justify-center gap-2">
                    <li
                        v-for="tech in stack"
                        :key="tech"
                        class="rounded-full border px-3 py-1 text-xs"
                    >
                        {{ tech }}
                    </li>
                </ul>
            </section>
        </main>

        <footer class="border-t">
            <div
                class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-2 px-6 py-5 text-sm text-muted-foreground"
            >
                <span>Hinata — 社員スキル・評価管理システム</span>
                <a
                    href="https://github.com/DevMonkey3/skill-eval-app"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 hover:text-foreground"
                >
                    <FolderGit2 class="size-4" />
                    GitHub
                </a>
            </div>
        </footer>
    </div>
</template>
