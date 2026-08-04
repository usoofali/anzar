<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Boxes,
    CreditCard,
    Droplet,
    Factory,
    Grid,
    PackageCheck,
    Receipt,
    Store,
    Users,
    Wallet,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';

const page = usePage();
const user = page.props.auth?.user;
const isManager = !user?.role || user?.role === 'manager';
const isProduction = isManager || user?.role === 'production_staff';
const isSales = isManager || user?.role === 'sales_staff';

const { isCurrentUrl } = useCurrentUrl();

const navGroups = [
    {
        label: 'Main',
        show: true,
        items: [
            { title: 'Dashboard', href: '/dashboard', icon: Grid },
        ],
    },
    {
        label: 'Production',
        show: isProduction,
        items: [
            { title: 'Nylon Purchases', href: '/raw-materials', icon: Boxes },
            { title: 'Production Batches', href: '/production-batches', icon: Factory },
        ],
    },
    {
        label: 'Distribution & Sales',
        show: isSales,
        items: [
            { title: 'Shops / Customers', href: '/customers', icon: Store },
            { title: 'Deliveries', href: '/deliveries', icon: PackageCheck },
            { title: 'Daily Collections', href: '/daily-collections', icon: Wallet },
            { title: 'Outstanding Debts', href: '/customer-debts', icon: CreditCard },
            { title: 'Leakage Returns', href: '/leakage-returns', icon: Droplet },
        ],
    },
    {
        label: 'Finance',
        show: true,
        items: [
            { title: 'Expenses', href: '/expenses', icon: Receipt },
        ],
    },
    {
        label: 'Management',
        show: true,
        items: [
            { title: 'Reports & Analytics', href: '/reports', icon: BarChart3 },
            ...(isManager ? [{ title: 'User Management', href: '/users', icon: Users }] : []),
        ],
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="space-y-1">
            <template v-for="group in navGroups" :key="group.label">
                <SidebarGroup v-if="group.show" class="px-2 py-1">
                    <SidebarGroupLabel>{{ group.label }}</SidebarGroupLabel>
                    <SidebarMenu>
                        <SidebarMenuItem v-for="item in group.items" :key="item.title">
                            <SidebarMenuButton
                                as-child
                                :is-active="isCurrentUrl(item.href)"
                                :tooltip="item.title"
                            >
                                <Link :href="item.href">
                                    <component :is="item.icon" />
                                    <span>{{ item.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>
                </SidebarGroup>
            </template>
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
