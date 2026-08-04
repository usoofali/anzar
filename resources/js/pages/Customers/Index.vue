<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Search, Store, Trash2 } from '@lucide/vue';
import { toast } from 'vue-sonner';
import StatusBadge from '@/components/ui/StatusBadge.vue';
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

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Customers', href: '/customers' }],
    },
});

interface CustomerItem {
    id: number;
    shop_name: string;
    owner_name: string | null;
    phone: string | null;
    address: string | null;
    status: string;
    outstanding_balance: number;
}

interface Props {
    customers: {
        data: CustomerItem[];
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');

const handleFilter = () => {
    router.get('/customers', { search: search.value, status: statusFilter.value }, { preserveState: true, replace: true });
};

// Form Modal State
const isModalOpen = ref(false);
const editingCustomer = ref<CustomerItem | null>(null);

const form = useForm({
    shop_name: '',
    owner_name: '',
    phone: '',
    address: '',
    status: 'active',
});

const openCreateModal = () => {
    editingCustomer.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (c: CustomerItem) => {
    editingCustomer.value = c;
    form.shop_name = c.shop_name;
    form.owner_name = c.owner_name || '';
    form.phone = c.phone || '';
    form.address = c.address || '';
    form.status = c.status;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingCustomer.value) {
        form.put(`/customers/${editingCustomer.value.id}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                toast.success('Customer updated successfully.');
            },
            onError: () => toast.error('Failed to update customer.'),
        });
    } else {
        form.post('/customers', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                toast.success('Customer created successfully.');
            },
            onError: () => toast.error('Failed to create customer.'),
        });
    }
};

// Delete Modal State
const isDeleteModalOpen = ref(false);
const deletingCustomer = ref<CustomerItem | null>(null);

const openDeleteModal = (c: CustomerItem) => {
    deletingCustomer.value = c;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingCustomer.value) return;
    router.delete(`/customers/${deletingCustomer.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingCustomer.value = null;
            toast.success('Customer deleted successfully.');
        },
        onError: (err: any) => {
            toast.error(err.message || 'Cannot delete customer.');
        },
    });
};

const formatMoney = (amount: number) => {
    return '₦' + (amount || 0).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <Head title="Customers (Shops) - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">Shops & Customers Directory</h1>
                <p class="text-sm text-muted-foreground">Manage retail stores, contact details, and account balances.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openCreateModal">
                <Plus class="h-4 w-4" /> Add New Customer
            </Button>
        </div>

        <!-- Search & Filters -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md w-full">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search shop name, owner, phone..."
                            class="pl-9"
                            @keyup.enter="handleFilter"
                        />
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <select
                            v-model="statusFilter"
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            @change="handleFilter"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="customers.data.length === 0">
                    <EmptyState
                        title="No Customers Found"
                        description="No customer shop accounts match your search query."
                        actionText="Add Customer"
                        :icon="Store"
                        @action="openCreateModal"
                    />
                </div>
                <div v-else class="relative overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Shop Name</th>
                                <th class="px-4 py-3">Owner</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Address</th>
                                <th class="px-4 py-3 text-right">Outstanding Debt</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="c in customers.data" :key="c.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 font-semibold text-foreground">{{ c.shop_name }}</td>
                                <td class="px-4 py-3 text-muted-foreground">{{ c.owner_name || 'N/A' }}</td>
                                <td class="px-4 py-3 text-xs font-mono text-muted-foreground">{{ c.phone || 'N/A' }}</td>
                                <td class="px-4 py-3 text-xs text-muted-foreground max-w-xs truncate">{{ c.address || 'N/A' }}</td>
                                <td class="px-4 py-3 text-right font-semibold" :class="c.outstanding_balance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600'">
                                    {{ formatMoney(c.outstanding_balance) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <StatusBadge :status="c.outstanding_balance > 0 ? 'open' : c.status" :label="c.outstanding_balance > 0 ? 'Debt Open' : (c.status === 'active' ? 'Active' : 'Inactive')" />
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <Button variant="outline" size="sm" class="h-8 px-2 text-xs" @click="openEditModal(c)">
                                        Edit
                                    </Button>
                                    <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(c)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Create / Edit Customer Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingCustomer ? 'Edit Customer' : 'Add New Customer Shop' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingCustomer ? 'Update shop contact details and status.' : 'Register a new shop customer in the system.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label for="shop_name">Shop Name</Label>
                        <Input id="shop_name" v-model="form.shop_name" required placeholder="e.g. Aisha Provision Store" />
                        <p v-if="form.errors.shop_name" class="text-xs text-rose-500">{{ form.errors.shop_name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="owner_name">Owner Name</Label>
                            <Input id="owner_name" v-model="form.owner_name" placeholder="e.g. Mrs. Aisha Bello" />
                        </div>
                        <div class="space-y-1">
                            <Label for="phone">Phone Number</Label>
                            <Input id="phone" v-model="form.phone" placeholder="08012345678" />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <Label for="address">Shop Address</Label>
                        <Input id="address" v-model="form.address" placeholder="e.g. No. 12 Main Market Road, Kano" />
                    </div>

                    <div class="space-y-1">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="form.status"
                            class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <DialogFooter class="pt-4">
                        <Button type="button" variant="outline" @click="isModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editingCustomer ? 'Update Customer' : 'Save Customer' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Customer?"
            :description="`Are you sure you want to delete '${deletingCustomer?.shop_name}'? This action cannot be undone.`"
            confirmText="Delete Customer"
            @confirm="confirmDelete"
        />
    </div>
</template>
