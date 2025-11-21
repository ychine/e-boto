<script setup lang="ts">
import SimpleToast from '@/components/SimpleToast.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { bulk as voteBulkRoute } from '@/routes/votes';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { BreadcrumbItemType } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Candidate {
    id: number;
    name: string;
    photo: string | null;
}

interface Position {
    id: number;
    name: string;
    max_votes: number;
    has_voted: boolean;
    current_vote: {
        candidate_name: string | null;
        cast_at: string;
    } | null;
    candidates: Candidate[];
}

interface ActiveElection {
    id: number;
    title: string;
    description: string | null;
    starts_at: string | null;
    ends_at: string | null;
    positions: Position[];
}

interface VoteHistoryRow {
    id: number;
    election: string;
    position: string;
    candidate: string;
    cast_at: string;
}

interface HistoryStats {
    totalVotes: number;
    electionsParticipated: number;
}

const props = defineProps<{
    activeElections: ActiveElection[];
    votingHistoryPreview: VoteHistoryRow[];
    historyStats: HistoryStats;
    fullHistory: VoteHistoryRow[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const voteModalOpen = ref(false);
const selectedElection = ref<ActiveElection | null>(null);
const selectedCandidateByPosition = ref<Record<number, number | null>>({});

const voteForm = useForm({
    election_id: null as number | null,
    votes: [] as Array<{ position_id: number; candidate_id: number }>,
});

const page = usePage<{ flash?: { success?: string } }>();
const showToast = ref(false);
const toastMessage = ref('');

watch(
    () => page.props.flash?.success,
    (message) => {
        if (message) {
            toastMessage.value = message;
            showToast.value = true;
            setTimeout(() => {
                showToast.value = false;
            }, 3500);
        }
    },
    { immediate: true },
);

const hasActiveElections = computed(
    () => props.activeElections.length > 0,
);

const selectedCount = computed(() =>
    Object.values(selectedCandidateByPosition.value).filter(Boolean).length,
);

const formatDateTime = (value?: string | null): string => {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short',
    });
};

function openVoteModal(election: ActiveElection) {
    selectedElection.value = election;
    selectedCandidateByPosition.value = election.positions.reduce(
        (acc, position) => {
            acc[position.id] = null;
            return acc;
        },
        {} as Record<number, number | null>,
    );
    voteModalOpen.value = true;
}

function closeVoteModal() {
    voteModalOpen.value = false;
    selectedElection.value = null;
    selectedCandidateByPosition.value = {};
    voteForm.reset();
}

function selectCandidate(positionId: number, candidateId: number) {
    selectedCandidateByPosition.value = {
        ...selectedCandidateByPosition.value,
        [positionId]: candidateId,
    };
}

function submitVotes() {
    const election = selectedElection.value;
    if (!election) {
        return;
    }

    const votes = Object.entries(selectedCandidateByPosition.value)
        .filter(([, candidateId]) => Boolean(candidateId))
        .map(([positionId, candidateId]) => ({
            position_id: Number(positionId),
            candidate_id: candidateId as number,
        }));

    if (votes.length === 0) {
        return;
    }

    voteForm.election_id = election.id;
    voteForm.votes = votes;

    voteForm.post(voteBulkRoute().url, {
        preserveScroll: true,
        onSuccess: () => {
            closeVoteModal();
        },
    });
}

const modalTitle = computed(() =>
    selectedElection.value
        ? `Vote - ${selectedElection.value.title}`
        : 'Vote',
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-10 p-4">
            <!-- Section 1: Dashboard -->
            <section class="space-y-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-2xl font-semibold text-foreground">
                        Dashboard
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Participate in ongoing elections and review your recent votes.
                    </p>
                </div>

                <div
                    v-if="hasActiveElections"
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                >
                    <Card
                        v-for="election in activeElections"
                        :key="election.id"
                        class="flex flex-col justify-between"
                    >
                        <CardHeader>
                            <CardTitle class="text-lg">
                                {{ election.title }}
                            </CardTitle>
                            <p class="text-sm text-muted-foreground">
                                {{ election.description || 'No additional details' }}
                            </p>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm text-muted-foreground">
                            <p>
                                Starts:
                                <span class="font-medium text-foreground">
                                    {{ formatDateTime(election.starts_at) }}
                                </span>
                            </p>
                            <p>
                                Ends:
                                <span class="font-medium text-foreground">
                                    {{ formatDateTime(election.ends_at) }}
                                </span>
                            </p>
                            <p>
                                Positions:
                                <span class="font-medium text-foreground">
                                    {{ election.positions.length }}
                                </span>
                            </p>
                            <Button
                                class="w-full"
                                @click="openVoteModal(election)"
                            >
                                Vote
                            </Button>
                        </CardContent>
                    </Card>
                </div>
                <div
                    v-else
                    class="rounded-lg border border-dashed border-border p-6 text-center text-muted-foreground"
                >
                    There are no active elections right now. Please check back later.
                </div>

                <!-- Voting history preview -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Voting History Preview</h3>
                        <p class="text-sm text-muted-foreground">
                            Showing your 5 most recent votes
                        </p>
                    </div>
                    <div class="overflow-hidden rounded-lg border">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-muted/40 text-xs uppercase tracking-wide text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">Election</th>
                                    <th class="px-4 py-3">Position</th>
                                    <th class="px-4 py-3">Candidate</th>
                                    <th class="px-4 py-3 text-right">Cast At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="row in votingHistoryPreview"
                                    :key="row.id"
                                    class="border-t"
                                >
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ row.election }}
                                    </td>
                                    <td class="px-4 py-3">{{ row.position }}</td>
                                    <td class="px-4 py-3">{{ row.candidate }}</td>
                                    <td class="px-4 py-3 text-right">
                                        {{ formatDateTime(row.cast_at) }}
                                    </td>
                                </tr>
                                <tr v-if="votingHistoryPreview.length === 0">
                                    <td
                                        colspan="4"
                                        class="px-4 py-6 text-center text-muted-foreground"
                                    >
                                        No votes recorded yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Section 2: My Voting History -->
            <section class="space-y-6">
                <div class="flex flex-col gap-2">
                    <h2 class="text-2xl font-semibold text-foreground">
                        My Voting History
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Track your participation and review past ballots.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <Card class="border border-green-200 bg-green-50 text-green-900 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-50">
                        <CardHeader>
                            <CardTitle class="text-base font-medium">
                                Total Votes Cast
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-4xl font-bold">
                                {{ historyStats.totalVotes }}
                            </p>
                        </CardContent>
                    </Card>
                    <Card class="border border-green-200 bg-green-50 text-green-900 dark:border-green-900/40 dark:bg-green-900/30 dark:text-green-50">
                        <CardHeader>
                            <CardTitle class="text-base font-medium">
                                Elections Participated
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-4xl font-bold">
                                {{ historyStats.electionsParticipated }}
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div class="overflow-hidden rounded-lg border">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-muted/40 text-xs uppercase tracking-wide text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">Election</th>
                                <th class="px-4 py-3">Position</th>
                                <th class="px-4 py-3">Candidate</th>
                                <th class="px-4 py-3 text-right">Cast At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in fullHistory"
                                :key="row.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3 font-medium text-foreground">
                                    {{ row.election }}
                                </td>
                                <td class="px-4 py-3">{{ row.position }}</td>
                                <td class="px-4 py-3">{{ row.candidate }}</td>
                                <td class="px-4 py-3 text-right">
                                    {{ formatDateTime(row.cast_at) }}
                                </td>
                            </tr>
                            <tr v-if="fullHistory.length === 0">
                                <td
                                    colspan="4"
                                    class="px-4 py-6 text-center text-muted-foreground"
                                >
                                    You have not participated in any elections yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <!-- Vote modal -->
        <Dialog
            :open="voteModalOpen"
            @update:open="(value) => {
                if (!value) {
                    closeVoteModal();
                }
            }"
        >
            <DialogContent class="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>{{ modalTitle }}</DialogTitle>
                    <DialogDescription>
                        Choose a candidate for each position. You can vote once per position.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="selectedElection" class="space-y-6">
                    <div
                        v-for="position in selectedElection.positions"
                        :key="position.id"
                        class="rounded-lg border p-4"
                    >
                        <div class="mb-3 flex flex-col gap-1 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-base font-semibold">
                                    {{ position.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Max votes: {{ position.max_votes }}
                                </p>
                            </div>
                            <p
                                v-if="position.has_voted && position.current_vote"
                                class="text-sm font-medium text-green-600 dark:text-green-400"
                            >
                                You voted for {{ position.current_vote.candidate_name }} on
                                {{ formatDateTime(position.current_vote.cast_at) }}
                            </p>
                        </div>

                        <div
                            v-if="!position.has_voted"
                            class="grid gap-3 md:grid-cols-2"
                        >
                            <div
                                v-for="candidate in position.candidates"
                                :key="candidate.id"
                                class="flex flex-col gap-3 rounded-lg border p-3"
                                :class="{
                                    'border-primary':
                                        selectedCandidateByPosition[position.id] === candidate.id,
                                }"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center overflow-hidden rounded-full bg-muted"
                                    >
                                        <img
                                            v-if="candidate.photo"
                                            :src="candidate.photo"
                                            :alt="candidate.name"
                                            class="h-12 w-12 object-cover"
                                        />
                                        <span v-else class="text-sm font-semibold">
                                            {{ candidate.name.slice(0, 2).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-foreground">
                                            {{ candidate.name }}
                                        </p>
                                    </div>
                                </div>

                                <Button
                                    variant="outline"
                                    class="w-full"
                                    :disabled="voteForm.processing"
                                    @click="selectCandidate(position.id, candidate.id)"
                                >
                                    {{
                                        selectedCandidateByPosition[position.id] === candidate.id
                                            ? 'Selected'
                                            : 'Select'
                                    }}
                                </Button>
                            </div>
                        </div>

                        <div v-else class="rounded-md bg-muted/40 p-3 text-sm text-muted-foreground">
                            You have already voted for this position.
                        </div>

                    </div>
                </div>

                <div v-if="selectedElection" class="border-t pt-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <p class="text-sm text-muted-foreground">
                            {{
                                selectedCount > 0
                                    ? `${selectedCount} position${selectedCount > 1 ? 's' : ''} selected`
                                    : 'Select candidates to enable voting'
                            }}
                        </p>
                        <Button
                            class="w-full md:w-auto"
                            :disabled="voteForm.processing || selectedCount === 0"
                            @click="submitVotes"
                        >
                            {{
                                voteForm.processing
                                    ? 'Submitting...'
                                    : `Cast ${selectedCount || ''} Vote${selectedCount === 1 ? '' : 's'}`
                            }}
                        </Button>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="closeVoteModal">Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <SimpleToast
            v-if="showToast"
            :message="toastMessage"
            type="success"
        />
    </AppLayout>
</template>
