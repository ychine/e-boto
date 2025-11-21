<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import VoterController from '@/actions/App/Http/Controllers/Admin/VoterController';
import { Form, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        open: boolean;
        courses: Array<{
            id: number;
            name: string;
        }>;
    }>(),
    {
        open: false,
        courses: () => [],
    },
);

const emit = defineEmits<{
    (event: 'update:open', value: boolean): void;
}>();

const firstName = ref('');
const lastName = ref('');

const internalOpen = computed({
    get: () => props.open,
    set: (value: boolean) => emit('update:open', value),
});

function closeModal(): void
{
    emit('update:open', false);
}

function handleSuccess(): void
{
    firstName.value = '';
    lastName.value = '';
    closeModal();
    router.reload();
}
</script>

<template>
    <Dialog v-model:open="internalOpen">
        <DialogContent class="sm:max-w-2xl">
            <DialogTitle>Register Voter</DialogTitle>
            <Form
                v-bind="VoterController.store.form()"
                v-slot="{ processing, errors }"
                class="space-y-6"
                reset-on-success
                @success="handleSuccess"
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label for="first_name">First Name</Label>
                        <Input
                            id="first_name"
                            name="first_name"
                            v-model="firstName"
                            required
                        />
                        <InputError :message="errors.first_name" />
                    </div>
                    <div>
                        <Label for="last_name">Last Name</Label>
                        <Input
                            id="last_name"
                            name="last_name"
                            v-model="lastName"
                            required
                        />
                        <InputError :message="errors.last_name" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label for="email">Email</Label>
                        <Input id="email" name="email" type="email" required />
                        <InputError :message="errors.email" />
                    </div>
                    <div>
                        <Label for="phone">Phone</Label>
                        <Input id="phone" name="phone" />
                        <InputError :message="errors.phone" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label for="student_id">Student ID</Label>
                        <Input id="student_id" name="student_id" required />
                        <InputError :message="errors.student_id" />
                    </div>
                    <div>
                        <Label for="lrn">LRN</Label>
                        <Input
                            id="lrn"
                            name="lrn"
                            inputmode="numeric"
                            maxlength="12"
                            pattern="\d{12}"
                        />
                        <InputError :message="errors.lrn" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label for="course">Course</Label>
                        <select
                            id="course"
                            name="course"
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
                        <InputError :message="errors.course" />
                    </div>
                    <div>
                        <Label for="section">Section</Label>
                        <Input id="section" name="section" />
                        <InputError :message="errors.section" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <Label for="year_level">Year Level</Label>
                        <Input
                            id="year_level"
                            name="year_level"
                            type="number"
                            min="1"
                            max="4"
                            step="1"
                            required
                        />
                        <InputError :message="errors.year_level" />
                    </div>
                    <div>
                        <Label for="age_group">Age Group</Label>
                        <select
                            id="age_group"
                            name="age_group"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                        >
                            <option value="">Choose</option>
                            <option value="18-20">18-20</option>
                            <option value="21-23">21-23</option>
                            <option value="24-26">24-26</option>
                            <option value="27+">27+</option>
                        </select>
                        <InputError :message="errors.age_group" />
                    </div>
                    <div>
                        <Label for="gender">Gender</Label>
                        <select
                            id="gender"
                            name="gender"
                            class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2"
                        >
                            <option value="">Choose</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError :message="errors.gender" />
                    </div>
                </div>

                <div>
                    <Label for="location">Location</Label>
                    <Input id="location" name="location" />
                    <InputError :message="errors.location" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <Label for="password">Temporary Password</Label>
                        <Input id="password" name="password" type="password" required />
                        <InputError :message="errors.password" />
                    </div>
                    <div>
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                        />
                        <InputError :message="errors.password_confirmation" />
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Button
                        type="button"
                        variant="outline"
                        @click="closeModal"
                    >
                        Cancel
                    </Button>
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Saving...' : 'Register Voter' }}
                    </Button>
                </div>
            </Form>
        </DialogContent>
    </Dialog>
</template>

