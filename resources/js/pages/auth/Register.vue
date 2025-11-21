<script setup lang="ts">
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <AuthBase
        title="Registration Unavailable"
        description="Only administrators can create new voter accounts."
    >
        <Head title="Registration Disabled" />

        <div class="rounded-lg border bg-white p-6 text-center shadow-sm">
            <p class="text-muted-foreground">
                Public registration has been disabled. Please contact an administrator if you need access.
            </p>
            <Link
                href="/"
                class="mt-4 inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white"
            >
                Return Home
            </Link>
        </div>
    </AuthBase>
</template>

