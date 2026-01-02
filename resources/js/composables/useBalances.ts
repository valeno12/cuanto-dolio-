import type { Balance, Debt, Expense, Participant } from '@/types';
import { computed, type ComputedRef } from 'vue';

interface UseBalancesOptions {
    participants: ComputedRef<Participant[]> | Participant[];
    expenses: ComputedRef<Expense[]> | Expense[];
}

export function useBalances({ participants, expenses }: UseBalancesOptions) {
    // Get reactive values
    const getParticipants = () => ('value' in participants ? participants.value : participants);
    const getExpenses = () => ('value' in expenses ? expenses.value : expenses);

    // Calculate balance for each participant
    const balances = computed<Balance[]>(() => {
        const participantsList = getParticipants();
        const expensesList = getExpenses();

        return participantsList.map((participant) => {
            // Total paid by this participant
            const totalPaid = expensesList.filter((e) => e.payer_id === participant.id).reduce((sum, e) => sum + parseFloat(e.amount), 0);

            // Total owed by this participant
            const totalOwed = expensesList
                .flatMap((e) => e.splits || [])
                .filter((s) => s.participant_id === participant.id)
                .reduce((sum, s) => sum + parseFloat(s.amount_owed), 0);

            return {
                participantId: participant.id,
                participant,
                totalPaid,
                totalOwed,
                netBalance: totalPaid - totalOwed, // positive = is owed money
            };
        });
    });

    // Calculate simplified debts (who owes whom)
    const debts = computed<Debt[]>(() => {
        const balancesList = [...balances.value];
        const result: Debt[] = [];

        // Separate into creditors (positive balance) and debtors (negative balance)
        const creditors = balancesList.filter((b) => b.netBalance > 0.01).sort((a, b) => b.netBalance - a.netBalance);

        const debtors = balancesList.filter((b) => b.netBalance < -0.01).sort((a, b) => a.netBalance - b.netBalance);

        // Match debtors to creditors
        let i = 0; // creditor index
        let j = 0; // debtor index

        while (i < creditors.length && j < debtors.length) {
            const creditor = creditors[i];
            const debtor = debtors[j];

            const amount = Math.min(creditor.netBalance, Math.abs(debtor.netBalance));

            if (amount > 0.01) {
                result.push({
                    from: debtor.participant,
                    to: creditor.participant,
                    amount: Math.round(amount * 100) / 100,
                });
            }

            creditor.netBalance -= amount;
            debtor.netBalance += amount;

            if (creditor.netBalance < 0.01) i++;
            if (debtor.netBalance > -0.01) j++;
        }

        return result;
    });

    // Get balance for a specific participant
    const getBalance = (participantId: string): Balance | undefined => {
        return balances.value.find((b) => b.participantId === participantId);
    };

    // Total expenses amount
    const totalExpenses = computed(() => {
        return getExpenses().reduce((sum, e) => sum + parseFloat(e.amount), 0);
    });

    // Format currency
    const formatCurrency = (amount: number): string => {
        return new Intl.NumberFormat('es-AR', {
            style: 'currency',
            currency: 'ARS',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        }).format(amount);
    };

    return {
        balances,
        debts,
        getBalance,
        totalExpenses,
        formatCurrency,
    };
}
