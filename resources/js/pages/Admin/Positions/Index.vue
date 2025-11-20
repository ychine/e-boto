<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItemType } from '@/types';
import PositionController from '@/actions/App/Http/Controllers/Admin/PositionController';

defineProps<{
    positions: Array<{
        id: number;
        name: string;
        election: string;
        description: string | null;
        max_votes: number;
    }>;
    elections: Array<{
        id: number;
        title: string;
    }>;
}>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingPosition = ref<any>(null);

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Positions', href: '/admin/positions' },
];

function openEditModal(position: any) {
    editingPosition.value = position;
    showEditModal.value = true;
}

function deletePosition(id: number) {
    if (confirm('Are you sure you want to delete this position?')) {
        router.delete(PositionController.destroy.url(id));
    }
}
</script>

<template>
    <Head title="Manage Positions" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Manage Positions</h1>
                <Button @click="showCreateModal = true">Add Position</Button>
            </div>

            <div class="rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">Position Name</th>
                            <th class="px-4 py-3 text-left">Election</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Max Votes Allowed</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="position in positions"
                            :key="position.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">{{ position.name }}</td>
                            <td class="px-4 py-3">{{ position.election }}</td>
                            <td class="px-4 py-3">
                                {{ position.description || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">{{ position.max_votes }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openEditModal(position)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deletePosition(position.id)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="positions.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No positions found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Add Position</DialogTitle>
                <Form
                    v-bind="PositionController.store.form()"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showCreateModal = false"
                >
                    <div>
                        <Label for="election_id">Election</Label>
                        <select
                            id="election_id"
                            name="election_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="">Select Election</option>
                            <option
                                v-for="election in elections"
                                :key="election.id"
                                :value="election.id"
                            >
                                {{ election.title }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label for="name">Position Name</Label>
                        <Input id="name" name="name" required />
                    </div>
                    <div>
                        <Label for="description">Description</Label>
                        <textarea
                            id="description"
                            name="description"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            rows="3"
                        ></textarea>
                    </div>
                    <div>
                        <Label for="max_votes">Max Votes Allowed</Label>
                        <Input
                            id="max_votes"
                            name="max_votes"
                            type="number"
                            min="1"
                            :default-value="1"
                            required
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
                            Add Position
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Edit Position</DialogTitle>
                <Form
                    v-if="editingPosition"
                    v-bind="PositionController.update.form(editingPosition.id)"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showEditModal = false"
                >
                    <div>
                        <Label for="edit-election_id">Election</Label>
                        <select
                            id="edit-election_id"
                            name="election_id"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="">Select Election</option>
                            <option
                                v-for="election in elections"
                                :key="election.id"
                                :value="election.id"
                            >
                                {{ election.title }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <Label for="edit-name">Position Name</Label>
                        <Input
                            id="edit-name"
                            name="name"
                            :default-value="editingPosition.name"
                            required
                        />
                    </div>
                    <div>
                        <Label for="edit-description">Description</Label>
                        <textarea
                            id="edit-description"
                            name="description"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                            rows="3"
                            :default-value="editingPosition.description"
                        ></textarea>
                    </div>
                    <div>
                        <Label for="edit-max_votes">Max Votes Allowed</Label>
                        <Input
                            id="edit-max_votes"
                            name="max_votes"
                            type="number"
                            min="1"
                            :default-value="editingPosition.max_votes"
                            required
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
                            Update Position
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>


