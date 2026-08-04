<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { KeyRound, Plus, Search, Trash2, UserCheck, UserPlus, Users as UsersIcon } from '@lucide/vue';
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
        breadcrumbs: [{ title: 'User Management', href: '/users' }],
    },
});

interface UserItem {
    id: number;
    name: string;
    email: string;
    username: string | null;
    phone: string | null;
    role: string;
    status: string;
}

interface Props {
    users: {
        data: UserItem[];
        links: any[];
    };
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');

const handleSearch = () => {
    router.get('/users', { search: search.value }, { preserveState: true, replace: true });
};

// Create / Edit Form Modal State
const isModalOpen = ref(false);
const editingUser = ref<UserItem | null>(null);

const form = useForm({
    name: '',
    email: '',
    username: '',
    phone: '',
    role: 'sales_staff',
    status: 'active',
    password: '',
});

const openCreateModal = () => {
    editingUser.value = null;
    form.reset();
    isModalOpen.value = true;
};

const openEditModal = (user: UserItem) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.username = user.username || '';
    form.phone = user.phone || '';
    form.role = user.role;
    form.status = user.status;
    form.password = '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (editingUser.value) {
        form.put(`/users/${editingUser.value.id}`, {
            onSuccess: () => {
                isModalOpen.value = false;
                toast.success('User updated successfully.');
            },
            onError: () => {
                toast.error('Failed to update user. Please check form errors.');
            },
        });
    } else {
        form.post('/users', {
            onSuccess: () => {
                isModalOpen.value = false;
                form.reset();
                toast.success('User created successfully.');
            },
            onError: () => {
                toast.error('Failed to create user. Please check form errors.');
            },
        });
    }
};

// Password Reset Modal
const isResetModalOpen = ref(false);
const resetUserId = ref<number | null>(null);
const resetForm = useForm({
    password: '',
});

const openResetModal = (id: number) => {
    resetUserId.value = id;
    resetForm.reset();
    isResetModalOpen.value = true;
};

const submitResetPassword = () => {
    if (!resetUserId.value) return;
    resetForm.post(`/users/${resetUserId.value}/reset-password`, {
        onSuccess: () => {
            isResetModalOpen.value = false;
            toast.success('Password reset successfully.');
        },
        onError: () => {
            toast.error('Failed to reset password.');
        },
    });
};

// Delete Confirmation Modal
const isDeleteModalOpen = ref(false);
const deletingUser = ref<UserItem | null>(null);

const openDeleteModal = (user: UserItem) => {
    deletingUser.value = user;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!deletingUser.value) return;
    router.delete(`/users/${deletingUser.value.id}`, {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            deletingUser.value = null;
            toast.success('User deleted successfully.');
        },
        onError: (errors: any) => {
            toast.error(errors.message || 'Failed to delete user.');
        },
    });
};

const roleLabel = (role: string) => {
    switch (role) {
        case 'manager':
            return 'Manager';
        case 'production_staff':
            return 'Production Staff';
        case 'sales_staff':
            return 'Sales Staff';
        default:
            return role;
    }
};
</script>

<template>
    <Head title="User Management - ANZAR Table Water" />

    <div class="space-y-6 p-6">
        <!-- Header Banner -->
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">User Management</h1>
                <p class="text-sm text-muted-foreground">Manage staff accounts, roles, and access credentials.</p>
            </div>
            <Button class="gap-1.5 bg-blue-600 hover:bg-blue-700" @click="openCreateModal">
                <UserPlus class="h-4 w-4" /> Add Staff Member
            </Button>
        </div>

        <!-- Search Bar & Filters -->
        <Card>
            <CardHeader class="pb-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            v-model="search"
                            placeholder="Search by name, email, username..."
                            class="pl-9"
                            @keyup.enter="handleSearch"
                        />
                    </div>
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="!users?.data || users.data.length === 0">
                    <EmptyState
                        title="No Users Found"
                        description="No staff user accounts match your search criteria."
                        actionText="Add New Staff"
                        :icon="UsersIcon"
                        @action="openCreateModal"
                    />
                </div>
                <div v-else class="relative overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Username / Email</th>
                                <th class="px-4 py-3">Phone</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="u in users.data" :key="u.id" class="hover:bg-muted/30">
                                <td class="px-4 py-3 font-semibold text-foreground">{{ u.name }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-xs font-mono font-medium text-foreground">{{ u.username || 'N/A' }}</div>
                                    <div class="text-xs text-muted-foreground">{{ u.email }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-muted-foreground">{{ u.phone || 'N/A' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                        {{ roleLabel(u.role) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <StatusBadge :status="u.status" />
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <Button variant="outline" size="sm" class="h-8 px-2 text-xs" @click="openEditModal(u)">
                                        Edit
                                    </Button>
                                    <Button variant="outline" size="sm" class="h-8 px-2 text-xs text-amber-600 hover:text-amber-700" @click="openResetModal(u.id)">
                                        <KeyRound class="h-3.5 w-3.5" />
                                    </Button>
                                    <Button variant="ghost" size="sm" class="h-8 px-2 text-xs text-rose-600 hover:text-rose-700" @click="openDeleteModal(u)">
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Create / Edit User Dialog -->
        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ editingUser ? 'Edit User' : 'Create Staff Member' }}</DialogTitle>
                    <DialogDescription>
                        {{ editingUser ? 'Update staff member information and permissions.' : 'Add a new staff member to the system.' }}
                    </DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitForm" class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label for="name">Full Name</Label>
                        <Input id="name" v-model="form.name" required placeholder="e.g. Ibrahim Abubakar" />
                        <p v-if="form.errors.name" class="text-xs text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="username">Username</Label>
                            <Input id="username" v-model="form.username" placeholder="e.g. ibrahim" />
                            <p v-if="form.errors.username" class="text-xs text-rose-500">{{ form.errors.username }}</p>
                        </div>
                        <div class="space-y-1">
                            <Label for="email">Email</Label>
                            <Input id="email" type="email" v-model="form.email" required placeholder="ibrahim@anzar.com" />
                            <p v-if="form.errors.email" class="text-xs text-rose-500">{{ form.errors.email }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="phone">Phone Number</Label>
                            <Input id="phone" v-model="form.phone" placeholder="08012345678" />
                        </div>
                        <div class="space-y-1">
                            <Label for="role">Role</Label>
                            <select
                                id="role"
                                v-model="form.role"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring"
                            >
                                <option value="manager">Manager</option>
                                <option value="production_staff">Production Staff</option>
                                <option value="sales_staff">Sales Staff</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1" v-if="!editingUser">
                        <Label for="password">Password</Label>
                        <Input id="password" type="password" v-model="form.password" required placeholder="••••••••" />
                        <p v-if="form.errors.password" class="text-xs text-rose-500">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-1" v-if="editingUser">
                        <Label for="status">Account Status</Label>
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
                            {{ editingUser ? 'Update User' : 'Save User' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Reset Password Dialog -->
        <Dialog :open="isResetModalOpen" @update:open="isResetModalOpen = $event">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Reset Password</DialogTitle>
                    <DialogDescription>Enter a new password for this staff member.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submitResetPassword" class="space-y-4 py-2">
                    <div class="space-y-1">
                        <Label for="new_password">New Password</Label>
                        <Input id="new_password" type="password" v-model="resetForm.password" required placeholder="Minimum 8 characters" />
                        <p v-if="resetForm.errors.password" class="text-xs text-rose-500">{{ resetForm.errors.password }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="isResetModalOpen = false">Cancel</Button>
                        <Button type="submit" :disabled="resetForm.processing">Reset Password</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="isDeleteModalOpen"
            title="Delete Staff Member?"
            :description="`Are you sure you want to delete user account '${deletingUser?.name}'? This action cannot be undone.`"
            confirmText="Delete User"
            @confirm="confirmDelete"
        />
    </div>
</template>
