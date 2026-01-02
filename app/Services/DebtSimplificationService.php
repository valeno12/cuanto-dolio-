<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Collection;

class DebtSimplificationService
{
    /**
     * Calculate simplified debts for a room.
     * 
     * This uses a greedy algorithm to minimize the number of transactions:
     * 1. Calculate net balance for each participant (paid - owed)
     * 2. Separate into creditors (positive balance) and debtors (negative balance)
     * 3. Match largest debtor with largest creditor, settle the smaller amount
     * 4. Repeat until all balanced
     *
     * @return array<int, array{from: array, to: array, amount: float}>
     */
    public function calculate(Room $room): array
    {
        $balances = $this->calculateBalances($room);
        
        // Separate into creditors (owed money) and debtors (owe money)
        $creditors = []; // People who should receive money (positive balance)
        $debtors = [];   // People who should pay money (negative balance)
        
        foreach ($balances as $participantId => $data) {
            $balance = round($data['balance'], 2);
            
            if ($balance > 0.01) {
                $creditors[] = [
                    'participant' => $data['participant'],
                    'amount' => $balance,
                ];
            } elseif ($balance < -0.01) {
                $debtors[] = [
                    'participant' => $data['participant'],
                    'amount' => abs($balance),
                ];
            }
            // If balance is ~0, they're even - skip
        }
        
        // Greedy algorithm: match largest debtor with largest creditor
        $settlements = [];
        
        while (!empty($creditors) && !empty($debtors)) {
            // Sort by amount descending
            usort($creditors, fn($a, $b) => $b['amount'] <=> $a['amount']);
            usort($debtors, fn($a, $b) => $b['amount'] <=> $a['amount']);
            
            $creditor = &$creditors[0];
            $debtor = &$debtors[0];
            
            // The settlement amount is the minimum of what's owed
            $amount = min($creditor['amount'], $debtor['amount']);
            
            if ($amount > 0.01) {
                $settlements[] = [
                    'from' => [
                        'id' => $debtor['participant']->id,
                        'name' => $debtor['participant']->name,
                    ],
                    'to' => [
                        'id' => $creditor['participant']->id,
                        'name' => $creditor['participant']->name,
                    ],
                    'amount' => round($amount, 2),
                ];
            }
            
            // Reduce balances
            $creditor['amount'] -= $amount;
            $debtor['amount'] -= $amount;
            
            // Remove if settled
            if ($creditor['amount'] < 0.01) {
                array_shift($creditors);
            }
            if ($debtor['amount'] < 0.01) {
                array_shift($debtors);
            }
        }
        
        return $settlements;
    }

    /**
     * Calculate net balance for each participant.
     * Balance = (total paid) - (total owed)
     * Positive = they should receive money
     * Negative = they should pay money
     *
     * @return array<string, array{participant: \App\Models\Participant, paid: float, owed: float, balance: float}>
     */
    public function calculateBalances(Room $room): array
    {
        $room->load(['participants', 'expenses.splits']);
        
        $balances = [];
        
        // Initialize all participants
        foreach ($room->participants as $participant) {
            $balances[$participant->id] = [
                'participant' => $participant,
                'paid' => 0.0,
                'owed' => 0.0,
                'balance' => 0.0,
            ];
        }
        
        // Calculate paid and owed amounts
        foreach ($room->expenses as $expense) {
            // The payer paid this amount
            if (isset($balances[$expense->payer_id])) {
                $balances[$expense->payer_id]['paid'] += (float) $expense->amount;
            }
            
            // Each person in the splits owes their portion
            foreach ($expense->splits as $split) {
                if (isset($balances[$split->participant_id])) {
                    $balances[$split->participant_id]['owed'] += (float) $split->amount_owed;
                }
            }
        }
        
        // Calculate net balance
        foreach ($balances as $participantId => &$data) {
            $data['balance'] = $data['paid'] - $data['owed'];
        }
        
        return $balances;
    }
}
