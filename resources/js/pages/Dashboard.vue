<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    ArrowUpRight,
    Boxes,
    CreditCard,
    Droplet,
    Factory,
    PackageCheck,
    Receipt,
    ShieldCheck,
    Store,
    TrendingUp,
    Users,
    Wallet,
} from '@lucide/vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/utils';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Dashboard', href: '/dashboard' }],
    },
});

interface Metrics {
    active_batches: number;
    bags_produced_today: number;
    bags_produced_month: number;
    bags_delivered_today: number;
    daily_cash_today: number;
    daily_transfer_today: number;
    outstanding_credit: number;
    customers_owing_count: number;
    leakage_pieces_today: number;
    leakage_pieces_month: number;
    expenses_today: number;
    expenses_month: number;
}

interface Props {
    metrics: Metrics;
    recentDeliveries: any[];
    customersOwing: any[];
    activeBatches: any[];
    recentPayments: any[];
    recentLeakageReturns: any[];
}

const props = defineProps<Props>();

import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth?.user;
const isAdmin = user?.role === 'admin';
const isManager = !user?.role || user?.role === 'manager' || isAdmin;
const isProduction = isManager || user?.role === 'production_staff';
const isSales = isManager || user?.role === 'sales_staff';

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Dashboard - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Operational Overview</h1>
                <p class="text-sm text-muted-foreground">Real-time production batch tracking, sales, debt collections, and quality control.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <Link v-if="isProduction" href="/production-batches" class="w-full sm:w-auto">
                    <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700 w-full sm:w-auto">
                        <Factory class="h-4 w-4" /> New Batch
                    </Button>
                </Link>
                <Link v-if="isSales" href="/deliveries" class="w-full sm:w-auto">
                    <Button variant="outline" class="gap-1.5 w-full sm:w-auto">
                        <PackageCheck class="h-4 w-4" /> Record Delivery
                    </Button>
                </Link>
            </div>
        </div>

        <!-- Admin Workspace Banner -->
        <Card v-if="isAdmin" class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white border-none shadow-md">
            <CardContent class="p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-lg bg-cyan-500/20 text-cyan-400">
                        <ShieldCheck class="h-6 w-6" />
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-100">System Overseer Workspace</h3>
                        <p class="text-xs text-slate-300">You have administrative access to system analytics, security roles, and user management.</p>
                    </div>
                </div>
                <Link href="/users" class="w-full sm:w-auto">
                    <Button size="sm" class="bg-cyan-500 hover:bg-cyan-600 text-slate-950 font-semibold gap-1.5 w-full sm:w-auto whitespace-nowrap">
                        <Users class="h-4 w-4" /> Manage System Users
                    </Button>
                </Link>
            </CardContent>
        </Card>

        <!-- Top Metric Cards Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Production Card -->
            <Card v-if="isProduction" class="relative overflow-hidden border-emerald-500/20 bg-gradient-to-br from-emerald-500/5 to-transparent">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        Today's Production
                    </CardTitle>
                    <div class="rounded-lg bg-emerald-500/10 p-2 text-emerald-600">
                        <Factory class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ metrics.bags_produced_today.toLocaleString() }} <span class="text-xs font-normal text-muted-foreground">Bags</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                        <span>{{ metrics.active_batches }} Active Batches</span>
                        <span>{{ metrics.bags_produced_month.toLocaleString() }} This Month</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Daily Collections Card -->
            <Card v-if="isSales" class="relative overflow-hidden border-blue-500/20 bg-gradient-to-br from-blue-500/5 to-transparent">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-semibold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        Today's Collections
                    </CardTitle>
                    <div class="rounded-lg bg-blue-500/10 p-2 text-blue-600">
                        <Wallet class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ formatMoney(metrics.daily_cash_today + metrics.daily_transfer_today) }}
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                        <span>Cash: {{ formatMoney(metrics.daily_cash_today) }}</span>
                        <span>Transfer: {{ formatMoney(metrics.daily_transfer_today) }}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Receivables Card -->
            <Card v-if="isSales" class="relative overflow-hidden border-amber-500/20 bg-gradient-to-br from-amber-500/5 to-transparent">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-semibold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                        Outstanding Credit
                    </CardTitle>
                    <div class="rounded-lg bg-amber-500/10 p-2 text-amber-600">
                        <CreditCard class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ formatMoney(metrics.outstanding_credit) }}
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                        <span>{{ metrics.customers_owing_count }} Shops Owing</span>
                        <Link href="/customer-debts" class="inline-flex items-center text-amber-600 hover:underline">
                            View Debts <ArrowUpRight class="h-3 w-3 ml-0.5" />
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <!-- Expenses Card -->
            <Card class="relative overflow-hidden border-purple-500/20 bg-gradient-to-br from-purple-500/5 to-transparent">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-xs font-semibold uppercase tracking-wider text-purple-600 dark:text-purple-400">
                        Today's Expenses
                    </CardTitle>
                    <div class="rounded-lg bg-purple-500/10 p-2 text-purple-600">
                        <Receipt class="h-4 w-4" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-foreground">
                        {{ formatMoney(metrics.expenses_today) }}
                    </div>
                    <div class="mt-2 flex items-center justify-between text-xs text-muted-foreground">
                        <span>Month: {{ formatMoney(metrics.expenses_month) }}</span>
                        <span>Leakages: {{ metrics.leakage_pieces_today }} Pcs</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Lower Layout Grid -->
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Activity Column (2 cols) -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Active Batches Summary -->
                <Card v-if="isProduction">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-base">Active Production Batches</CardTitle>
                            <CardDescription>Batches currently undergoing delivery and debt collection</CardDescription>
                        </div>
                        <Link href="/production-batches">
                            <Button variant="ghost" size="sm" class="gap-1 text-xs">
                                View All <ArrowUpRight class="h-3.5 w-3.5" />
                            </Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div v-if="activeBatches.length === 0">
                            <EmptyState
                                title="No Active Batches"
                                description="Create a new production batch to start tracking deliveries and revenue."
                                actionText="Create Batch"
                                @action="$inertia.visit('/production-batches')"
                            />
                        </div>
                        <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                            <table class="w-full min-w-[600px] text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-4 py-3">Batch No</th>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3 text-right">Produced</th>
                                        <th class="px-4 py-3 text-right">Delivered</th>
                                        <th class="px-4 py-3 text-right">Stock</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-border/40">
                                    <tr v-for="batch in activeBatches" :key="batch.id" class="hover:bg-muted/30">
                                        <td class="px-4 py-3 font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                            <Link :href="`/production-batches/${batch.id}`" class="hover:underline">
                                                {{ batch.batch_no }}
                                            </Link>
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(batch.production_date) }}</td>
                                        <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ batch.bags_produced }}</td>
                                        <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-medium whitespace-nowrap">{{ batch.bags_delivered }}</td>
                                        <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">{{ batch.remaining_stock }}</td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <StatusBadge :status="batch.status" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Deliveries Table -->
                <Card v-if="isSales">
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-base">Recent Deliveries</CardTitle>
                            <CardDescription>Latest shop deliveries and distribution logs</CardDescription>
                        </div>
                        <Link href="/deliveries">
                            <Button variant="ghost" size="sm" class="gap-1 text-xs">
                                View All <ArrowUpRight class="h-3.5 w-3.5" />
                            </Button>
                        </Link>
                    </CardHeader>
                    <CardContent>
                        <div v-if="recentDeliveries.length === 0">
                            <EmptyState title="No Recent Deliveries" description="Record deliveries when products leave the factory." />
                        </div>
                        <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                            <table class="w-full min-w-[600px] text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-4 py-3">Delivery No</th>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="px-4 py-3">Batch</th>
                                        <th class="px-4 py-3 text-right">Bags</th>
                                        <th class="px-4 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="delivery in recentDeliveries" :key="delivery.id" class="hover:bg-muted/30">
                                        <td class="px-4 py-3 font-mono text-xs font-medium">{{ delivery.delivery_no }}</td>
                                        <td class="px-4 py-3 font-medium text-foreground">{{ delivery.customer?.shop_name }}</td>
                                        <td class="px-4 py-3 text-xs text-muted-foreground">{{ delivery.batch?.batch_no }}</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ delivery.bags_delivered }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ formatMoney(delivery.total_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Side Widget Column (1 col) -->
            <div v-if="isSales" class="space-y-6">
                <!-- Customers Owing Widget -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between pb-3">
                        <CardTitle class="text-base">Customers Owing</CardTitle>
                        <Link href="/customer-debts">
                            <Button variant="ghost" size="sm" class="h-8 text-xs">View All</Button>
                        </Link>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="customersOwing.length === 0" class="text-center py-4 text-sm text-muted-foreground">
                            🎉 All customer accounts settled!
                        </div>
                        <div v-for="debt in customersOwing" :key="debt.id" class="flex items-center justify-between p-3 rounded-lg border bg-muted/20">
                            <div>
                                <p class="font-semibold text-sm text-foreground">{{ debt.customer?.shop_name }}</p>
                                <p class="text-xs text-muted-foreground">Batch {{ debt.batch?.batch_no }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm text-amber-600 dark:text-amber-400">{{ formatMoney(debt.outstanding_amount) }}</p>
                                <StatusBadge status="open" label="Open" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Payments Widget -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base">Recent Debt Repayments</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div v-if="recentPayments.length === 0" class="text-center py-4 text-sm text-muted-foreground">
                            No repayments logged today.
                        </div>
                        <div v-for="pay in recentPayments" :key="pay.id" class="flex items-center justify-between border-b pb-2">
                            <div>
                                <p class="font-medium text-xs text-foreground">{{ pay.customer?.shop_name }}</p>
                                <p class="text-[11px] text-muted-foreground uppercase">{{ pay.payment_method }} • {{ formatDate(pay.payment_date) }}</p>
                            </div>
                            <span class="font-semibold text-sm text-emerald-600">+{{ formatMoney(pay.amount) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
