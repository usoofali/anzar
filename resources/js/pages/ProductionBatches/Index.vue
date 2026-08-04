<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowUpRight, Eye, Factory, Lock, MoreHorizontal, Plus, Search, Trash2, Unlock } from '@lucide/vue';
import { toast } from 'vue-sonner';
import StatusBadge from '@/components/ui/StatusBadge.vue';
import ConfirmModal from '@/components/ui/ConfirmModal.vue';
import EmptyState from '@/components/ui/EmptyState.vue';
import Pagination from '@/components/ui/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
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
        breadcrumbs: [{ title: 'Production Batches', href: '/production-batches' }],
    },
});

interface BatchItem {
    id: number;
    batch_no: string;
    production_date: string;
    raw_material: string;
    bags_produced: number;
    bags_delivered: number;
    remaining_stock: number;
    produced_by_name: string;
    status: string;
    expected_revenue: number;
    total_collected: number;
    outstanding_credit: number;
    returned_pieces: number;
    remaining_packing_pieces?: number;
}

interface RawMaterial {
    id: number;
    purchase_no: string;
    supplier: string;
    quantity_kg: number;
    purchase_date: string;
    packing_nylon_pieces: number;
}

interface Props {
    batches: {
        data: BatchItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    availablePurchases: RawMaterial[];
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const handleFilter = () => {
    router.get('/production-batches', { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true });
};

// Create Form Modal
const isModalOpen = ref(false);
const form = useForm({
    raw_material_purchase_id: '',
    production_date: new Date().toISOString().split('T')[0],
    production_time: 'morning',
    bags_produced: '' as any,
});

const handlePurchaseChange = () => {
    const purchaseId = parseInt(form.raw_material_purchase_id);
    const purchase = props.availablePurchases.find(p => p.id === purchaseId);
    if (purchase) {
        form.bags_produced = purchase.packing_nylon_pieces || '';
    }
};

const openCreateModal = () => {
    form.reset();
    form.production_date = new Date().toISOString().split('T')[0];
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/production-batches', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Production batch created successfully.');
        },
        onError: () => toast.error('Failed to create production batch.'),
    });
};

// Sub-production Run Modal
const isRunModalOpen = ref(false);
const selectedBatchForRun = ref<BatchItem | null>(null);
const runForm = useForm({
    production_date: new Date().toISOString().split('T')[0],
    production_time: 'morning',
    bags_produced: '' as any,
    remarks: '',
});

const openRunModal = (batch: BatchItem) => {
    selectedBatchForRun.value = batch;
    runForm.reset();
    runForm.production_date = new Date().toISOString().split('T')[0];
    isRunModalOpen.value = true;
};

const submitRunForm = () => {
    if (!selectedBatchForRun.value) return;
    runForm.post(`/production-batches/${selectedBatchForRun.value.id}/productions`, {
        onSuccess: () => {
            isRunModalOpen.value = false;
            runForm.reset();
            selectedBatchForRun.value = null;
            toast.success('Production run logged successfully.');
        },
        onError: () => toast.error('Failed to log production run. Please check remaining limits.'),
    });
};

// Toggle Status Confirmation Modal (Manager)
const isToggleModalOpen = ref(false);
const togglingBatch = ref<BatchItem | null>(null);

const openToggleModal = (batch: BatchItem) => {
    togglingBatch.value = batch;
    isToggleModalOpen.value = true;
};

const confirmToggleStatus = () => {
    if (!togglingBatch.value) return;
    router.post(`/production-batches/${togglingBatch.value.id}/toggle-status`, {}, {
        onSuccess: () => {
            isToggleModalOpen.value = false;
            toast.success(`Batch ${togglingBatch.value?.batch_no} status updated.`);
            togglingBatch.value = null;
        },
        onError: (err: any) => toast.error(err.message || 'Action failed.'),
    });
};

// Delete Batch Modal
const isDeleteModalOpen = ref(false);
const deletingBatch = ref<BatchItem | null>(null);

const openDeleteModal = (batch: BatchItem) => {
    deletingBatch.value = batch;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingBatch.value) return;
    router.delete(`/production-batches/${deletingBatch.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingBatch.value = null;
            toast.success('Production batch deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete batch.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Production Batches - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Production Batches</h1>
                <p class="text-sm text-muted-foreground">Manage active table water production runs, output, and batch closure.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openCreateModal">
                <Plus class="h-4 w-4" /> Create Production Batch
            </Button>
        </div>

        <!-- Search & Table Card -->
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
                        <select
                            v-model="statusFilter"
                            class="w-full sm:w-auto rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring dark:bg-slate-900 dark:border-slate-800"
                            @change="handleFilter"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="closed">Closed</option>
                        </select>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="batches.data.length === 0">
                    <EmptyState
                        title="No Production Batches Found"
                        description="No production batches match your filter selection."
                        actionText="Create Batch"
                        :icon="Factory"
                        @action="openCreateModal"
                    />
                </div>
                <div v-else>
                    <div class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[750px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Batch No</th>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Nylon Purchase</th>
                                    <th class="px-4 py-3 text-right">Produced</th>
                                    <th class="px-4 py-3 text-right">Delivered</th>
                                    <th class="px-4 py-3 text-right">Stock</th>
                                    <th class="px-4 py-3 text-right">Revenue</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="b in batches.data" :key="b.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 font-semibold text-blue-600 dark:text-blue-400 whitespace-nowrap">
                                        <Link :href="`/production-batches/${b.id}`" class="hover:underline flex items-center gap-1">
                                            {{ b.batch_no }} <Eye class="h-3.5 w-3.5 text-muted-foreground" />
                                        </Link>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(b.production_date) }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ b.raw_material }}</td>
                                    <td class="px-4 py-3 text-right font-medium whitespace-nowrap">{{ b.bags_produced }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">{{ b.bags_delivered }}</td>
                                    <td class="px-4 py-3 text-right font-bold whitespace-nowrap">{{ b.remaining_stock }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-foreground whitespace-nowrap">{{ formatMoney(b.total_collected) }}</td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <StatusBadge :status="b.status" />
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 hover:bg-muted">
                                                    <MoreHorizontal class="h-4 w-4 text-muted-foreground" />
                                                    <span class="sr-only">Open menu</span>
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-48">
                                                <DropdownMenuLabel class="text-xs font-normal text-muted-foreground">Actions</DropdownMenuLabel>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem as-child>
                                                    <Link :href="`/production-batches/${b.id}`" class="flex items-center gap-2 cursor-pointer text-xs">
                                                        <Eye class="h-4 w-4 text-blue-500" />
                                                        <span>View Details</span>
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem v-if="b.status === 'active'" @select="openRunModal(b)" class="flex items-center gap-2 cursor-pointer text-xs">
                                                    <Plus class="h-4 w-4 text-emerald-500" />
                                                    <span>Record Production Run</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem @select="openToggleModal(b)" class="flex items-center gap-2 cursor-pointer text-xs">
                                                    <component :is="b.status === 'active' ? Lock : Unlock" class="h-4 w-4" :class="b.status === 'active' ? 'text-amber-500' : 'text-cyan-500'" />
                                                    <span>{{ b.status === 'active' ? 'Close Batch' : 'Reopen Batch' }}</span>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @select="openDeleteModal(b)" class="flex items-center gap-2 cursor-pointer text-xs text-rose-600 dark:text-rose-400 focus:text-rose-600 focus:bg-rose-50 dark:focus:bg-rose-950/50">
                                                    <Trash2 class="h-4 w-4 text-rose-500" />
                                                    <span>Delete Batch</span>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="batches.links"
                        :from="batches.from"
                        :to="batches.to"
                        :total="batches.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Create Batch Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Create Production Batch</DialogTitle>
                    <DialogDescription>Link an available nylon purchase and record bags produced.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label for="raw_material">Raw Material Nylon Purchase</Label>
                        <select
                            id="raw_material"
                            v-model="form.raw_material_purchase_id"
                            required
                            @change="handlePurchaseChange"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                        >
                            <option value="" disabled>Select unassigned nylon purchase...</option>
                            <option v-for="rm in availablePurchases" :key="rm.id" :value="rm.id">
                                {{ rm.purchase_no }} ({{ rm.quantity_kg }} KG / {{ rm.packing_nylon_pieces }} Pcs)
                            </option>
                        </select>
                        <p v-if="availablePurchases.length === 0" class="text-xs text-amber-600">
                            No unassigned nylon purchases available. Record a nylon purchase first.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="production_date">Production Date</Label>
                            <Input id="production_date" type="date" v-model="form.production_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="production_time">Production Shift</Label>
                            <select
                                id="production_time"
                                v-model="form.production_time"
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
                        <Label for="bags_produced">Bags Produced</Label>
                        <Input id="bags_produced" type="number" min="1" v-model="form.bags_produced" required placeholder="e.g. 250" />
                        <p v-if="form.errors.bags_produced" class="text-xs text-rose-500">{{ form.errors.bags_produced }}</p>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing || availablePurchases.length === 0">
                            Create Batch
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Toggle Status Modal -->
        <ConfirmModal
            v-model:open="isToggleModalOpen"
            :title="togglingBatch?.status === 'active' ? 'Close Production Batch?' : 'Reopen Production Batch?'"
            :description="togglingBatch?.status === 'active' 
                ? `Are you sure you want to mark batch '${togglingBatch?.batch_no}' as Closed? Remaining stock: ${togglingBatch?.remaining_stock} bags, Uncollected credit: ${formatMoney(togglingBatch?.outstanding_credit || 0)}.`
                : `Reopen batch '${togglingBatch?.batch_no}' to allow recording new deliveries?`"
            :confirmText="togglingBatch?.status === 'active' ? 'Close Batch' : 'Reopen Batch'"
            :variant="togglingBatch?.status === 'active' ? 'warning' : 'default'"
            @confirm="confirmToggleStatus"
        />

        <!-- Record Sub-Production Run Dialog -->
        <Dialog :open="isRunModalOpen" @update:open="isRunModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Production Run - Batch {{ selectedBatchForRun?.batch_no }}</DialogTitle>
                    <DialogDescription>Add a new sub-production run to accumulate under this batch.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitRunForm" class="space-y-4 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="sub_production_date">Production Date</Label>
                            <Input id="sub_production_date" type="date" v-model="runForm.production_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="sub_production_time">Production Shift</Label>
                            <select
                                id="sub_production_time"
                                v-model="runForm.production_time"
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
                        <Label for="sub_bags_produced">Bags Produced</Label>
                        <Input
                            id="sub_bags_produced"
                            type="number"
                            min="1"
                            :max="selectedBatchForRun?.remaining_packing_pieces"
                            v-model="runForm.bags_produced"
                            required
                            :placeholder="`Max: ${selectedBatchForRun?.remaining_packing_pieces ?? 0} Bags`"
                        />
                        <p class="text-[10px] text-muted-foreground">Available Outer Bags: {{ selectedBatchForRun?.remaining_packing_pieces ?? 0 }} Bags</p>
                    </div>

                    <div class="space-y-1">
                        <Label for="sub_remarks">Remarks (Optional)</Label>
                        <Input id="sub_remarks" v-model="runForm.remarks" placeholder="e.g. Afternoon shift production" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isRunModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="runForm.processing || (selectedBatchForRun?.remaining_packing_pieces ?? 0) <= 0">
                            Record Production Run
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Production Batch?"
            :description="`Are you sure you want to delete batch '${deletingBatch?.batch_no}'? This action cannot be undone.`"
            confirmText="Delete Batch"
            @confirm="confirmDelete"
        />
    </div>
</template>
