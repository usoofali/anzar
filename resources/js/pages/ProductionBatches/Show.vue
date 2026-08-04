<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, CreditCard, Droplet, Factory, Lock, PackageCheck, Printer, TrendingUp, Unlock, Wallet } from '@lucide/vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import PrintableReportHeader from '@/components/ui/PrintableReportHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/utils';

interface BatchSummary {
    id: number;
    batch_no: string;
    production_date: string;
    raw_material_supplier: string;
    raw_material_purchase_no: string;
    quantity_used_kg: number;
    unit_price_per_kg: number;
    nylon_cost: number;
    cost_per_bag: number;
    bags_produced: number;
    bags_delivered: number;
    remaining_stock: number;
    produced_by: string;
    status: string;
    expected_revenue: number;
    cash_collected: number;
    transfer_collected: number;
    total_collected: number;
    outstanding_credit: number;
    returned_pieces: number;
    replacement_issued: number;
    gross_profit: number;
    realized_cash_profit: number;
    profit_margin_percent: number;
}

interface Props {
    batch: BatchSummary;
    deliveries: any[];
    dailyCollections: any[];
    customerDebts: any[];
    debtPayments: any[];
    leakageReturns: any[];
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Production Batches', href: '/production-batches' },
            { title: 'Batch Summary', href: '/production-batches' },
        ],
    },
});

const activeTab = ref<'deliveries' | 'collections' | 'debts' | 'leakages'>('deliveries');

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="`Batch ${batch.batch_no} - Lifecycle Performance`" />

    <div class="space-y-6 p-6">
        <!-- Printable Header for PDF / Print Mode -->
        <div class="hidden print:block">
            <PrintableReportHeader
                :title="`BATCH PERFORMANCE REPORT: ${batch.batch_no}`"
                :generatedAt="new Date().toLocaleString()"
                generatedBy="Manager"
            />
        </div>

        <!-- Page Action Bar -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div class="flex items-center gap-3">
                <Link href="/production-batches">
                    <Button variant="outline" size="sm" class="gap-1">
                        <ArrowLeft class="h-4 w-4" /> Back to Batches
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">Batch {{ batch.batch_no }}</h1>
                        <StatusBadge :status="batch.status" />
                    </div>
                    <p class="text-sm text-muted-foreground">Produced on {{ formatDate(batch.production_date) }} by {{ batch.produced_by }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="gap-1.5" @click="triggerPrint">
                    <Printer class="h-4 w-4" /> Print / Export PDF
                </Button>
            </div>
        </div>

        <!-- Key Performance Metrics Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Raw Material & Production</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-foreground">{{ batch.bags_produced.toLocaleString() }} Bags</div>
                    <p class="text-xs text-muted-foreground mt-1">From {{ batch.quantity_used_kg }} KG Nylon</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Distribution & Stock</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-emerald-600">{{ batch.bags_delivered.toLocaleString() }} Delivered</div>
                    <p class="text-xs text-muted-foreground mt-1">{{ batch.remaining_stock }} Bags Remaining Stock</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Batch Collections</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-blue-600">{{ formatMoney(batch.total_collected) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Cash: {{ formatMoney(batch.cash_collected) }} | Transfer: {{ formatMoney(batch.transfer_collected) }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Outstanding Credit & Quality</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-amber-600">{{ formatMoney(batch.outstanding_credit) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Leakages: {{ batch.returned_pieces }} Pcs ({{ batch.replacement_issued }} Replaced)</p>
                </CardContent>
            </Card>
        </div>

        <!-- Tab Navigation & Transaction Tables -->
        <Card>
            <CardHeader class="border-b pb-3 print:hidden">
                <div class="flex items-center gap-4">
                    <button
                        class="pb-2 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'deliveries' ? 'border-blue-600 text-blue-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'deliveries'"
                    >
                        Deliveries ({{ deliveries.length }})
                    </button>
                    <button
                        class="pb-2 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'collections' ? 'border-blue-600 text-blue-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'collections'"
                    >
                        Daily Collections ({{ dailyCollections.length }})
                    </button>
                    <button
                        class="pb-2 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'debts' ? 'border-blue-600 text-blue-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'debts'"
                    >
                        Outstanding Debts ({{ customerDebts.length }})
                    </button>
                    <button
                        class="pb-2 text-sm font-semibold border-b-2 transition-colors"
                        :class="activeTab === 'leakages' ? 'border-blue-600 text-blue-600' : 'border-transparent text-muted-foreground hover:text-foreground'"
                        @click="activeTab = 'leakages'"
                    >
                        Leakage Returns ({{ leakageReturns.length }})
                    </button>
                </div>
            </CardHeader>

            <CardContent class="pt-4">
                <!-- Deliveries Tab -->
                <div v-if="activeTab === 'deliveries'">
                    <div v-if="deliveries.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No deliveries recorded for this batch yet.
                    </div>
                    <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Delivery No</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Customer Shop</th>
                                    <th class="px-4 py-3 text-right">Bags</th>
                                    <th class="px-4 py-3 text-right">Unit Price</th>
                                    <th class="px-4 py-3 text-right">Total Amount</th>
                                    <th class="px-4 py-3 text-right">Upfront Paid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="d in deliveries" :key="d.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold whitespace-nowrap">{{ d.delivery_no }}</td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(d.delivery_date) }}</td>
                                    <td class="px-4 py-3 font-medium text-foreground whitespace-nowrap">{{ d.customer?.shop_name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">{{ d.bags_delivered }}</td>
                                    <td class="px-4 py-3 text-right text-muted-foreground whitespace-nowrap">{{ formatMoney(d.unit_price) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-foreground whitespace-nowrap">{{ formatMoney(d.total_amount) }}</td>
                                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-semibold whitespace-nowrap">{{ formatMoney(d.paid_amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daily Collections Tab -->
                <div v-if="activeTab === 'collections'">
                    <div v-if="dailyCollections.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No physical cash or transfer collections logged for this batch yet.
                    </div>
                    <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[600px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3 text-right">Cash Amount</th>
                                    <th class="px-4 py-3 text-right">Transfer Amount</th>
                                    <th class="px-4 py-3 text-right">Total Collection</th>
                                    <th class="px-4 py-3">Recorded By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="c in dailyCollections" :key="c.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(c.collection_date) }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ formatMoney(c.cash_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ formatMoney(c.transfer_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ formatMoney((parseFloat(c.cash_amount) || 0) + (parseFloat(c.transfer_amount) || 0)) }}</td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ c.recorded_by?.name || 'Staff' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Outstanding Debts Tab -->
                <div v-if="activeTab === 'debts'">
                    <div v-if="customerDebts.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No customer debts recorded for this batch.
                    </div>
                    <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[500px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Customer Shop</th>
                                    <th class="px-4 py-3 text-right">Outstanding Amount</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="cd in customerDebts" :key="cd.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 font-semibold text-foreground whitespace-nowrap">{{ cd.customer?.shop_name }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">{{ formatMoney(cd.outstanding_amount) }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <StatusBadge :status="cd.status" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Leakage Returns Tab -->
                <div v-if="activeTab === 'leakages'">
                    <div v-if="leakageReturns.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No leakage returns recorded for this batch.
                    </div>
                    <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[600px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3 text-right">Returned (Pieces)</th>
                                    <th class="px-4 py-3 text-right">Replacement Issued</th>
                                    <th class="px-4 py-3">Remarks</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="lr in leakageReturns" :key="lr.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(lr.date) }}</td>
                                    <td class="px-4 py-3 font-medium text-foreground whitespace-nowrap">{{ lr.customer?.shop_name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-rose-600 dark:text-rose-400 whitespace-nowrap">{{ lr.returned_pieces }} Pcs</td>
                                    <td class="px-4 py-3 text-right font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ lr.replacement_issued }} Pcs</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">{{ lr.remarks || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Batch Financial Profit & Loss Summary Card -->
        <Card class="border-2 border-border/80 shadow-sm">
            <CardHeader class="pb-3 border-b bg-muted/20">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                            <TrendingUp class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle class="text-base font-bold text-foreground">Batch Financial Profit & Loss Summary</CardTitle>
                            <p class="text-xs text-muted-foreground">Comprehensive profitability analysis comparing nylon material cost against sales revenue.</p>
                        </div>
                    </div>
                    <div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                            :class="batch.net_profit_after_leakage >= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300/40' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300/40'"
                        >
                            {{ batch.net_profit_after_leakage >= 0 ? 'Net Profit:' : 'Net Loss:' }} {{ formatMoney(batch.net_profit_after_leakage) }} ({{ batch.profit_margin_percent }}%)
                        </span>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="pt-4">
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Column 1: Gross Sales & Revenue -->
                    <div class="space-y-3 p-3 rounded-lg bg-muted/30 border border-border/40">
                        <div class="flex items-center justify-between border-b pb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Revenue Inflows</span>
                            <span class="text-xs font-mono text-blue-600 dark:text-blue-400 font-semibold">{{ batch.bags_delivered }} Bags Delivered</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Gross Sales Value:</span>
                                <span class="font-bold text-foreground">{{ formatMoney(batch.expected_revenue) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-emerald-600 dark:text-emerald-400">Realized Collections:</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">+{{ formatMoney(batch.total_collected) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-amber-600 dark:text-amber-400">Outstanding Shop Credit:</span>
                                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ formatMoney(batch.outstanding_credit) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Direct Material Costs & Leakage Value -->
                    <div class="space-y-3 p-3 rounded-lg bg-muted/30 border border-border/40">
                        <div class="flex items-center justify-between border-b pb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Material Cost & Quality Loss</span>
                            <span class="text-xs font-mono text-purple-600 dark:text-purple-400 font-semibold">{{ batch.quantity_used_kg }} KG Nylon</span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Nylon Purchase Cost:</span>
                                <span class="font-bold text-foreground">{{ formatMoney(batch.nylon_cost) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-muted-foreground">Material Cost / Bag:</span>
                                <span class="font-medium text-foreground">{{ formatMoney(batch.cost_per_bag) }} / bag</span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-rose-600 dark:text-rose-400">Leakage Loss ({{ batch.returned_pieces }} Pcs):</span>
                                <span class="font-semibold text-rose-600 dark:text-rose-400">-{{ formatMoney(batch.leakage_loss_value) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Column 3: Profit Margins & Cash Flow -->
                    <div class="space-y-3 p-3 rounded-lg bg-muted/30 border border-border/40">
                        <div class="flex items-center justify-between border-b pb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Net Margin & Return</span>
                            <span class="text-xs font-semibold" :class="batch.profit_margin_percent >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600'">
                                {{ batch.profit_margin_percent }}% Margin
                            </span>
                        </div>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium">Gross Profit:</span>
                                <span class="font-bold text-foreground">{{ formatMoney(batch.gross_profit) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium">Net Profit (after leakages):</span>
                                <span class="font-extrabold text-base" :class="batch.net_profit_after_leakage >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600'">
                                    {{ formatMoney(batch.net_profit_after_leakage) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-muted-foreground">Realized Cash Profit:</span>
                                <span class="font-bold" :class="batch.realized_cash_profit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600'">
                                    {{ formatMoney(batch.realized_cash_profit) }}
                                </span>
                            </div>
                            <!-- Profit Margin Bar -->
                            <div class="w-full bg-muted rounded-full h-2 overflow-hidden mt-1">
                                <div
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="batch.net_profit_after_leakage >= 0 ? 'bg-emerald-500' : 'bg-rose-500'"
                                    :style="{ width: `${Math.min(Math.max(batch.profit_margin_percent, 0), 100)}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
