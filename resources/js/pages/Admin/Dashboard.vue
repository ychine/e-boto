<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Head, router } from '@inertiajs/vue3';
import { ref, toRefs } from 'vue';
import type { BreadcrumbItemType } from '@/types';

const props = defineProps<{
    totalVoters: number;
    activeElections: number;
    upcomingElections: Array<{
        id: number;
        title: string;
        starts_at: string;
        ends_at: string | null;
    }>;
    recentActivities: Array<{
        id: number;
        title: string;
        description: string | null;
        user_name: string;
        created_at: string;
    }>;
    yearLevelBreakdown: Array<{
        year_level: string;
        count: number;
    }>;
    pendingApprovals: Array<{
        id: number;
        first_name: string | null;
        last_name: string | null;
        email: string;
        course: string | null;
        year_level: string | null;
        registered_at: string;
    }>;
    pendingApprovalsCount: number;
}>();

const {
    totalVoters,
    activeElections,
    upcomingElections,
    recentActivities,
    yearLevelBreakdown,
    pendingApprovals,
    pendingApprovalsCount,
} = toRefs(props);

const processingUserId = ref<number | null>(null);

function updateStatus(userId: number, status: 'approved' | 'rejected') {
    processingUserId.value = userId;
    router.patch(
        `/admin/voters/${userId}/status`,
        { status },
        {
            preserveScroll: true,
            onFinish: () => {
                processingUserId.value = null;
            },
        },
    );
}

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: '/admin/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <h1 class="text-3xl font-bold">Dashboard</h1>

            <!-- Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
                        Active Elections
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ activeElections }}
                    </div>
                </div>
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="text-sm font-medium text-gray-600">
                        Total Elections
                    </div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        {{ upcomingElections.length }}
                    </div>
                </div>
            </div>

            <!-- Pending Approvals -->
            <div class="rounded-lg border bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold">Pending Approvals</h2>
                        <p class="text-sm text-muted-foreground">
                            {{ pendingApprovalsCount }} waiting for review
                        </p>
                    </div>
                    <TextLink href="/admin/voters">View all voters</TextLink>
                </div>

                <div class="mt-6 space-y-4">
                    <div
                        v-for="pending in pendingApprovals"
                        :key="pending.id"
                        class="flex flex-wrap items-center justify-between gap-4 rounded-md border p-4"
                    >
                        <div>
                            <div class="font-semibold">
                                {{
                                    pending.first_name || pending.last_name
                                        ? [pending.first_name, pending.last_name]
                                              .filter(Boolean)
                                              .join(' ')
                                        : pending.email
                                }}
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ pending.email }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ pending.course || 'No course' }} •
                                {{ pending.year_level || 'No year level' }} •
                                Registered {{ pending.registered_at }}
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Button
                                size="sm"
                                class="bg-green-600 text-white hover:bg-green-700"
                                :disabled="processingUserId === pending.id"
                                @click="updateStatus(pending.id, 'approved')"
                            >
                                {{
                                    processingUserId === pending.id
                                        ? 'Saving...'
                                        : 'Approve'
                                }}
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="border-red-200 text-red-600 hover:bg-red-50"
                                :disabled="processingUserId === pending.id"
                                @click="updateStatus(pending.id, 'rejected')"
                            >
                                Reject
                            </Button>
                        </div>
                    </div>
                    <div
                        v-if="pendingApprovals.length === 0"
                        class="rounded-md border border-dashed py-12 text-center text-muted-foreground"
                    >
                        No pending approvals right now.
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Upcoming Elections -->
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold">
                        Upcoming Elections
                    </h2>
                    <div class="space-y-3">
                        <div
                            v-for="election in upcomingElections"
                            :key="election.id"
                            class="rounded-md border p-4"
                        >
                            <div class="font-medium">{{ election.title }}</div>
                            <div class="mt-1 text-sm text-muted-foreground">
                                Starts: {{ election.starts_at }}
                            </div>
                            <div
                                v-if="election.ends_at"
                                class="text-sm text-muted-foreground"
                            >
                                Ends: {{ election.ends_at }}
                            </div>
                        </div>
                        <div
                            v-if="upcomingElections.length === 0"
                            class="text-center text-muted-foreground"
                        >
                            No upcoming elections
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-xl font-semibold">
                        Recent Activities
                    </h2>
                    <div class="max-h-96 space-y-3 overflow-y-auto">
                        <div
                            v-for="activity in recentActivities"
                            :key="activity.id"
                            class="flex items-start justify-between rounded-md border p-4"
                        >
                            <div class="flex-1">
                                <div class="font-medium">{{ activity.title }}</div>
                                <div
                                    v-if="activity.description"
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    {{ activity.description }}
                                </div>
                                <div class="mt-1 text-xs text-muted-foreground">
                                    by {{ activity.user_name }}
                                </div>
                            </div>
                            <div class="ml-4 text-right text-xs text-muted-foreground">
                                {{ activity.created_at }}
                            </div>
                        </div>
                        <div
                            v-if="recentActivities.length === 0"
                            class="text-center text-muted-foreground"
                        >
                            No recent activities
                        </div>
                    </div>
                </div>
            </div>

            <!-- Year Level Breakdown -->
            <div class="rounded-lg border bg-white p-6 dark:bg-gray-900">
                <h2 class="mb-4 text-xl font-semibold">
                    Voters by Year Level
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="px-4 py-2 text-left">Year Level</th>
                                <th class="px-4 py-2 text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in yearLevelBreakdown"
                                :key="item.year_level"
                                class="border-b"
                            >
                                <td class="px-4 py-2">
                                    {{ item.year_level || 'Not specified' }}
                                </td>
                                <td class="px-4 py-2 text-right">
                                    {{ item.count }}
                                </td>
                            </tr>
                            <tr v-if="yearLevelBreakdown.length === 0">
                                <td
                                    colspan="2"
                                    class="px-4 py-2 text-center text-muted-foreground"
                                >
                                    No data available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

