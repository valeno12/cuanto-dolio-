import type { Expense, Participant, Room } from '@/types';
import { router } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { onMounted, onUnmounted, type Ref } from 'vue';

// Make Pusher available globally for Echo
declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: InstanceType<typeof Echo>;
    }
}

window.Pusher = Pusher;

// Initialize Echo (singleton)
let echo: InstanceType<typeof Echo> | null = null;
let echoFailed = false;

function getEcho(): InstanceType<typeof Echo> | null {
    // If we already failed or don't have the key, don't try again
    if (echoFailed) {
        return null;
    }

    const appKey = import.meta.env.VITE_REVERB_APP_KEY;
    
    if (!appKey) {
        console.warn('[CuantoDolio] VITE_REVERB_APP_KEY not configured. Real-time updates disabled.');
        echoFailed = true;
        return null;
    }

    if (!echo) {
        try {
            echo = new Echo({
                broadcaster: 'reverb',
                key: appKey,
                wsHost: import.meta.env.VITE_REVERB_HOST ?? 'localhost',
                wsPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
                wssPort: Number(import.meta.env.VITE_REVERB_PORT) || 8080,
                forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
                enabledTransports: ['ws', 'wss'],
            });
        } catch (error) {
            console.warn('[CuantoDolio] Failed to initialize Echo:', error);
            echoFailed = true;
            return null;
        }
    }
    return echo;
}

interface UseRoomChannelOptions {
    room: Ref<Room> | Room;
    onExpenseCreated?: (expense: Expense) => void;
    onExpenseUpdated?: (expense: Expense) => void;
    onExpenseDeleted?: (expenseId: string) => void;
    onParticipantJoined?: (participant: Participant) => void;
    onRoomLocked?: () => void;
    onSettlementPaid?: (data: { settlement_id: string }) => void;
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

        const echoInstance = getEcho();
        
        // If Echo is not available, skip real-time updates
        if (!echoInstance) {
            return;
        }

        const channel = echoInstance.private(channelName);

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
            })
            .listen('RoomLocked', () => {
                if (options.onRoomLocked) {
                    options.onRoomLocked();
                } else {
                    router.reload({ only: ['room'] });
                }
            })
            .listen('SettlementPaid', (data: { settlement_id: string }) => {
                if (options.onSettlementPaid) {
                    options.onSettlementPaid(data);
                } else {
                    // Optional: reload if needed, but dashboard handles its own data
                }
            });
    });

    onUnmounted(() => {
        if (channelName) {
            getEcho()?.leave(channelName);
        }
    });
}
