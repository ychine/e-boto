<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';

defineProps<{
    logs: {
        data: Array<{
            id: number;
            action: string;
            title: string;
            description: string | null;
            user_name: string;
            created_at: string;
        }>;
        links: any;
        meta: any;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Audit Logs', href: '/admin/audit-logs' },
];
</script>

<template>
    <Head title="Audit Logs" />

    <AdminLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <h1 class="text-3xl font-bold">Audit Logs</h1>

            <div class="rounded-lg border bg-white shadow-sm">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left">Action</th>
                            <th class="px-4 py-3 text-left">Title</th>
                            <th class="px-4 py-3 text-left">Description</th>
                            <th class="px-4 py-3 text-left">User</th>
                            <th class="px-4 py-3 text-left">Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="border-b"
                        >
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800"
                                >
                                    {{ log.action }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ log.title }}</td>
                            <td class="px-4 py-3">
                                {{ log.description || 'N/A' }}
                            </td>
                            <td class="px-4 py-3">{{ log.user_name }}</td>
                            <td class="px-4 py-3">{{ log.created_at }}</td>
                        </tr>
                        <tr v-if="logs.data.length === 0">
                            <td
                                colspan="5"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No audit logs found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.links" class="flex justify-center gap-2">
                <template v-for="link in logs.links" :key="link.label">
                    <button
                        v-if="link.url"
                        @click="
                            $inertia.visit(link.url, {
                                preserveState: true,
                            })
                        "
                        :class="{
                            'bg-primary text-primary-foreground': link.active,
                        }"
                        class="rounded-md border px-3 py-1"
                        v-html="link.label"
                    ></button>
                    <span
                        v-else
                        class="px-3 py-1 text-muted-foreground"
                        v-html="link.label"
                    ></span>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>


