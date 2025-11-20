<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import SimpleToast from '@/components/SimpleToast.vue';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps<{
    canRegister: boolean;
    currentElection?: {
        id: number;
        title: string;
        is_active: boolean;
    } | null;
}>();

const showAdminLogin = ref(false);
const showStudentLogin = ref(false);
const showRegister = ref(false);
const loginMode = ref<'admin' | 'student'>('student');
const showToast = ref(false);
const toastMessage = ref('');
const registerFirstName = ref('');
const registerLastName = ref('');

const registerFullName = computed(() => {
    if (registerFirstName.value && registerLastName.value) {
        return `${registerFirstName.value} ${registerLastName.value}`;
    }

    return registerFirstName.value || registerLastName.value || '';
});

const page = usePage();

function triggerToast(message: string) {
    if (!message) {
        return;
    }

    showToast.value = false;
    nextTick(() => {
        toastMessage.value = message;
        showToast.value = true;
    });
}

// Watch for login errors
watch(
    () => page.props.errors,
    (errors) => {
        if (errors && (errors.email || errors.password)) {
            const errorMsg = errors.email
                ? Array.isArray(errors.email)
                    ? errors.email[0]
                    : errors.email
                : errors.password
                    ? Array.isArray(errors.password)
                        ? errors.password[0]
                        : errors.password
                    : 'Login failed';
            triggerToast(errorMsg);
        }
    },
    { deep: true, immediate: true },
);

watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.error) {
            triggerToast(
                Array.isArray(flash.error) ? flash.error[0] : String(flash.error),
            );
        }
    },
    { deep: true, immediate: true },
);

const electionStatus = computed(() => {
    if (!props.currentElection) {
        return 'No elections right now';
    }
    return props.currentElection.is_active
        ? `Current elections right now: ${props.currentElection.title}`
        : 'No elections right now';
});

function openLogin(mode: 'admin' | 'student') {
    loginMode.value = mode;
    if (mode === 'admin') {
        showAdminLogin.value = true;
    } else {
        showStudentLogin.value = true;
    }
}

// Forms will be handled by Inertia Form component
</script>

<template>
    <Head title="e-Boto - Supreme Student Council Voting">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div
        class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-cover bg-center bg-no-repeat p-6"
        style="background-image: url('/images/santiagobldg.png');"
    >
        <!-- Green overlay -->
        <div
            class="absolute inset-0 bg-gradient-to-br from-green-900/60 to-green-800/40"
        ></div>

        <!-- Content -->
        <div class="relative z-10 w-full max-w-4xl">
            <!-- Header with Logo -->
            <header class="mb-8 flex flex-col items-center gap-4 text-center">
                <img
                    src="/images/FINAL_SEAL.png"
                    alt="ISU Logo"
                    class="h-24 w-24 rounded-full bg-white p-2 shadow-lg"
                />
                <div>
                    <h1 class="text-4xl font-bold text-white drop-shadow-lg">
                        e-Boto
                    </h1>
                    <p class="mt-2 text-xl text-white/90 drop-shadow-md">
                        Supreme Student Council Voting
                    </p>
                </div>
            </header>

            <!-- Main Panel -->
            <div
                class="rounded-2xl bg-white/95 p-8 shadow-2xl backdrop-blur-sm dark:bg-gray-900/95"
            >
                <!-- Welcome Text -->
                <div class="mb-8 text-center">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        Welcome
                    </h2>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Choose how you want to sign in:
                    </p>
                </div>

                <!-- Login Buttons -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <Button
                        @click="openLogin('admin')"
                        class="h-14 bg-green-600 px-8 text-lg font-semibold hover:bg-green-700"
                    >
                        Admin Login
                    </Button>
                    <Button
                        @click="openLogin('student')"
                        class="h-14 bg-yellow-500 px-8 text-lg font-semibold hover:bg-yellow-600"
                    >
                        Student Login
                    </Button>
                </div>

                <!-- Election Status -->
                <div
                    class="rounded-lg border-2 border-gray-300 bg-gray-50 p-4 text-center dark:border-gray-700 dark:bg-gray-800"
                >
                    <p class="font-medium text-gray-700 dark:text-gray-300">
                        {{ electionStatus }}
                    </p>
                </div>

                <!-- Sign Up Link -->
                <div class="mt-6 text-center">
                    <button
                        v-if="canRegister"
                        @click="showRegister = true"
                        class="text-sm text-green-600 hover:underline dark:text-green-400"
                    >
                        Don't have an account? Sign Up
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Login Modal -->
    <Dialog v-model:open="showAdminLogin">
        <DialogContent class="sm:max-w-md">
            <DialogTitle>Admin Login</DialogTitle>
            <Form
                action="/login"
                method="post"
                @success="() => { 
                    showAdminLogin = false;
                }"
                @error="() => {
                    // Errors are handled by the watch function
                }"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="admin-email">Email / Username</Label>
                    <Input
                        id="admin-email"
                        type="email"
                        name="email"
                        required
                        autofocus
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                        {{ errors.email }}
                    </p>
                </div>
                <div>
                    <Label for="admin-password">Password</Label>
                    <Input
                        id="admin-password"
                        type="password"
                        name="password"
                        required
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showAdminLogin = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <span v-if="processing">Logging in...</span>
                        <span v-else>Login</span>
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Student Login Modal -->
    <Dialog v-model:open="showStudentLogin">
        <DialogContent class="sm:max-w-md">
            <DialogTitle>Student Login</DialogTitle>
            <Form
                action="/login"
                method="post"
                @success="() => { 
                    showStudentLogin = false;
                }"
                @error="() => {
                    // Errors are handled by the watch function
                }"
                v-slot="{ processing, errors }"
                class="space-y-4"
            >
                <div>
                    <Label for="student-email">Email / Username</Label>
                    <Input
                        id="student-email"
                        type="email"
                        name="email"
                        required
                        autofocus
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                        {{ errors.email }}
                    </p>
                </div>
                <div>
                    <Label for="student-password">Password</Label>
                    <Input
                        id="student-password"
                        type="password"
                        name="password"
                        required
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showStudentLogin = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        <span v-if="processing">Logging in...</span>
                        <span v-else>Login</span>
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Register Modal -->
    <Dialog v-model:open="showRegister">
        <DialogContent class="sm:max-w-lg">
            <DialogTitle>Sign Up</DialogTitle>
            <Form
                action="/register"
                method="post"
                @submit="() => { showRegister = false; }"
                v-slot="{ processing }"
                class="space-y-4"
            >
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="first_name">First Name</Label>
                        <Input
                            id="first_name"
                            name="first_name"
                            type="text"
                            required
                            v-model="registerFirstName"
                        />
                    </div>
                    <div>
                        <Label for="last_name">Last Name</Label>
                        <Input
                            id="last_name"
                            name="last_name"
                            type="text"
                            required
                            v-model="registerLastName"
                        />
                    </div>
                </div>
                <input type="hidden" name="name" :value="registerFullName" />
                <div>
                    <Label for="register-email">Email</Label>
                    <Input
                        id="register-email"
                        name="email"
                        type="email"
                        required
                    />
                </div>
                <div>
                    <Label for="register-password">Password</Label>
                    <Input
                        id="register-password"
                        name="password"
                        type="password"
                        required
                    />
                </div>
                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                    />
                </div>
                <div>
                    <Label for="role">Role</Label>
                    <select
                        id="role"
                        name="role"
                        class="w-full rounded-md border border-gray-300 px-3 py-2"
                    >
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="year_level">Year Level</Label>
                        <Input
                            id="year_level"
                            name="year_level"
                            type="number"
                            min="1"
                            max="4"
                            step="1"
                            inputmode="numeric"
                        />
                    </div>
                    <div>
                        <Label for="age_group">Age Group</Label>
                        <select
                            id="age_group"
                            name="age_group"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                        >
                            <option value="">Select</option>
                            <option value="18-20">18-20</option>
                            <option value="21-23">21-23</option>
                            <option value="24-26">24-26</option>
                            <option value="27+">27+</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <Label for="gender">Gender</Label>
                        <select
                            id="gender"
                            name="gender"
                            class="w-full rounded-md border border-gray-300 px-3 py-2"
                        >
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <Label for="location">Location</Label>
                        <Input
                            id="location"
                            name="location"
                            type="text"
                        />
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showRegister = false"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Creating Account...' : 'Create Account' }}
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>

    <!-- Simple Toast for Login Errors -->
    <SimpleToast v-if="showToast" :message="toastMessage" type="error" />
</template>

