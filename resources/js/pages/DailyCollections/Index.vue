<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Search, Trash2, Wallet } from '@lucide/vue';
import { toast } from 'vue-sonner';
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
import { formatDate } from '@/lib/utils';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Daily Collections', href: '/daily-collections' }],
    },
});

interface CollectionItem {
    id: number;
    collection_date: string;
    cash_amount: number;
    transfer_amount: number;
    total_collection: number;
    remarks: string | null;
    batch?: any;
    recorded_by?: any;
}

interface BatchOption {
    id: number;
    batch_no: string;
}

interface Props {
    collections: {
        data: CollectionItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    batches: BatchOption[];
    filters: {
        search?: string;
        batch_id?: string;
        date?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const batchFilter = ref(props.filters.batch_id || '');
const dateFilter = ref(props.filters.date || '');

const handleFilter = () => {
    router.get('/daily-collections', {
        search: search.value,
        batch_id: batchFilter.value,
        date: dateFilter.value,
    }, { preserveState: true, replace: true });
};

// Form Modal State
const isModalOpen = ref(false);
const form = useForm({
    batch_id: '',
    collection_date: new Date().toISOString().split('T')[0],
    cash_amount: 0 as any,
    transfer_amount: 0 as any,
    remarks: '',
});

const computedTotal = computed(() => {
    const cash = parseFloat(form.cash_amount) || 0;
    const transfer = parseFloat(form.transfer_amount) || 0;
    return cash + transfer;
});

const openModal = () => {
    form.reset();
    form.collection_date = new Date().toISOString().split('T')[0];
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/daily-collections', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Daily collection recorded successfully.');
        },
        onError: () => toast.error('Failed to record daily collection.'),
    });
};

// Delete Modal State
const isDeleteModalOpen = ref(false);
const deletingCollection = ref<CollectionItem | null>(null);

const openDeleteModal = (c: CollectionItem) => {
    deletingCollection.value = c;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingCollection.value) return;
    router.delete(`/daily-collections/${deletingCollection.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingCollection.value = null;
            toast.success('Daily collection deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete collection.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Daily Collections - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Daily Cash & Bank Collections</h1>
                <p class="text-sm text-muted-foreground">Log money physically returned by sales teams per batch for the day.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openModal">
                <Plus class="h-4 w-4" /> Record Daily Collection
            </Button>
        </div>

        <!-- Search & Filters -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-xs">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search batch number..."
                            class="pl-9 w-full"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <Input type="date" v-model="dateFilter" class="w-full sm:w-auto text-sm" @change="handleFilter" />
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
                <div v-if="collections.data.length === 0">
                    <EmptyState
                        title="No Collections Logged"
                        description="Log physical cash and bank transfers returned by the sales team."
                        actionText="Record Collection"
                        :icon="Wallet"
                        @action="openModal"
                    />
                </div>
                <div v-else>
                    <div class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Batch</th>
                                    <th class="px-4 py-3 text-right">Cash Amount</th>
                                    <th class="px-4 py-3 text-right">Transfer Amount</th>
                                    <th class="px-4 py-3 text-right">Total Collection</th>
                                    <th class="px-4 py-3">Recorded By</th>
                                    <th class="px-4 py-3">Remarks</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="c in collections.data" :key="c.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 text-muted-foreground font-medium whitespace-nowrap">{{ formatDate(c.collection_date) }}</td>
                                    <td class="px-4 py-3 font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ c.batch?.batch_no }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-foreground whitespace-nowrap">{{ formatMoney(c.cash_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-foreground whitespace-nowrap">{{ formatMoney(c.transfer_amount) }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ formatMoney(c.total_collection) }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">{{ c.recorded_by?.name || 'Staff' }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ c.remarks || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(c)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="collections.links"
                        :from="collections.from"
                        :to="collections.to"
                        :total="collections.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Record Collection Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Daily Collection</DialogTitle>
                    <DialogDescription>Log physical cash and transfer collections returned by sales staff.</DialogDescription>
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
                                <option value="" disabled>Select batch...</option>
                                <option v-for="b in batches" :key="b.id" :value="b.id">{{ b.batch_no }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <Label for="collection_date">Collection Date</Label>
                            <Input id="collection_date" type="date" v-model="form.collection_date" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="cash_amount">Cash Amount (₦)</Label>
                            <Input id="cash_amount" type="number" step="0.01" min="0" v-model="form.cash_amount" placeholder="0.00" />
                        </div>
                        <div class="space-y-1">
                            <Label for="transfer_amount">Transfer Amount (₦)</Label>
                            <Input id="transfer_amount" type="number" step="0.01" min="0" v-model="form.transfer_amount" placeholder="0.00" />
                        </div>
                    </div>

                    <!-- Total Collection Summary Box -->
                    <div class="p-3 rounded-lg border bg-emerald-500/10 border-emerald-500/20 flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Total Collection = Cash + Transfer:</span>
                        <span class="text-base font-bold text-emerald-600 dark:text-emerald-400">{{ formatMoney(computedTotal) }}</span>
                    </div>

                    <div class="space-y-1">
                        <Label for="remarks">Remarks / Notes</Label>
                        <Input id="remarks" v-model="form.remarks" placeholder="Route 1 daily collection" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing || computedTotal <= 0">
                            Save Collection
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Collection Record?"
            description="Are you sure you want to delete this daily collection record? This action cannot be undone."
            confirmText="Delete Collection"
            @confirm="confirmDelete"
        />
    </div>
</template>
