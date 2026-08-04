<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    BarChart3,
    Boxes,
    CreditCard,
    Download,
    Droplet,
    Factory,
    PackageCheck,
    Printer,
    Receipt,
    Store,
    Wallet,
} from '@lucide/vue';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import PrintableReportHeader from '@/components/ui/PrintableReportHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatDate } from '@/lib/utils';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Reports & Analytics', href: '/reports' }],
    },
});

interface Props {
    reportType: string;
    startDate: string;
    endDate: string;
    selectedCustomerId: string | number | null;
    selectedBatchId: string | number | null;
    reportData: any;
    customers: any[];
    batches: any[];
}

const props = defineProps<Props>();

const currentType = computed(() => props.reportType || 'batch_performance');

const start_date = ref(props.startDate || '');
const end_date = ref(props.endDate || '');
const customer_id = ref(props.selectedCustomerId || '');
const batch_id = ref(props.selectedBatchId || '');

watch(() => props.startDate, (val) => { start_date.value = val || ''; });
watch(() => props.endDate, (val) => { end_date.value = val || ''; });
watch(() => props.selectedCustomerId, (val) => { customer_id.value = val || ''; });
watch(() => props.selectedBatchId, (val) => { batch_id.value = val || ''; });

const reportTypes = [
    { id: 'batch_performance', name: '1. Batch Performance Traceability Report', icon: Factory },
    { id: 'production', name: '2. Production Summary Report', icon: Boxes },
    { id: 'delivery', name: '3. Distribution & Deliveries Log', icon: PackageCheck },
    { id: 'daily_collection', name: '4. Daily Cash & Bank Collections', icon: Wallet },
    { id: 'outstanding_customers', name: '5. Outstanding Customers Credit Log', icon: CreditCard },
    { id: 'leakage', name: '6. Leakage & Quality Returns Report', icon: Droplet },
    { id: 'expense', name: '7. Operational Expenses Summary', icon: Receipt },
    { id: 'customer_statement', name: '8. Customer Account Statement', icon: Store },
];

const generateReport = () => {
    router.get('/reports', {
        type: currentType.value,
        start_date: start_date.value,
        end_date: end_date.value,
        customer_id: customer_id.value,
        batch_id: batch_id.value,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

const selectReportType = (type: string) => {
    start_date.value = '';
    end_date.value = '';
    customer_id.value = '';
    batch_id.value = '';
    router.get('/reports', {
        type: type,
    }, { preserveState: true, preserveScroll: true, replace: true });
};

const triggerPrint = () => {
    window.print();
};

const exportCSV = () => {
    window.location.href = `/reports?type=${currentType.value}&start_date=${start_date.value}&end_date=${end_date.value}&customer_id=${customer_id.value}&batch_id=${batch_id.value}&export=csv`;
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Reports & Analytics - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Printable Header for PDF Export -->
        <div class="hidden print:block">
            <PrintableReportHeader
                :title="reportTypes.find(r => r.id === currentType)?.name || 'MANAGEMENT REPORT'"
                :startDate="start_date"
                :endDate="end_date"
                :generatedAt="new Date().toLocaleString()"
                generatedBy="Management Staff"
            />
        </div>

        <!-- Screen Header -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Management Reports & Analytics</h1>
                <p class="text-sm text-muted-foreground">Comprehensive batch lifecycle reports, revenue summaries, and accounting statements.</p>
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" class="gap-1.5" @click="triggerPrint">
                    <Printer class="h-4 w-4" /> Print / Export PDF
                </Button>
                <Button variant="secondary" class="gap-1.5" @click="exportCSV">
                    <Download class="h-4 w-4" /> Export CSV
                </Button>
            </div>
        </div>

        <!-- Report Selector Grid & Date Filters (Screen only) -->
        <div class="grid gap-6 lg:grid-cols-4 print:hidden">
            <!-- Sidebar Selection Menu -->
            <Card class="lg:col-span-1">
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Select Report</CardTitle>
                    <CardDescription>Choose standard report module</CardDescription>
                </CardHeader>
                <CardContent class="p-2 space-y-1">
                    <button
                        v-for="rt in reportTypes"
                        :key="rt.id"
                        class="w-full text-left flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-xs font-medium transition-colors"
                        :class="currentType === rt.id ? 'bg-blue-600 text-white font-semibold shadow-sm' : 'hover:bg-muted text-foreground'"
                        @click="selectReportType(rt.id)"
                    >
                        <component :is="rt.icon" class="h-4 w-4 shrink-0" />
                        <span class="truncate">{{ rt.name }}</span>
                    </button>
                </CardContent>
            </Card>

            <!-- Main Filter & Preview Pane -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Filters Bar -->
                <Card>
                    <CardContent class="pt-4">
                        <form @submit.prevent="generateReport" class="grid gap-4 sm:grid-cols-4 items-end">
                            <div class="space-y-1">
                                <Label for="start_date">Start Date</Label>
                                <Input id="start_date" type="date" v-model="start_date" @change="generateReport" />
                            </div>
                            <div class="space-y-1">
                                <Label for="end_date">End Date</Label>
                                <Input id="end_date" type="date" v-model="end_date" @change="generateReport" />
                            </div>
                            <div v-if="currentType === 'customer_statement'" class="space-y-1 sm:col-span-2">
                                <Label for="customer_id">Select Customer</Label>
                                <select
                                    id="customer_id"
                                    v-model="customer_id"
                                    @change="generateReport"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                                >
                                    <option value="">All Customers</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.shop_name }}</option>
                                </select>
                            </div>
                            <div v-if="currentType === 'batch_performance'" class="space-y-1 sm:col-span-2">
                                <Label for="batch_id">Select Batch</Label>
                                <select
                                    id="batch_id"
                                    v-model="batch_id"
                                    @change="generateReport"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                                >
                                    <option value="">All Batches</option>
                                    <option v-for="b in batches" :key="b.id" :value="b.id">{{ b.batch_no }}</option>
                                </select>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Report Output Preview Area -->
                <Card>
                    <CardHeader class="border-b pb-3">
                        <CardTitle class="text-lg font-bold">
                            {{ reportTypes.find(r => r.id === currentType)?.name }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-4">
                        <!-- 1. Batch Performance Report -->
                        <div v-if="currentType === 'batch_performance' && Array.isArray(reportData)" class="space-y-4">
                            <div class="relative overflow-x-auto">
                                <table class="w-full text-left text-sm">
                                    <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                        <tr>
                                            <th class="px-3 py-2">Batch No</th>
                                            <th class="px-3 py-2">Date</th>
                                            <th class="px-3 py-2 text-right">Produced</th>
                                            <th class="px-3 py-2 text-right">Delivered</th>
                                            <th class="px-3 py-2 text-right">Stock</th>
                                            <th class="px-3 py-2 text-right">Collected (₦)</th>
                                            <th class="px-3 py-2 text-right">Credit (₦)</th>
                                            <th class="px-3 py-2 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="row in reportData" :key="row.id || row.batch_no">
                                            <td class="px-3 py-2 font-semibold text-blue-600">{{ row.batch_no }}</td>
                                            <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.production_date) }}</td>
                                            <td class="px-3 py-2 text-right font-medium">{{ row.bags_produced }}</td>
                                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">{{ row.bags_delivered }}</td>
                                            <td class="px-3 py-2 text-right font-bold">{{ row.remaining_stock }}</td>
                                            <td class="px-3 py-2 text-right font-semibold">{{ formatMoney(row.total_collected) }}</td>
                                            <td class="px-3 py-2 text-right font-semibold text-amber-600">{{ formatMoney(row.outstanding_credit) }}</td>
                                            <td class="px-3 py-2 text-center"><StatusBadge :status="row.status" /></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 2. Production Summary Report -->
                        <div v-else-if="currentType === 'production' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Batch No</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Supplier</th>
                                        <th class="px-3 py-2 text-right">KG Used</th>
                                        <th class="px-3 py-2 text-right">Bags Produced</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 font-semibold">{{ row.batch_no }}</td>
                                        <td class="px-3 py-2 text-muted-foreground">{{ formatDate(row.production_date) }}</td>
                                        <td class="px-3 py-2 text-xs">{{ row.supplier || 'N/A' }}</td>
                                        <td class="px-3 py-2 text-right font-bold">{{ row.nylon_used_kg }} KG</td>
                                        <td class="px-3 py-2 text-right font-bold text-emerald-600">{{ row.bags_produced }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 3. Delivery Log -->
                        <div v-else-if="currentType === 'delivery' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Delivery No</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Customer Shop</th>
                                        <th class="px-3 py-2 text-right">Bags</th>
                                        <th class="px-3 py-2 text-right">Total (₦)</th>
                                        <th class="px-3 py-2 text-right">Paid (₦)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 font-mono text-xs">{{ row.delivery_no }}</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.delivery_date) }}</td>
                                        <td class="px-3 py-2 font-medium">{{ row.shop_name }}</td>
                                        <td class="px-3 py-2 text-right font-bold">{{ row.bags_delivered }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">{{ formatMoney(row.total_amount) }}</td>
                                        <td class="px-3 py-2 text-right text-emerald-600 font-semibold">{{ formatMoney(row.paid_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 4. Daily Collections Report -->
                        <div v-else-if="currentType === 'daily_collection' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Batch</th>
                                        <th class="px-3 py-2 text-right">Cash (₦)</th>
                                        <th class="px-3 py-2 text-right">Transfer (₦)</th>
                                        <th class="px-3 py-2 text-right">Total (₦)</th>
                                        <th class="px-3 py-2">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 text-muted-foreground">{{ formatDate(row.collection_date) }}</td>
                                        <td class="px-3 py-2 font-semibold text-blue-600">{{ row.batch_no }}</td>
                                        <td class="px-3 py-2 text-right font-medium">{{ formatMoney(row.cash_amount) }}</td>
                                        <td class="px-3 py-2 text-right font-medium">{{ formatMoney(row.transfer_amount) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-emerald-600">{{ formatMoney(row.total_collection) }}</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ row.remarks || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 5. Outstanding Customers Credit Log -->
                        <div v-else-if="currentType === 'outstanding_customers' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Customer Shop</th>
                                        <th class="px-3 py-2">Owner & Phone</th>
                                        <th class="px-3 py-2">Batch</th>
                                        <th class="px-3 py-2">Delivery Date</th>
                                        <th class="px-3 py-2 text-right">Outstanding Debt (₦)</th>
                                        <th class="px-3 py-2 text-center">Age</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 font-semibold">{{ row.shop_name }}</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ row.owner_name }} ({{ row.phone }})</td>
                                        <td class="px-3 py-2 text-xs font-semibold text-blue-600">{{ row.batch_no }}</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.delivery_date) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-amber-600">{{ formatMoney(row.outstanding_amount) }}</td>
                                        <td class="px-3 py-2 text-center text-xs font-medium">{{ row.age_days }} Days</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 6. Leakage Returns Report -->
                        <div v-else-if="currentType === 'leakage' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Customer Shop</th>
                                        <th class="px-3 py-2">Batch</th>
                                        <th class="px-3 py-2 text-right">Leaked (Pieces)</th>
                                        <th class="px-3 py-2 text-right">Replacement Issued</th>
                                        <th class="px-3 py-2">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.date) }}</td>
                                        <td class="px-3 py-2 font-semibold">{{ row.shop_name }}</td>
                                        <td class="px-3 py-2 font-medium text-blue-600">{{ row.batch_no }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-rose-600">{{ row.returned_pieces }} Pcs</td>
                                        <td class="px-3 py-2 text-right font-bold text-blue-600">{{ row.replacement_issued }} Pcs</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ row.remarks || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 7. Operational Expenses Summary -->
                        <div v-else-if="currentType === 'expense' && Array.isArray(reportData)" class="space-y-4">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Category</th>
                                        <th class="px-3 py-2">Description</th>
                                        <th class="px-3 py-2 text-right">Amount (₦)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData" :key="idx">
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.expense_date) }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-xs font-semibold text-purple-700 dark:bg-purple-950/50 dark:text-purple-300">
                                                {{ row.category }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 font-medium">{{ row.description }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-rose-600">{{ formatMoney(row.amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 8. Customer Account Statement -->
                        <div v-else-if="currentType === 'customer_statement' && reportData && !Array.isArray(reportData)" class="space-y-4">
                            <div v-if="reportData?.customer" class="p-4 rounded-lg border bg-muted/20 space-y-1">
                                <h3 class="font-bold text-base text-foreground">{{ reportData.customer.shop_name }}</h3>
                                <p class="text-xs text-muted-foreground">{{ reportData.customer.owner_name }} • {{ reportData.customer.phone }} • {{ reportData.customer.address }}</p>
                                <p class="text-sm font-semibold text-amber-600 pt-1">Total Account Debt: {{ formatMoney(reportData.customer.total_outstanding) }}</p>
                            </div>
                            <table v-if="reportData?.deliveries?.length" class="w-full text-left text-sm">
                                <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                    <tr>
                                        <th class="px-3 py-2">Delivery No</th>
                                        <th class="px-3 py-2">Date</th>
                                        <th class="px-3 py-2">Batch</th>
                                        <th class="px-3 py-2 text-right">Bags</th>
                                        <th class="px-3 py-2 text-right">Total (₦)</th>
                                        <th class="px-3 py-2 text-right">Paid (₦)</th>
                                        <th class="px-3 py-2 text-right">Debt Balance (₦)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(row, idx) in reportData.deliveries" :key="idx">
                                        <td class="px-3 py-2 font-mono text-xs">{{ row.delivery_no }}</td>
                                        <td class="px-3 py-2 text-xs text-muted-foreground">{{ formatDate(row.delivery_date) }}</td>
                                        <td class="px-3 py-2 font-semibold text-blue-600">{{ row.batch_no }}</td>
                                        <td class="px-3 py-2 text-right font-medium">{{ row.bags_delivered }}</td>
                                        <td class="px-3 py-2 text-right font-semibold">{{ formatMoney(row.total_amount) }}</td>
                                        <td class="px-3 py-2 text-right text-emerald-600 font-semibold">{{ formatMoney(row.paid_amount) }}</td>
                                        <td class="px-3 py-2 text-right font-bold text-amber-600">{{ formatMoney(row.outstanding_amount) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
