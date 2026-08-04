<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CreditCard, DollarSign, Plus, Search, Trash2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Outstanding Debts', href: '/customer-debts' }],
    },
});

interface DebtItem {
    id: number;
    outstanding_amount: number;
    status: string;
    created_at: string;
    customer?: any;
    batch?: any;
    delivery?: any;
    payments?: any[];
}

interface BatchOption {
    id: number;
    batch_no: string;
}

interface CustomerOption {
    id: number;
    shop_name: string;
}

interface Props {
    debts: {
        data: DebtItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    batches: BatchOption[];
    customers: CustomerOption[];
    filters: {
        search?: string;
        batch_id?: string;
        customer_id?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const batchFilter = ref(props.filters?.batch_id || '');
const customerFilter = ref(props.filters?.customer_id || '');
const statusFilter = ref(props.filters?.status || 'open');

const handleFilter = () => {
    router.get('/customer-debts', {
        search: search.value,
        batch_id: batchFilter.value,
        customer_id: customerFilter.value,
        status: statusFilter.value,
    }, { preserveState: true, replace: true });
};

// Record Debt Payment Modal State
const isPaymentModalOpen = ref(false);
const selectedDebt = ref<DebtItem | null>(null);

const paymentForm = useForm({
    debt_id: 0,
    payment_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    amount: '' as any,
});

const openPaymentModal = (debt: DebtItem) => {
    selectedDebt.value = debt;
    paymentForm.debt_id = debt.id;
    paymentForm.payment_date = new Date().toISOString().split('T')[0];
    paymentForm.payment_method = 'cash';
    paymentForm.amount = debt.outstanding_amount;
    isPaymentModalOpen.value = true;
};

const submitPayment = () => {
    paymentForm.post('/debt-payments', {
        onSuccess: () => {
            isPaymentModalOpen.value = false;
            toast.success('Debt repayment recorded successfully.');
        },
        onError: (err: any) => toast.error(err.amount || 'Failed to record repayment.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Outstanding Customer Debts - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Outstanding Customer Credit</h1>
                <p class="text-sm text-muted-foreground">Track unpaid shop deliveries, receive debt repayments, and monitor credit age.</p>
            </div>
        </div>

        <!-- Search & Filters -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-xs">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search customer shop or batch..."
                            class="pl-9 w-full"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <select
                            v-model="statusFilter"
                            class="w-full sm:w-auto rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring dark:bg-slate-900 dark:border-slate-800"
                            @change="handleFilter"
                        >
                            <option value="open">Open Debts</option>
                            <option value="settled">Settled Debts</option>
                            <option value="">All Statuses</option>
                        </select>
                        <select
                            v-model="batchFilter"
                            class="w-full sm:w-auto rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring dark:bg-slate-900 dark:border-slate-800"
                            @change="handleFilter"
                        >
                            <option value="">All Batches</option>
                            <option v-for="b in batches" :key="b.id" :value="b.id">{{ b.batch_no }}</option>
                        </select>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="!debts?.data || debts.data.length === 0">
                    <EmptyState
                        title="No Outstanding Debts Found"
                        description="All customer delivery debts have been fully settled or match your filters."
                        :icon="CreditCard"
                    />
                </div>
                <div v-else>
                    <div class="relative overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Customer Shop</th>
                                    <th class="px-4 py-3">Batch</th>
                                    <th class="px-4 py-3">Delivery No</th>
                                    <th class="px-4 py-3 text-right">Original Delivery</th>
                                    <th class="px-4 py-3 text-right">Outstanding Debt</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="debt in debts.data" :key="debt.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-foreground">{{ debt.customer?.shop_name }}</div>
                                        <div class="text-xs text-muted-foreground">{{ debt.customer?.owner_name || 'N/A' }} • {{ debt.customer?.phone || '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ debt.batch?.batch_no }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground whitespace-nowrap">{{ debt.delivery?.delivery_no }}</td>
                                    <td class="px-4 py-3 text-right text-muted-foreground whitespace-nowrap">{{ formatMoney(debt.delivery?.total_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap">
                                        {{ formatMoney(debt.outstanding_amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <StatusBadge :status="debt.status" />
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button
                                            v-if="debt.status === 'open'"
                                            variant="default"
                                            size="sm"
                                            class="h-8 px-2 text-xs bg-emerald-600 hover:bg-emerald-700"
                                            @click="openPaymentModal(debt)"
                                        >
                                            <DollarSign class="h-3.5 w-3.5 mr-1" /> Record Repayment
                                        </Button>
                                        <span v-else class="text-xs text-muted-foreground font-medium">Fully Paid</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="debts.links"
                        :from="debts.from"
                        :to="debts.to"
                        :total="debts.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Record Debt Payment Dialog -->
        <Dialog :open="isPaymentModalOpen" @update:open="isPaymentModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Customer Debt Repayment</DialogTitle>
                    <DialogDescription>
                        Receiving payment for {{ selectedDebt?.customer?.shop_name }} (Batch {{ selectedDebt?.batch?.batch_no }})
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitPayment" class="space-y-4 py-2">
                    <div class="p-3 rounded-lg border bg-amber-500/10 border-amber-500/20 flex items-center justify-between">
                        <span class="text-xs text-amber-800 dark:text-amber-300 font-medium">Current Outstanding Balance:</span>
                        <span class="text-base font-bold text-amber-600 dark:text-amber-400">{{ formatMoney(selectedDebt?.outstanding_amount || 0) }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="payment_date">Payment Date</Label>
                            <Input id="payment_date" type="date" v-model="paymentForm.payment_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="payment_method">Payment Method</Label>
                            <select
                                id="payment_method"
                                v-model="paymentForm.payment_method"
                                required
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="cash">Physical Cash</option>
                                <option value="transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="amount">Payment Amount (₦)</Label>
                        <Input
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            :max="selectedDebt?.outstanding_amount"
                            v-model="paymentForm.amount"
                            required
                        />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isPaymentModalOpen = false">Cancel</Button>
                        <Button type="submit" class="bg-emerald-600 hover:bg-emerald-700" :disabled="paymentForm.processing">
                            Save Repayment
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
