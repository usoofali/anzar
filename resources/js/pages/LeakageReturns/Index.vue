<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Droplet, Plus, Search, Trash2 } from '@lucide/vue';
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
        breadcrumbs: [{ title: 'Leakage Returns', href: '/leakage-returns' }],
    },
});

interface LeakageItem {
    id: number;
    date: string;
    returned_pieces: number;
    replacement_issued: number;
    remarks: string | null;
    delivery?: any;
    customer?: any;
    batch?: any;
}

interface DeliveryOption {
    id: number;
    delivery_no: string;
    customer_name: string;
    batch_no: string;
}

interface Props {
    leakageReturns: {
        data: LeakageItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    recentDeliveries: DeliveryOption[];
    filters: {
        search?: string;
        date?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const dateFilter = ref(props.filters?.date || '');

const handleFilter = () => {
    router.get('/leakage-returns', {
        search: search.value,
        date: dateFilter.value,
    }, { preserveState: true, replace: true });
};

// Form Modal State
const isModalOpen = ref(false);
const form = useForm({
    delivery_id: '',
    date: new Date().toISOString().split('T')[0],
    returned_pieces: '' as any,
    replacement_issued: '' as any,
    remarks: '',
});

const openModal = () => {
    form.reset();
    form.date = new Date().toISOString().split('T')[0];
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/leakage-returns', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Leakage return recorded successfully.');
        },
        onError: () => toast.error('Failed to record leakage return.'),
    });
};

// Delete Modal State
const isDeleteModalOpen = ref(false);
const deletingReturn = ref<LeakageItem | null>(null);

const openDeleteModal = (item: LeakageItem) => {
    deletingReturn.value = item;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingReturn.value) return;
    router.delete(`/leakage-returns/${deletingReturn.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingReturn.value = null;
            toast.success('Leakage return deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete record.'),
    });
};
</script>

<template>
    <Head title="Leakage Returns - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Defective Sachet & Leakage Returns</h1>
                <p class="text-sm text-muted-foreground">Track defective water sachets returned in pieces and replacement sachets issued.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openModal">
                <Plus class="h-4 w-4" /> Record Leakage Return
            </Button>
        </div>

        <!-- Table & Search Card -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-xs">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search delivery, customer, batch..."
                            class="pl-9 w-full"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <Input type="date" v-model="dateFilter" class="w-full sm:w-auto text-sm" @change="handleFilter" />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="!leakageReturns?.data || leakageReturns.data.length === 0">
                    <EmptyState
                        title="No Leakage Returns Found"
                        description="Record defective sachets returned by shops to monitor quality control."
                        actionText="Record Leakage"
                        :icon="Droplet"
                        @action="openModal"
                    />
                </div>
                <div v-else>
                    <div class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[700px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Customer Shop</th>
                                    <th class="px-4 py-3">Batch</th>
                                    <th class="px-4 py-3">Delivery No</th>
                                    <th class="px-4 py-3 text-right">Returned (Pieces)</th>
                                    <th class="px-4 py-3 text-right">Replacement Issued</th>
                                    <th class="px-4 py-3">Remarks</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="lr in leakageReturns.data" :key="lr.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(lr.date) }}</td>
                                    <td class="px-4 py-3 font-semibold text-foreground whitespace-nowrap">{{ lr.customer?.shop_name }}</td>
                                    <td class="px-4 py-3 font-medium text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ lr.batch?.batch_no }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground whitespace-nowrap">{{ lr.delivery?.delivery_no }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">{{ lr.returned_pieces }} Pcs</td>
                                    <td class="px-4 py-3 text-right font-bold text-blue-600 dark:text-blue-400 whitespace-nowrap">{{ lr.replacement_issued }} Pcs</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ lr.remarks || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(lr)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="leakageReturns.links"
                        :from="leakageReturns.from"
                        :to="leakageReturns.to"
                        :total="leakageReturns.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Record Leakage Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Defective Sachets / Leakages</DialogTitle>
                    <DialogDescription>Select customer delivery and specify returned pieces.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label for="delivery_id">Customer Delivery</Label>
                        <select
                            id="delivery_id"
                            v-model="form.delivery_id"
                            required
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                        >
                            <option value="" disabled>Select delivery...</option>
                            <option v-for="d in recentDeliveries" :key="d.id" :value="d.id">
                                {{ d.delivery_no }} - {{ d.customer_name }} (Batch {{ d.batch_no }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <Label for="date">Return Date</Label>
                            <Input id="date" type="date" v-model="form.date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="returned_pieces">Leaked (Pieces)</Label>
                            <Input id="returned_pieces" type="number" min="1" v-model="form.returned_pieces" required placeholder="e.g. 5" />
                        </div>
                        <div class="space-y-1">
                            <Label for="replacement_issued">Replaced (Pieces)</Label>
                            <Input id="replacement_issued" type="number" min="0" v-model="form.replacement_issued" required placeholder="e.g. 5" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="remarks">Remarks / Reason</Label>
                        <Input id="remarks" v-model="form.remarks" placeholder="Sealing flaw on top edge" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Save Leakage Return</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Leakage Return Record?"
            description="Are you sure you want to delete this leakage return log? This action cannot be undone."
            confirmText="Delete Record"
            @confirm="confirmDelete"
        />
    </div>
</template>
