<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Boxes, CreditCard, Droplet, Factory, Lock, PackageCheck, Printer, Unlock, Wallet } from '@lucide/vue';
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
    quantity_used_kg: number;
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
                    <table v-else class="w-full text-left text-sm">
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
                        <tbody class="divide-y">
                            <tr v-for="d in deliveries" :key="d.id">
                                <td class="px-4 py-3 font-mono text-xs font-semibold">{{ d.delivery_no }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(d.delivery_date) }}</td>
                                <td class="px-4 py-3 font-medium">{{ d.customer?.shop_name }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ d.bags_delivered }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ formatMoney(d.unit_price) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-foreground">{{ formatMoney(d.total_amount) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600 font-semibold">{{ formatMoney(d.paid_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Daily Collections Tab -->
                <div v-if="activeTab === 'collections'">
                    <div v-if="dailyCollections.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No physical cash or transfer collections logged for this batch yet.
                    </div>
                    <table v-else class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3 text-right">Cash Amount</th>
                                <th class="px-4 py-3 text-right">Transfer Amount</th>
                                <th class="px-4 py-3 text-right">Total Collection</th>
                                <th class="px-4 py-3">Recorded By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="c in dailyCollections" :key="c.id">
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(c.collection_date) }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatMoney(c.cash_amount) }}</td>
                                <td class="px-4 py-3 text-right font-medium">{{ formatMoney(c.transfer_amount) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">{{ formatMoney((parseFloat(c.cash_amount) || 0) + (parseFloat(c.transfer_amount) || 0)) }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ c.recorded_by?.name || 'Staff' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Outstanding Debts Tab -->
                <div v-if="activeTab === 'debts'">
                    <div v-if="customerDebts.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No customer debts recorded for this batch.
                    </div>
                    <table v-else class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Customer Shop</th>
                                <th class="px-4 py-3 text-right">Outstanding Amount</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="cd in customerDebts" :key="cd.id">
                                <td class="px-4 py-3 font-semibold">{{ cd.customer?.shop_name }}</td>
                                <td class="px-4 py-3 text-right font-bold text-amber-600">{{ formatMoney(cd.outstanding_amount) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <StatusBadge :status="cd.status" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Leakage Returns Tab -->
                <div v-if="activeTab === 'leakages'">
                    <div v-if="leakageReturns.length === 0" class="text-center py-6 text-sm text-muted-foreground">
                        No leakage returns recorded for this batch.
                    </div>
                    <table v-else class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3 text-right">Returned (Pieces)</th>
                                <th class="px-4 py-3 text-right">Replacement Issued</th>
                                <th class="px-4 py-3">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="lr in leakageReturns" :key="lr.id">
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(lr.date) }}</td>
                                <td class="px-4 py-3 font-medium">{{ lr.customer?.shop_name }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">{{ lr.returned_pieces }} Pcs</td>
                                <td class="px-4 py-3 text-right font-semibold text-blue-600">{{ lr.replacement_issued }} Pcs</td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">{{ lr.remarks || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
