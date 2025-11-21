<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import VoterCredentialModal from '@/components/VoterCredentialModal.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, toRefs } from 'vue';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps<{
    voters: Array<{
        id: number;
        first_name: string | null;
        last_name: string | null;
        email: string;
        student_id: string | null;
        lrn: string | null;
        phone: string | null;
        course: string | null;
        section: string | null;
        year_level: string | null;
        age_group: string | null;
        gender: string | null;
        location: string | null;
        status: string;
        times_voted: number;
        last_login: string | null;
        registered_date: string;
    }>;
    totalVoters: number;
    courses: Array<{
        id: number;
        name: string;
    }>;
}>();

const showEditModal = ref(false);
const showCredentialModal = ref(false);
const editingVoter = ref<any>(null);
const { voters, totalVoters, courses } = toRefs(props);

const editForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    student_id: '',
    lrn: '',
    phone: '',
    course: '',
    section: '',
    year_level: 1,
    age_group: '',
    gender: '',
    location: '',
    status: 'pending',
});

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Voters', href: '/admin/voters' },
];

function openEditModal(voter: any) {
    editingVoter.value = voter;
    editForm.clearErrors();
    editForm.first_name = voter.first_name ?? '';
    editForm.last_name = voter.last_name ?? '';
    editForm.email = voter.email ?? '';
    editForm.student_id = voter.student_id ?? '';
    editForm.lrn = voter.lrn ?? '';
    editForm.phone = voter.phone ?? '';
    editForm.course = voter.course ?? '';
    editForm.section = voter.section ?? '';
    editForm.year_level = voter.year_level ? Number(voter.year_level) : 1;
    editForm.age_group = voter.age_group ?? '';
    editForm.gender = voter.gender ?? '';
    editForm.location = voter.location ?? '';
    editForm.status = voter.status ?? 'pending';
    showEditModal.value = true;
}

function closeEditModal() {
    showEditModal.value = false;
    editingVoter.value = null;
    editForm.reset();
    editForm.clearErrors();
}

function submitEdit() {
    if (!editingVoter.value) {
        return;
    }

    editForm.transform((data) => ({
        ...data,
        year_level: Number(data.year_level),
    })).put(`/admin/voters/${editingVoter.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
}
</script>

<template>
    <Head title="Manage Voters" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Manage Voters</h1>
                    <p class="text-sm text-muted-foreground">
                        Create and review registered voters. Only administrators can add accounts.
                    </p>
                </div>
                <Button @click="showCredentialModal = true">
                    Register Voter
                </Button>
            </div>

            <div class="rounded-lg border bg-white p-6 shadow-sm">
                <div class="text-sm font-medium text-gray-600">
                    Total Voters
                </div>
                <div class="mt-2 text-3xl font-bold text-gray-900">
                    {{ totalVoters }}
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
                            <th class="px-4 py-3 text-left">Course</th>
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
                                {{ voter.course || 'N/A' }}
                            </td>
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
                <form v-if="editingVoter" class="space-y-4" @submit.prevent="submitEdit">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-first_name">First Name</Label>
                            <Input
                                id="edit-first_name"
                                v-model="editForm.first_name"
                                required
                            />
                            <InputError :message="editForm.errors.first_name" />
                        </div>
                        <div>
                            <Label for="edit-last_name">Last Name</Label>
                            <Input
                                id="edit-last_name"
                                v-model="editForm.last_name"
                                required
                            />
                            <InputError :message="editForm.errors.last_name" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-email">Email</Label>
                            <Input
                                id="edit-email"
                                type="email"
                                v-model="editForm.email"
                                required
                            />
                            <InputError :message="editForm.errors.email" />
                        </div>
                        <div>
                            <Label for="edit-phone">Phone</Label>
                            <Input id="edit-phone" v-model="editForm.phone" />
                            <InputError :message="editForm.errors.phone" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-student_id">Student ID</Label>
                            <Input
                                id="edit-student_id"
                                v-model="editForm.student_id"
                                required
                            />
                            <InputError :message="editForm.errors.student_id" />
                        </div>
                        <div>
                            <Label for="edit-lrn">LRN</Label>
                            <Input
                                id="edit-lrn"
                                v-model="editForm.lrn"
                                inputmode="numeric"
                                maxlength="12"
                                pattern="\d{12}"
                            />
                            <InputError :message="editForm.errors.lrn" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-course">Course</Label>
                            <select
                                id="edit-course"
                                v-model="editForm.course"
                                class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                                required
                            >
                                <option value="">Select course</option>
                                <option
                                    v-for="course in courses"
                                    :key="course.id"
                                    :value="course.name"
                                >
                                    {{ course.name }}
                                </option>
                            </select>
                            <InputError :message="editForm.errors.course" />
                        </div>
                        <div>
                            <Label for="edit-section">Section</Label>
                            <Input id="edit-section" v-model="editForm.section" />
                            <InputError :message="editForm.errors.section" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-year_level">Year Level</Label>
                            <Input
                                id="edit-year_level"
                                type="number"
                                min="1"
                                max="4"
                                step="1"
                                v-model.number="editForm.year_level"
                                required
                            />
                            <InputError :message="editForm.errors.year_level" />
                        </div>
                        <div>
                            <Label for="edit-age_group">Age Group</Label>
                            <Input id="edit-age_group" v-model="editForm.age_group" />
                            <InputError :message="editForm.errors.age_group" />
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label for="edit-gender">Gender</Label>
                            <Input id="edit-gender" v-model="editForm.gender" />
                            <InputError :message="editForm.errors.gender" />
                        </div>
                        <div>
                            <Label for="edit-location">Location</Label>
                            <Input id="edit-location" v-model="editForm.location" />
                            <InputError :message="editForm.errors.location" />
                        </div>
                    </div>
                    <div>
                        <Label for="edit-status">Status</Label>
                        <select
                            id="edit-status"
                            v-model="editForm.status"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                            required
                        >
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <InputError :message="editForm.errors.status" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="closeEditModal"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <VoterCredentialModal v-model:open="showCredentialModal" :courses="courses" />
    </AdminLayout>
</template>


