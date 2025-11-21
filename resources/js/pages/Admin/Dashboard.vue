<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import VoterCredentialModal from '@/components/VoterCredentialModal.vue';
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
    courses: Array<{
        id: number;
        name: string;
    }>;
}>();

const { totalVoters, activeElections, upcomingElections, recentActivities, yearLevelBreakdown, courses } =
    toRefs(props);

const showCredentialModal = ref(false);

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
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h1 class="text-3xl font-bold">Dashboard</h1>
                <Button @click="showCredentialModal = true">Register Voter</Button>
            </div>

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

            <div class="rounded-lg border bg-white p-6 shadow-sm">
                <h2 class="text-xl font-semibold">Voter Registration</h2>
                <p class="mt-2 text-sm text-muted-foreground">
                    Admins can add voters directly. This bypasses public sign-ups and keeps records tidy.
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <Button @click="showCredentialModal = true">Register Voter</Button>
                    <Button variant="outline" @click="router.visit('/admin/voters')">
                        Manage Voters
                    </Button>
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

        <VoterCredentialModal
            v-model:open="showCredentialModal"
            :courses="courses"
        />
    </AdminLayout>
</template>

