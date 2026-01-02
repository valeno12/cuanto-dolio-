// CuantoDolio Types

export type ParticipantRole = 'admin' | 'member' | 'virtual';

export interface Participant {
    id: string;
    room_id: string;
    name: string;
    role: ParticipantRole;
    created_at: string;
    updated_at: string;
}

export interface ExpenseSplit {
    id: string;
    expense_id: string;
    participant_id: string;
    amount_owed: string; // Decimal comes as string from Laravel
    participant?: Participant;
}

export interface Expense {
    id: string;
    room_id: string;
    payer_id: string;
    amount: string; // Decimal comes as string from Laravel
    description: string;
    created_at: string;
    updated_at: string;
    payer?: Participant;
    splits?: ExpenseSplit[];
}

export interface Room {
    id: string;
    code: string;
    is_locked: boolean;
    created_at: string;
    updated_at: string;
    participants?: Participant[];
    expenses?: Expense[];
}

// Inertia Page Props
export interface PageProps {
    name: string;
    participant: Participant | null;
}

export interface RoomShowProps extends PageProps {
    room: Room;
    currentParticipant: Participant;
}

export interface RoomJoinProps extends PageProps {
    room: Pick<Room, 'id' | 'code'>;
}

export interface RoomLockedProps extends PageProps {
    room: Pick<Room, 'code'>;
}

// Form types
export interface CreateExpenseForm {
    description: string;
    amount: number;
    payer_id: string;
    splits: {
        participant_id: string;
        amount: number;
    }[];
}

// Balance calculation
export interface Balance {
    participantId: string;
    participant: Participant;
    totalPaid: number;
    totalOwed: number;
    netBalance: number; // positive = is owed money, negative = owes money
}

export interface Debt {
    from: Participant;
    to: Participant;
    amount: number;
}
