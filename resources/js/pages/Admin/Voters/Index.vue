<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItemType } from '@/types';

defineProps<{
    voters: Array<{
        id: number;
        first_name: string | null;
        last_name: string | null;
        email: string;
        age_group: string | null;
        gender: string | null;
        location: string | null;
        status: string;
        times_voted: number;
        last_login: string | null;
        registered_date: string;
    }>;
    totalVoters: number;
    pendingApprovals: number;
}>();

const showEditModal = ref(false);
const editingVoter = ref<any>(null);

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Voters', href: '/admin/voters' },
];

function openEditModal(voter: any) {
    editingVoter.value = voter;
    showEditModal.value = true;
}

function updateStatus(voterId: number, status: string) {
    router.patch(`/admin/voters/${voterId}/status`, { status });
}
</script>

<template>
    <Head title="Manage Voters" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <h1 class="text-3xl font-bold">Manage Voters</h1>

            <!-- Cards -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">
                        Total Voters
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ totalVoters }}
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">
                        Pending Approvals
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ pendingApprovals }}
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">First Name</th>
                            <th class="px-4 py-3 text-left">Last Name</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Age Group</th>
                            <th class="px-4 py-3 text-left">Gender</th>
                            <th class="px-4 py-3 text-left">Location</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Times Voted</th>
                            <th class="px-4 py-3 text-left">Last Login</th>
                            <th class="px-4 py-3 text-left">Registered Date</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="voter in voters"
                            :key="voter.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">
                                {{ voter.first_name || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ voter.last_name || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">{{ voter.email }}</td>
                            <td class="px-4 py-3">
                                {{ voter.age_group || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ voter.gender || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ voter.location || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            voter.status === 'approved',
                                        'bg-yellow-100 text-yellow-800':
                                            voter.status === 'pending',
                                        'bg-red-100 text-red-800':
                                            voter.status === 'rejected',
                                    }"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                >
                                    {{ voter.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ voter.times_voted }}</td>
                            <td class="px-4 py-3">
                                {{ voter.last_login || 'Never' }}
                            </td>
                            <td class="px-4 py-3">{{ voter.registered_date }}</td>
                            <td class="px-4 py-3">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openEditModal(voter)"
                                >
                                    Edit
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="voters.length === 0">
                            <td
                                colspan="11"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No voters found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Edit Voter</DialogTitle>
                <div v-if="editingVoter" class="space-y-4">
                    <div>
                        <Label>First Name</Label>
                        <div class="mt-1 text-sm">
                            {{ editingVoter.first_name || 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <Label>Last Name</Label>
                        <div class="mt-1 text-sm">
                            {{ editingVoter.last_name || 'N/A' }}
                        </div>
                    </div>
                    <div>
                        <Label>Email</Label>
                        <div class="mt-1 text-sm">{{ editingVoter.email }}</div>
                    </div>
                    <div>
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            v-model="editingVoter.status"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                            @change="
                                updateStatus(editingVoter.id, editingVoter.status)
                            "
                        >
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="showEditModal = false"
                        >
                            Close
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>


