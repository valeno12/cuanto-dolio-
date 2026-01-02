import type { Expense, Participant, Room } from '@/types';
import { router } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { onMounted, onUnmounted, type Ref } from 'vue';

// Make Pusher available globally for Echo
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo;
    }
}

window.Pusher = Pusher;

// Initialize Echo (singleton)
let echo: Echo | null = null;

function getEcho(): Echo {
    if (!echo) {
        echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }
    return echo;
}

interface UseRoomChannelOptions {
    room: Ref<Room> | Room;
    onExpenseCreated?: (expense: Expense) => void;
    onExpenseUpdated?: (expense: Expense) => void;
    onExpenseDeleted?: (expenseId: string) => void;
    onParticipantJoined?: (participant: Participant) => void;
}

export function useRoomChannel(options: UseRoomChannelOptions) {
    const getRoomId = () => {
        const room = 'value' in options.room ? options.room.value : options.room;
        return room.id;
    };

    let channelName: string;

    onMounted(() => {
        const roomId = getRoomId();
        channelName = `room.${roomId}`;

        const channel = getEcho().private(channelName);

        // Listen for expense events
        channel
            .listen('ExpenseCreated', (data: { expense: Expense }) => {
                if (options.onExpenseCreated) {
                    options.onExpenseCreated(data.expense);
                } else {
                    // Default: reload page data via Inertia
                    router.reload({ only: ['room'] });
                }
            })
            .listen('ExpenseUpdated', (data: { expense: Expense }) => {
                if (options.onExpenseUpdated) {
                    options.onExpenseUpdated(data.expense);
                } else {
                    router.reload({ only: ['room'] });
                }
            })
            .listen('ExpenseDeleted', (data: { expenseId: string }) => {
                if (options.onExpenseDeleted) {
                    options.onExpenseDeleted(data.expenseId);
                } else {
                    router.reload({ only: ['room'] });
                }
            })
            .listen('ParticipantJoined', (data: { participant: Participant }) => {
                if (options.onParticipantJoined) {
                    options.onParticipantJoined(data.participant);
                } else {
                    router.reload({ only: ['room'] });
                }
            });
    });

    onUnmounted(() => {
        if (channelName) {
            getEcho().leave(channelName);
        }
    });
}
