<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Form, Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { BreadcrumbItemType } from '@/types';
import ElectionController from '@/actions/App/Http/Controllers/Admin/ElectionController';

const props = defineProps<{
    elections: Array<{
        id: number;
        title: string;
        description: string | null;
        start_date: string | null;
        end_date: string | null;
        status: string;
        is_active: boolean;
        created_by: string;
    }>;
    attendanceReport: {
        selectedElectionId: number | null;
        options: Array<{
            id: number;
            title: string;
        }>;
        summary: {
            electionId: number;
            totalRegistered: number;
            attended: number;
            absent: number;
            attendanceRate: number;
            courseBreakdown: Array<{
                course: string;
                count: number;
            }>;
            generatedAt: string;
        } | null;
    };
}>();

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingElection = ref<any>(null);
const selectedElectionId = ref<number | null>(props.attendanceReport.selectedElectionId);
const attendanceSummary = computed(() => props.attendanceReport.summary);
const attendanceOptions = computed(() => props.attendanceReport.options);

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Elections', href: '/admin/elections' },
];

function openEditModal(election: any) {
    editingElection.value = election;
    showEditModal.value = true;
}

function deleteElection(id: number) {
    if (confirm('Are you sure you want to delete this election?')) {
        router.delete(ElectionController.destroy.url(id));
    }
}

watch(
    () => props.attendanceReport.selectedElectionId,
    (value) => {
        selectedElectionId.value = value;
    },
);

watch(
    () => selectedElectionId.value,
    (value, oldValue) => {
        if (!value || value === oldValue) {
            return;
        }

        router.get(
            ElectionController.index.url({
                query: {
                    attendance_election_id: value,
                },
            }),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only: ['attendanceReport'],
            },
        );
    },
);

function downloadAttendanceReport(): void {
    if (!selectedElectionId.value) {
        return;
    }

    window.location.href = ElectionController.exportAttendance.url(selectedElectionId.value);
}
</script>

<template>
    <Head title="Elections Manager" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold">Elections Manager</h1>
                <Button @click="showCreateModal = true">Create New Election</Button>
            </div>

            <div class="rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">Start Date</th>
                            <th class="px-4 py-3 text-left">End Date</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="election in elections"
                            :key="election.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">{{ election.title }}</td>
                            <td class="px-4 py-3">
                                {{ election.description || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ election.start_date || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ election.end_date || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            election.status === 'active',
                                        'bg-yellow-100 text-yellow-800':
                                            election.status === 'upcoming',
                                        'bg-gray-100 text-gray-800': [
                                            'ended',
                                            'inactive',
                                        ].includes(election.status),
                                    }"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                >
                                    {{ election.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ election.created_by }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="openEditModal(election)"
                                    >
                                        Edit
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="deleteElection(election.id)"
                                    >
                                        Delete
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="elections.length === 0">
                            <td
                                colspan="7"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No elections found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg border bg-white p-6 shadow-sm space-y-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">Attendance Monitor</h2>
                        <p class="text-sm text-muted-foreground">
                            Track participation per election and export CSV reports.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select
                            v-model.number="selectedElectionId"
                            class="rounded-md border border-gray-300 px-3 py-2"
                        >
                            <option value="">Select election</option>
                            <option
                                v-for="option in attendanceOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.title }}
                            </option>
                        </select>
                        <Button
                            variant="outline"
                            :disabled="!attendanceSummary"
                            @click="downloadAttendanceReport"
                        >
                            Download CSV
                        </Button>
                    </div>
                </div>

                <div
                    v-if="attendanceSummary"
                    class="grid gap-4 md:grid-cols-3"
                >
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <div class="text-sm text-muted-foreground">Registered Voters</div>
                        <div class="text-3xl font-semibold">
                            {{ attendanceSummary.totalRegistered }}
                        </div>
                    </div>
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <div class="text-sm text-muted-foreground">Checked-In</div>
                        <div class="text-3xl font-semibold">
                            {{ attendanceSummary.attended }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ attendanceSummary.absent }} yet to vote
                        </p>
                    </div>
                    <div class="rounded-lg border bg-gray-50 p-4">
                        <div class="text-sm text-muted-foreground">Attendance Rate</div>
                        <div class="text-3xl font-semibold">
                            {{ attendanceSummary.attendanceRate }}%
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Generated {{ attendanceSummary.generatedAt }}
                        </p>
                    </div>
                </div>

                <div v-if="attendanceSummary" class="overflow-x-auto">
                    <table class="w-full table-fixed">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-2 text-left w-3/4 min-w-[200px]">Course</th>
                                <th class="px-4 py-2 text-right w-1/4 min-w-[120px]">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="course in attendanceSummary.courseBreakdown"
                                :key="course.course"
                                class="border-b"
                            >
                                <td class="px-4 py-2">
                                    {{ course.course }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ course.count }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-else
                    class="text-sm text-muted-foreground"
                >
                    No attendance data for this election yet.
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Dialog v-model:open="showCreateModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Create New Election</DialogTitle>
                <Form
                    v-bind="ElectionController.store.form()"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showCreateModal = false"
                >
                    <div>
                        <Label for="title">Title</Label>
                        <Input id="title" name="title" required />
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
                        <Label for="start_date">Start Date</Label>
                        <Input
                            id="start_date"
                            name="start_date"
                            type="date"
                            required
                        />
                    </div>
                    <div>
                        <Label for="end_date">End Date</Label>
                        <Input
                            id="end_date"
                            name="end_date"
                            type="date"
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
                            Create Election
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>

        <!-- Edit Modal -->
        <Dialog v-model:open="showEditModal">
            <DialogContent class="sm:max-w-md">
                <DialogTitle>Edit Election</DialogTitle>
                <Form
                    v-if="editingElection"
                    v-bind="ElectionController.update.form(editingElection.id)"
                    v-slot="{ processing }"
                    class="space-y-4"
                    @success="showEditModal = false"
                >
                    <div>
                        <Label for="edit-title">Title</Label>
                        <Input
                            id="edit-title"
                            name="title"
                            :default-value="editingElection.title"
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
                            :default-value="editingElection.description"
                        ></textarea>
                    </div>
                    <div>
                        <Label for="edit-start_date">Start Date</Label>
                        <Input
                            id="edit-start_date"
                            name="start_date"
                            type="date"
                            :default-value="editingElection.start_date"
                            required
                        />
                    </div>
                    <div>
                        <Label for="edit-end_date">End Date</Label>
                        <Input
                            id="edit-end_date"
                            name="end_date"
                            type="date"
                            :default-value="editingElection.end_date"
                            required
                        />
                    </div>
                    <div>
                        <Label for="edit-is_active">Status</Label>
                        <select
                            id="edit-is_active"
                            name="is_active"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                            :value="editingElection.is_active ? '1' : '0'"
                        >
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Inactive elections will not appear on the voter dashboard
                        </p>
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
                            Update Election
                        </Button>
                    </div>
                </Form>
            </DialogContent>
        </Dialog>
    </AdminLayout>
</template>

