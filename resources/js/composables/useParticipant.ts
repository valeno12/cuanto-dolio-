import type { PageProps, Participant } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useParticipant() {
    const page = usePage<PageProps>();

    const participant = computed<Participant | null>(() => {
        return page.props.participant;
    });

    const isAdmin = computed(() => {
        return participant.value?.role === 'admin';
    });

    const isMember = computed(() => {
        return participant.value?.role === 'member';
    });

    const isVirtual = computed(() => {
        return participant.value?.role === 'virtual';
    });

    const isAuthenticated = computed(() => {
        return participant.value !== null;
    });

    return {
        participant,
        isAdmin,
        isMember,
        isVirtual,
        isAuthenticated,
    };
}
