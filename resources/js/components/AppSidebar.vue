<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    FolderGit2,
    LayoutGrid,
    Sparkles,
    Users,
} from '@lucide/vue';
import EmployeeController from '@/actions/App/Http/Controllers/EmployeeController';
import EvaluationController from '@/actions/App/Http/Controllers/EvaluationController';
import SkillController from '@/actions/App/Http/Controllers/SkillController';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'ダッシュボード',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: '社員',
        href: EmployeeController.index(),
        icon: Users,
    },
    {
        title: 'スキル',
        href: SkillController.index(),
        icon: Sparkles,
    },
    {
        title: '評価',
        href: EvaluationController.index(),
        icon: ClipboardCheck,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'GitHub',
        href: 'https://github.com/DevMonkey3/skill-eval-app',
        icon: FolderGit2,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
