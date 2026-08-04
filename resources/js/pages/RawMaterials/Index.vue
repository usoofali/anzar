<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Boxes, Plus, Search, Trash2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
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
        breadcrumbs: [{ title: 'Nylon Purchases', href: '/raw-materials' }],
    },
});

interface PurchaseItem {
    id: number;
    purchase_no: string;
    purchase_date: string;
    quantity_kg: number;
    unit_price: number;
    total_cost: number;
    remarks: string | null;
    production_batch?: {
        id: number;
        batch_no: string;
    } | null;
}

interface Props {
    purchases: {
        data: PurchaseItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get('/raw-materials', { search: search.value }, { preserveState: true, replace: true });
};

// Form Modal
const isModalOpen = ref(false);
const form = useForm({
    purchase_date: new Date().toISOString().split('T')[0],
    quantity_kg: '' as any,
    unit_price: '' as any,
    remarks: '',
});

const computedTotalCost = computed(() => {
    const qty = parseFloat(form.quantity_kg) || 0;
    const price = parseFloat(form.unit_price) || 0;
    return qty * price;
});

const openModal = () => {
    form.reset();
    form.purchase_date = new Date().toISOString().split('T')[0];
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/raw-materials', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Raw material purchase recorded successfully.');
        },
        onError: () => toast.error('Failed to record nylon purchase.'),
    });
};

// Delete Modal
const isDeleteModalOpen = ref(false);
const deletingPurchase = ref<PurchaseItem | null>(null);

const openDeleteModal = (p: PurchaseItem) => {
    deletingPurchase.value = p;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingPurchase.value) return;
    router.delete(`/raw-materials/${deletingPurchase.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingPurchase.value = null;
            toast.success('Purchase record deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete purchase.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Raw Material Purchases - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Packaging Nylon Purchases</h1>
                <p class="text-sm text-muted-foreground">Record and trace raw material nylon purchases in kilograms.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openModal">
                <Plus class="h-4 w-4" /> Record Nylon Purchase
            </Button>
        </div>

        <!-- Search & Table Card -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                    <div class="relative w-full sm:max-w-md">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search purchase no..."
                            class="pl-9 w-full"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="purchases.data.length === 0">
                    <EmptyState
                        title="No Nylon Purchases Recorded"
                        description="Record a raw material nylon purchase to assign it to production batches."
                        actionText="Record Purchase"
                        :icon="Boxes"
                        @action="openModal"
                    />
                </div>
                <div v-else>
                    <div class="relative overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Purchase No</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3 text-right">Quantity (KG)</th>
                                    <th class="px-4 py-3 text-right">Unit Price</th>
                                    <th class="px-4 py-3 text-right">Total Cost</th>
                                    <th class="px-4 py-3 text-center">Batch Linked</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="p in purchases.data" :key="p.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 font-mono text-xs font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ p.purchase_no }}</td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(p.purchase_date) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-foreground whitespace-nowrap">{{ p.quantity_kg }} KG</td>
                                    <td class="px-4 py-3 text-right text-muted-foreground whitespace-nowrap">{{ formatMoney(p.unit_price) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ formatMoney(p.total_cost) }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <span v-if="p.production_batch" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200/50 dark:border-blue-800/40">
                                            {{ p.production_batch.batch_no }}
                                        </span>
                                        <span v-else class="text-xs text-muted-foreground">Unassigned</span>
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(p)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="purchases.links"
                        :from="purchases.from"
                        :to="purchases.to"
                        :total="purchases.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Record Purchase Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Nylon Purchase</DialogTitle>
                    <DialogDescription>Log a new raw material nylon purchase.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="purchase_date">Purchase Date</Label>
                            <Input id="purchase_date" type="date" v-model="form.purchase_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="quantity_kg">Quantity (KG)</Label>
                            <Input id="quantity_kg" type="number" step="0.01" min="0.1" v-model="form.quantity_kg" required placeholder="20.00" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="unit_price">Unit Price per KG (₦)</Label>
                            <Input id="unit_price" type="number" step="0.01" min="0" v-model="form.unit_price" required placeholder="1800.00" />
                        </div>
                        <div class="space-y-1">
                            <Label>Calculated Total Cost</Label>
                            <div class="flex h-9 w-full items-center rounded-md border border-input bg-muted/40 px-3 py-1 text-sm font-semibold text-emerald-600">
                                {{ formatMoney(computedTotalCost) }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="remarks">Remarks (Optional)</Label>
                        <Input id="remarks" v-model="form.remarks" placeholder="Batch thickness 50 micron" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Save Purchase</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Nylon Purchase?"
            :description="`Are you sure you want to delete purchase '${deletingPurchase?.purchase_no}'? This action cannot be undone.`"
            confirmText="Delete Purchase"
            @confirm="confirmDelete"
        />
    </div>
</template>
