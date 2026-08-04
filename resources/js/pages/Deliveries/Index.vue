<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { PackageCheck, Plus, Search, Trash2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
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
import { formatDate } from '@/lib/utils';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Deliveries', href: '/deliveries' }],
    },
});

interface DeliveryItem {
    id: number;
    delivery_no: string;
    delivery_date: string;
    bags_delivered: number;
    unit_price: number;
    total_amount: number;
    paid_amount: number;
    batch?: any;
    customer?: any;
    delivered_by?: any;
}

interface BatchOption {
    id: number;
    batch_no: string;
    remaining_stock: number;
}

interface CustomerOption {
    id: number;
    shop_name: string;
}

interface Props {
    deliveries: {
        data: DeliveryItem[];
        links: any[];
    };
    activeBatches: BatchOption[];
    customers: CustomerOption[];
    filters: {
        search?: string;
        batch_id?: string;
        customer_id?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const batchFilter = ref(props.filters.batch_id || '');
const customerFilter = ref(props.filters.customer_id || '');

const handleFilter = () => {
    router.get('/deliveries', {
        search: search.value,
        batch_id: batchFilter.value,
        customer_id: customerFilter.value,
    }, { preserveState: true, replace: true });
};

// Form Modal State
const isModalOpen = ref(false);
const form = useForm({
    batch_id: '',
    customer_id: '',
    delivery_date: new Date().toISOString().split('T')[0],
    bags_delivered: '' as any,
    unit_price: 400 as any, // Default unit price per bag (e.g. ₦400)
    paid_amount: '' as any,
});

const selectedBatch = computed(() => {
    if (!form.batch_id) return null;
    return props.activeBatches.find(b => b.id === Number(form.batch_id)) || null;
});

const computedTotalAmount = computed(() => {
    const bags = parseInt(form.bags_delivered) || 0;
    const price = parseFloat(form.unit_price) || 0;
    return bags * price;
});

const computedOutstandingDebt = computed(() => {
    const paid = parseFloat(form.paid_amount) || 0;
    return Math.max(0, computedTotalAmount.value - paid);
});

const openCreateModal = () => {
    form.reset();
    form.delivery_date = new Date().toISOString().split('T')[0];
    form.unit_price = 400;
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/deliveries', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Delivery recorded successfully.');
        },
        onError: () => toast.error('Failed to record delivery.'),
    });
};

// Delete Modal State
const isDeleteModalOpen = ref(false);
const deletingDelivery = ref<DeliveryItem | null>(null);

const openDeleteModal = (d: DeliveryItem) => {
    deletingDelivery.value = d;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingDelivery.value) return;
    router.delete(`/deliveries/${deletingDelivery.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingDelivery.value = null;
            toast.success('Delivery deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete delivery.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Deliveries - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Distribution & Deliveries</h1>
                <p class="text-sm text-muted-foreground">Record shop deliveries, upfront cash payments, and automatic customer debt balances.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openCreateModal">
                <Plus class="h-4 w-4" /> Record New Delivery
            </Button>
        </div>

        <!-- Table & Filters Card -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md w-full">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search delivery no, customer, batch..."
                            class="pl-9"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                        <select
                            v-model="batchFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            @change="handleFilter"
                        >
                            <option value="">All Batches</option>
                            <option v-for="b in activeBatches" :key="b.id" :value="b.id">{{ b.batch_no }}</option>
                        </select>
                        <select
                            v-model="customerFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            @change="handleFilter"
                        >
                            <option value="">All Customers</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.shop_name }}</option>
                        </select>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="deliveries.data.length === 0">
                    <EmptyState
                        title="No Deliveries Found"
                        description="Record a delivery when table water bags are supplied to a customer shop."
                        actionText="Record Delivery"
                        :icon="PackageCheck"
                        @action="openCreateModal"
                    />
                </div>
                <div v-else class="relative overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Delivery No</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Batch</th>
                                <th class="px-4 py-3">Customer Shop</th>
                                <th class="px-4 py-3 text-right">Bags</th>
                                <th class="px-4 py-3 text-right">Unit Price</th>
                                <th class="px-4 py-3 text-right">Total Amount</th>
                                <th class="px-4 py-3 text-right">Paid Upfront</th>
                                <th class="px-4 py-3 text-right">Credit / Debt</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="d in deliveries.data" :key="d.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-foreground">{{ d.delivery_no }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ formatDate(d.delivery_date) }}</td>
                                <td class="px-4 py-3 font-medium text-blue-600">{{ d.batch?.batch_no }}</td>
                                <td class="px-4 py-3 font-medium text-foreground">{{ d.customer?.shop_name }}</td>
                                <td class="px-4 py-3 text-right font-bold text-foreground">{{ d.bags_delivered }}</td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ formatMoney(d.unit_price) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-foreground">{{ formatMoney(d.total_amount) }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-600">{{ formatMoney(d.paid_amount) }}</td>
                                <td class="px-4 py-3 text-right font-semibold" :class="d.total_amount - d.paid_amount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-muted-foreground'">
                                    {{ formatMoney(d.total_amount - d.paid_amount) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(d)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Record Delivery Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Record New Delivery</DialogTitle>
                    <DialogDescription>Supply table water bags to a shop and record immediate payment.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="batch_id">Production Batch</Label>
                            <select
                                id="batch_id"
                                v-model="form.batch_id"
                                required
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="" disabled>Select active batch...</option>
                                <option v-for="b in activeBatches" :key="b.id" :value="b.id">
                                    {{ b.batch_no }} (Stock: {{ b.remaining_stock }} bags)
                                </option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <Label for="customer_id">Customer Shop</Label>
                            <select
                                id="customer_id"
                                v-model="form.customer_id"
                                required
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="" disabled>Select customer shop...</option>
                                <option v-for="c in customers" :key="c.id" :value="c.id">
                                    {{ c.shop_name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <Label for="delivery_date">Delivery Date</Label>
                            <Input id="delivery_date" type="date" v-model="form.delivery_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="bags_delivered">Bags Delivered</Label>
                            <Input id="bags_delivered" type="number" min="1" v-model="form.bags_delivered" required placeholder="e.g. 50" />
                        </div>
                        <div class="space-y-1">
                            <Label for="unit_price">Price Per Bag (₦)</Label>
                            <Input id="unit_price" type="number" min="0" v-model="form.unit_price" required placeholder="400" />
                        </div>
                    </div>

                    <!-- Calculations Summary Box -->
                    <div class="p-3 rounded-lg border bg-muted/30 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-muted-foreground">Total Delivery Value:</span>
                            <span class="font-bold text-foreground text-base">{{ formatMoney(computedTotalAmount) }}</span>
                        </div>
                        <div class="space-y-1">
                            <Label for="paid_amount" class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                                Upfront Amount Paid (₦)
                            </Label>
                            <Input id="paid_amount" type="number" step="0.01" min="0" v-model="form.paid_amount" required placeholder="0.00" />
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1 border-t">
                            <span class="text-muted-foreground">Automatic Customer Debt Logged:</span>
                            <span class="font-bold" :class="computedOutstandingDebt > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600'">
                                {{ formatMoney(computedOutstandingDebt) }}
                            </span>
                        </div>
                    </div>

                    <DialogFooter class="pt-2">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing || activeBatches.length === 0">
                            Save Delivery
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Delivery Record?"
            :description="`Are you sure you want to delete delivery '${deletingDelivery?.delivery_no}'? This action cannot be undone.`"
            confirmText="Delete Delivery"
            @confirm="confirmDelete"
        />
    </div>
</template>
