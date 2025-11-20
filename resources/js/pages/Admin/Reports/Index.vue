<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps<{
    elections: Array<{
        id: number;
        title: string;
    }>;
    stats: {
        total_voters: number;
        total_votes: number;
        voter_turnout: number;
    };
    selectedElectionId?: number | null;
}>();

const selectedElection = ref(props.selectedElectionId);

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reports', href: '/admin/reports' },
];

function filterByElection() {
    router.get('/admin/reports', {
        election_id: selectedElection.value || null,
    });
}
</script>

<template>
    <Head title="Reports" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <h1 class="text-3xl font-bold">Reports</h1>

            <!-- Election Filter -->
            <div class="rounded-lg border bg-white p-4 dark:bg-gray-900">
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <Label for="election_filter">Filter by Election</Label>
                        <select
                            id="election_filter"
                            v-model="selectedElection"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                            @change="filterByElection"
                        >
                            <option :value="null">All Elections</option>
                            <option
                                v-for="election in elections"
                                :key="election.id"
                                :value="election.id"
                            >
                                {{ election.title }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-lg border bg-green-50 p-6 dark:bg-green-950/20"
                >
                    <div class="text-sm font-medium text-green-600 dark:text-green-400">
                        Total Voters
                    </div>
                    <div class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">
                        {{ stats.total_voters }}
                    </div>
                </div>
                <div
                    class="rounded-lg border bg-green-50 p-6 dark:bg-green-950/20"
                >
                    <div
                        class="text-sm font-medium text-green-600 dark:text-green-400"
                    >
                        Total Votes
                    </div>
                    <div
                        class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100"
                    >
                        {{ stats.total_votes }}
                    </div>
                </div>
                <div
                    class="rounded-lg border bg-purple-50 p-6 dark:bg-purple-950/20"
                >
                    <div
                        class="text-sm font-medium text-purple-600 dark:text-purple-400"
                    >
                        Voter Turnout
                    </div>
                    <div
                        class="mt-2 text-3xl font-bold text-purple-900 dark:text-purple-100"
                    >
                        {{ stats.voter_turnout }}%
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>




