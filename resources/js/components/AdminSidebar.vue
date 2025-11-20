<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
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
import admin from '@/routes/admin';
import type { NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    CalendarClock,
    LayoutDashboard,
    Layers3,
    Megaphone,
    ScrollText,
    Users,
} from 'lucide-vue-next';

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: admin.dashboard(),
        icon: LayoutDashboard,
    },
    {
        title: 'Elections',
        href: admin.elections.index(),
        icon: CalendarClock,
    },
    {
        title: 'Positions',
        href: admin.positions.index(),
        icon: Layers3,
    },
    {
        title: 'Candidates',
        href: admin.candidates.index(),
        icon: Megaphone,
    },
    {
        title: 'Voters',
        href: admin.voters.index(),
        icon: Users,
    },
    {
        title: 'Audit Logs',
        href: admin.auditLogs.index(),
        icon: ScrollText,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="admin.dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Administration" :items="adminNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
