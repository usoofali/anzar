<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, CreditCard, Droplet, Factory, Lock, PackageCheck, Plus, Printer, Trash2, TrendingUp, Unlock, Wallet } from '@lucide/vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import PrintableReportHeader from '@/components/ui/PrintableReportHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDate } from '@/lib/utils';
import { toast } from 'vue-sonner';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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
    remaining_nylon_kg: number;
    remaining_packing_pieces: number;
}

interface Props {
    batch: BatchSummary;
    deliveries: any[];
    dailyCollections: any[];
    customerDebts: any[];
    debtPayments: any[];
    leakageReturns: any[];
    batchProductions: any[];
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

const activeTab = ref<'productions' | 'deliveries' | 'collections' | 'debts' | 'leakages'>('productions');

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const triggerPrint = () => {
    window.print();
};

const page = usePage();
const user = page.props.auth?.user;
const isManager = !user?.role || user?.role === 'manager' || user?.role === 'admin';
const isProduction = isManager || user?.role === 'production_staff';

// Dialog Form for daily sub-production run
const isProductionModalOpen = ref(false);
const productionForm = useForm({
    production_date: new Date().toISOString().split('T')[0],
    production_time: 'morning',
    bags_produced: '' as any,
    remarks: '',
});

const openProductionModal = () => {
    productionForm.reset();
    productionForm.production_date = new Date().toISOString().split('T')[0];
    isProductionModalOpen.value = true;
};

const submitProductionForm = () => {
    productionForm.post(`/production-batches/${props.batch.id}/productions`, {
        onSuccess: () => {
            isProductionModalOpen.value = false;
            productionForm.reset();
            toast.success('Production run logged successfully.');
        },
        onError: () => toast.error('Failed to log production run. Please check remaining limits.'),
    });
};

// Delete production run
const deleteProductionRun = (runId: number) => {
    if (confirm('Are you sure you want to delete this production run? This will update the batch aggregates.')) {
        useForm({}).delete(`/production-batches/${props.batch.id}/productions/${runId}`, {
            onSuccess: () => toast.success('Production run deleted successfully.'),
            onError: () => toast.error('Failed to delete production run.'),
        });
    }
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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <Link href="/production-batches" class="w-fit">
                    <Button variant="outline" size="sm" class="gap-1.5 w-full sm:w-auto">
                        <ArrowLeft class="h-4 w-4" /> Back to Batches
                    </Button>
                </Link>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">Batch {{ batch.batch_no }}</h1>
                        <StatusBadge :status="batch.status" />
                    </div>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-0.5">Produced on {{ formatDate(batch.production_date) }} by {{ batch.produced_by }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <Button variant="outline" size="sm" class="gap-1.5 w-full sm:w-auto justify-center" @click="triggerPrint">
                    <Printer class="h-4 w-4" /> Print / Export PDF
                </Button>
                <Button
                    v-if="batch.status === 'active' && isProduction"
                    class="gap-1.5 bg-blue-600 hover:bg-blue-700 w-full sm:w-auto justify-center"
                    size="sm"
                    @click="openProductionModal"
                >
                    <Plus class="h-4 w-4" /> Record Production Run
                </Button>
            </div>
        </div>

        <!-- Key Performance Metrics Grid -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Raw Material Capacity</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-foreground">{{ batch.bags_produced.toLocaleString() }} Bags</div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Left: {{ batch.remaining_nylon_kg }} KG / {{ batch.remaining_packing_pieces }} Bags
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Distribution & Stock</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ batch.bags_delivered.toLocaleString() }} Delivered</div>
                    <p class="text-xs text-muted-foreground mt-1">{{ batch.remaining_stock }} Bags Remaining Stock</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Batch Collections</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ formatMoney(batch.total_collected) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Cash: {{ formatMoney(batch.cash_collected) }} | Transfer: {{ formatMoney(batch.transfer_collected) }}</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-xs font-semibold uppercase text-muted-foreground">Outstanding Credit & Quality</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xl font-bold text-amber-600 dark:text-amber-400">{{ formatMoney(batch.outstanding_credit) }}</div>
                    <p class="text-xs text-muted-foreground mt-1">Leakages: {{ batch.returned_pieces }} Pcs ({{ batch.replacement_issued }} Replaced)</p>
                </CardContent>
            </Card>
        </div>

        <!-- Tab Navigation & Transaction Tables -->
        <Card>
            <CardHeader class="border-b pb-3 print:hidden px-4 sm:px-6">
                <div class="flex items-center gap-2 overflow-x-auto pb-1 whitespace-nowrap">
                    <button
                        class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-colors whitespace-nowrap shrink-0"
                        :class="activeTab === 'productions' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-muted/40 text-muted-foreground hover:text-foreground hover:bg-muted'"
                        @click="activeTab = 'productions'"
                    >
                        Production Runs ({{ batchProductions.length }})
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-colors whitespace-nowrap shrink-0"
                        :class="activeTab === 'deliveries' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-muted/40 text-muted-foreground hover:text-foreground hover:bg-muted'"
                        @click="activeTab = 'deliveries'"
                    >
                        Deliveries ({{ deliveries.length }})
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-colors whitespace-nowrap shrink-0"
                        :class="activeTab === 'collections' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-muted/40 text-muted-foreground hover:text-foreground hover:bg-muted'"
                        @click="activeTab = 'collections'"
                    >
                        Daily Collections ({{ dailyCollections.length }})
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-colors whitespace-nowrap shrink-0"
                        :class="activeTab === 'debts' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-muted/40 text-muted-foreground hover:text-foreground hover:bg-muted'"
                        @click="activeTab = 'debts'"
                    >
                        Outstanding Debts ({{ customerDebts.length }})
                    </button>
                    <button
                        class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-colors whitespace-nowrap shrink-0"
                        :class="activeTab === 'leakages' ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'bg-muted/40 text-muted-foreground hover:text-foreground hover:bg-muted'"
                        @click="activeTab = 'leakages'"
                    >
                        Leakage Returns ({{ leakageReturns.length }})
                    </button>
                </div>
            </CardHeader>

            <CardContent class="pt-4">
                <!-- Production Runs Tab -->
                <div v-if="activeTab === 'productions'">
                    <div v-if="batchProductions.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No production runs logged for this batch yet.
                    </div>
                    <div v-else class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Production Date</th>
                                    <th class="px-4 py-3">Shift</th>
                                    <th class="px-4 py-3 text-right">Nylon Used (KG)</th>
                                    <th class="px-4 py-3 text-right">Packing Nylon Used</th>
                                    <th class="px-4 py-3 text-right">Bags Produced</th>
                                    <th class="px-4 py-3">Remarks</th>
                                    <th class="px-4 py-3">Recorded By</th>
                                    <th v-if="batch.status === 'active' && isManager" class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="p in batchProductions" :key="p.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 font-semibold text-foreground whitespace-nowrap">{{ formatDate(p.production_date) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap capitalize text-muted-foreground">{{ p.production_time }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-blue-600 dark:text-blue-400">{{ p.nylon_used_kg }} KG</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap text-purple-600 dark:text-purple-400">{{ p.packing_nylon_used }} Pcs</td>
                                    <td class="px-4 py-3 text-right font-bold text-foreground whitespace-nowrap">{{ p.bags_produced }} Bags</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ p.remarks || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ p.produced_by_name }}</td>
                                    <td v-if="batch.status === 'active' && isManager" class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button
                                            v-if="batchProductions.length > 1"
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700"
                                            @click="deleteProductionRun(p.id)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                        <span v-else class="text-xs text-muted-foreground italic">Required initial run</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

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

        <!-- Record Production Run Dialog -->
        <Dialog :open="isProductionModalOpen" @update:open="isProductionModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Daily Production Run</DialogTitle>
                    <DialogDescription>Log a new production run using remaining nylon and outer bags capacity.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitProductionForm" class="space-y-4 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="run_production_date">Production Date</Label>
                            <Input id="run_production_date" type="date" v-model="productionForm.production_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="run_production_time">Production Shift</Label>
                            <select
                                id="run_production_time"
                                v-model="productionForm.production_time"
                                required
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring dark:bg-slate-900 dark:border-slate-800"
                            >
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="run_bags_produced">Bags Produced</Label>
                        <Input
                            id="run_bags_produced"
                            type="number"
                            min="1"
                            :max="batch.remaining_packing_pieces"
                            v-model="productionForm.bags_produced"
                            required
                            :placeholder="`Max: ${batch.remaining_packing_pieces} Bags`"
                        />
                        <p class="text-[10px] text-muted-foreground">Available: {{ batch.remaining_packing_pieces }} Bags</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="run_remarks">Remarks (Optional)</Label>
                        <Input id="run_remarks" v-model="productionForm.remarks" placeholder="e.g. Afternoon shift run" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isProductionModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="productionForm.processing || batch.remaining_packing_pieces <= 0">
                            Log Production Run
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
