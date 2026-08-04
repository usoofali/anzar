<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Receipt, Search, Trash2 } from '@lucide/vue';
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

import Pagination from '@/components/ui/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Operational Expenses', href: '/expenses' }],
    },
});

interface ExpenseItem {
    id: number;
    expense_date: string;
    category: string;
    description: string;
    amount: number;
    recorded_by?: any;
}

interface Props {
    expenses: {
        data: ExpenseItem[];
        links: any[];
        from?: number;
        to?: number;
        total?: number;
    };
    categories: string[];
    filters: {
        search?: string;
        category?: string;
        date?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const categoryFilter = ref(props.filters?.category || '');
const dateFilter = ref(props.filters?.date || '');

const handleFilter = () => {
    router.get('/expenses', {
        search: search.value,
        category: categoryFilter.value,
        date: dateFilter.value,
    }, { preserveState: true, replace: true });
};

// Form Modal State
const isModalOpen = ref(false);
const form = useForm({
    expense_date: new Date().toISOString().split('T')[0],
    category: 'Fuel',
    description: '',
    amount: '' as any,
});

const openModal = () => {
    form.reset();
    form.expense_date = new Date().toISOString().split('T')[0];
    form.category = 'Fuel';
    isModalOpen.value = true;
};

const submitForm = () => {
    form.post('/expenses', {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            toast.success('Expense recorded successfully.');
        },
        onError: () => toast.error('Failed to record expense.'),
    });
};

// Delete Modal State
const isDeleteModalOpen = ref(false);
const deletingExpense = ref<ExpenseItem | null>(null);

const openDeleteModal = (exp: ExpenseItem) => {
    deletingExpense.value = exp;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingExpense.value) return;
    router.delete(`/expenses/${deletingExpense.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingExpense.value = null;
            toast.success('Expense deleted successfully.');
        },
        onError: (err: any) => toast.error(err.message || 'Cannot delete expense.'),
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Operational Expenses - ANZAR Table Water" />

    <div class="space-y-4 sm:space-y-6 p-4 sm:p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground">Operational Running Expenses</h1>
                <p class="text-xs sm:text-sm text-muted-foreground">Log factory operating costs independent of production batches.</p>
            </div>
            <Button class="w-full sm:w-auto justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 shrink-0" @click="openModal">
                <Plus class="h-4 w-4" /> Record Expense
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
                            placeholder="Search category, description..."
                            class="pl-9 w-full"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <Input type="date" v-model="dateFilter" class="w-full sm:w-auto text-sm" @change="handleFilter" />
                        <select
                            v-model="categoryFilter"
                            class="w-full sm:w-auto rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring dark:bg-slate-900 dark:border-slate-800"
                            @change="handleFilter"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="!expenses?.data || expenses.data.length === 0">
                    <EmptyState
                        title="No Operational Expenses Found"
                        description="Log factory running costs such as fuel, generator maintenance, or staff salaries."
                        actionText="Record Expense"
                        :icon="Receipt"
                        @action="openModal"
                    />
                </div>
                <div v-else>
                    <div class="relative w-full overflow-x-auto rounded-md border border-border/40">
                        <table class="w-full min-w-[600px] text-left text-sm">
                            <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Category</th>
                                    <th class="px-4 py-3">Description</th>
                                    <th class="px-4 py-3 text-right">Amount</th>
                                    <th class="px-4 py-3">Recorded By</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/40">
                                <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-muted/30">
                                    <td class="px-4 py-3 text-muted-foreground whitespace-nowrap">{{ formatDate(exp.expense_date) }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200/50 dark:border-purple-800/40">
                                            {{ exp.category }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-foreground">{{ exp.description }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-rose-600 dark:text-rose-400 whitespace-nowrap">{{ formatMoney(exp.amount) }}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground whitespace-nowrap">{{ exp.recorded_by?.name || 'Staff' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(exp)">
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        :links="expenses.links"
                        :from="expenses.from"
                        :to="expenses.to"
                        :total="expenses.total"
                        class="mt-4"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Record Expense Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Record Operational Expense</DialogTitle>
                    <DialogDescription>Log factory operating costs independent of production batches.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="expense_date">Date</Label>
                            <Input id="expense_date" type="date" v-model="form.expense_date" required />
                        </div>
                        <div class="space-y-1">
                            <Label for="category">Category</Label>
                            <select
                                id="category"
                                v-model="form.category"
                                required
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="description">Expense Description</Label>
                        <Input id="description" v-model="form.description" required placeholder="e.g. 50 Litres Diesel for Factory Generator" />
                    </div>

                    <div class="space-y-1">
                        <Label for="amount">Amount (₦)</Label>
                        <Input id="amount" type="number" step="0.01" min="1" v-model="form.amount" required placeholder="e.g. 45000.00" />
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">Save Expense</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Expense Log?"
            description="Are you sure you want to delete this operational expense record? This action cannot be undone."
            confirmText="Delete Expense"
            @confirm="confirmDelete"
        />
    </div>
</template>
