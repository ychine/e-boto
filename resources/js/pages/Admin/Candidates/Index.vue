<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItemType } from '@/types';
import CandidateController from '@/actions/App/Http/Controllers/Admin/CandidateController';

defineProps<{
    candidates: Array<{
        id: number;
        name: string;
        photo: string | null;
        election: string;
        position: string;
        biography: string | null;
        status: string;
    }>;
    positions: Array<{
        id: number;
        name: string;
        election: string;
    }>;
    totalCandidates: number;
    totalElections: number;
}>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingCandidate = ref<any>(null);

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Candidates', href: '/admin/candidates' },
];

function openEditModal(candidate: any) {
    editingCandidate.value = candidate;
    showEditModal.value = true;
}

function deleteCandidate(id: number) {
    if (confirm('Are you sure you want to delete this candidate?')) {
        router.delete(CandidateController.destroy.url(id));
    }
}
</script>

<template>
    <Head title="Manage Candidates" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Manage Candidates</h1>
                <Button @click="showCreateModal = true">Add Candidate</Button>
            </div>

            <!-- Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">
                        Total Candidates
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ totalCandidates }}
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">
                        Total Elections
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ totalElections }}
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">Photo</th>
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Election</th>
                            <th class="px-4 py-3 text-left">Position</th>
                            <th class="px-4 py-3 text-left">Biography</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="candidate in candidates"
                            :key="candidate.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">
                                <img
                                    v-if="candidate.photo"
                                    :src="candidate.photo"
                                    :alt="candidate.name"
                                    class="h-12 w-12 rounded-full object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-200"
                                >
                                    No Photo
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ candidate.name }}</td>
                            <td class="px-4 py-3">{{ candidate.election }}</td>
                            <td class="px-4 py-3">{{ candidate.position }}</td>
                            <td class="px-4 py-3">
                                {{ candidate.biography || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            candidate.status === 'active',
                                        'bg-gray-100 text-gray-800':
                                            candidate.status === 'inactive',
                                    }"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                >
                                    {{ candidate.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openEditModal(candidate)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteCandidate(candidate.id)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="candidates.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No candidates found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Add Candidate</DialogTitle>
                <Form
                    v-bind="CandidateController.store.form()"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showCreateModal = false"
                    enctype="multipart/form-data"
                >
                    <div>
                        <Label for="position_id">Position</Label>
                        <select
                            id="position_id"
                            name="position_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="">Select Position</option>
                            <option
                                v-for="position in positions"
                                :key="position.id"
                                :value="position.id"
                            >
                                {{ position.name }} ({{ position.election }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label for="name">Candidate Name</Label>
                        <Input id="name" name="name" required />
                    </div>
                    <div>
                        <Label for="biography">Biography</Label>
                        <textarea
                            id="biography"
                            name="biography"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            rows="4"
                        ></textarea>
                    </div>
                    <div>
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div>
                        <Label for="photo">Photo</Label>
                        <Input
                            id="photo"
                            name="photo"
                            type="file"
                            accept="image/*"
                        />
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="showCreateModal = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            Add Candidate
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Edit Candidate</DialogTitle>
                <Form
                    v-if="editingCandidate"
                    v-bind="CandidateController.update.form(editingCandidate.id)"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showEditModal = false"
                    enctype="multipart/form-data"
                >
                    <div>
                        <Label for="edit-position_id">Position</Label>
                        <select
                            id="edit-position_id"
                            name="position_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="">Select Position</option>
                            <option
                                v-for="position in positions"
                                :key="position.id"
                                :value="position.id"
                            >
                                {{ position.name }} ({{ position.election }})
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label for="edit-name">Candidate Name</Label>
                        <Input
                            id="edit-name"
                            name="name"
                            :default-value="editingCandidate.name"
                            required
                        />
                    </div>
                    <div>
                        <Label for="edit-biography">Biography</Label>
                        <textarea
                            id="edit-biography"
                            name="biography"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            rows="4"
                            :default-value="editingCandidate.biography"
                        ></textarea>
                    </div>
                    <div>
                        <Label for="edit-status">Status</Label>
                        <select
                            id="edit-status"
                            name="status"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option
                                value="active"
                                :selected="editingCandidate.status === 'active'"
                            >
                                Active
                            </option>
                            <option
                                value="inactive"
                                :selected="editingCandidate.status === 'inactive'"
                            >
                                Inactive
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label for="edit-photo">Photo</Label>
                        <Input
                            id="edit-photo"
                            name="photo"
                            type="file"
                            accept="image/*"
                        />
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="showEditModal = false"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="processing">
                            Update Candidate
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>


