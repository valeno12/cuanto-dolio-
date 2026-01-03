<?php

namespace Database\Seeders;

use App\Enums\ParticipantRole;
use App\Models\Expense;
use App\Models\ExpenseSplit;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Database\Seeder;

class AsadoSeeder extends Seeder
{
    public function run(): void
    {
        // Find the room
        $room = Room::where('code', 'MWEW4V')->first();
        
        if (!$room) {
            $this->command->error('Room MWEW4V not found!');
            return;
        }

        // Get existing admin (Valentino)
        $admin = $room->participants()->where('role', ParticipantRole::Admin)->first();
        
        if ($admin) {
            $admin->update(['payment_alias' => 'vale.mp']);
        }

        // Create virtual participants
        $participantsData = [
            ['name' => 'Martin', 'payment_alias' => 'martin.mp'],
            ['name' => 'Lucas', 'payment_alias' => 'lucas.cvu.234'],
            ['name' => 'Sofia', 'payment_alias' => 'sofi.pagos'],
            ['name' => 'Camila', 'payment_alias' => 'cami.mp'],
            ['name' => 'Juan', 'payment_alias' => 'juan.transferencias'],
            ['name' => 'Pedro', 'payment_alias' => null], // No alias
            ['name' => 'María', 'payment_alias' => 'maria.mp'],
            ['name' => 'Agustín', 'payment_alias' => 'agus.pagos'],
            ['name' => 'Florencia', 'payment_alias' => 'flor.cbu.567'],
        ];

        $participants = [];
        
        // Add admin to participants array
        if ($admin) {
            $participants[$admin->name] = $admin;
        }

        foreach ($participantsData as $data) {
            $participant = Participant::create([
                'room_id' => $room->id,
                'name' => $data['name'],
                'role' => ParticipantRole::Virtual,
                'payment_alias' => $data['payment_alias'],
            ]);
            $participants[$data['name']] = $participant;
        }

        $allNames = array_keys($participants);
        
        // People who don't eat chorizos
        $noChorizos = ['Sofia', 'María'];
        $eatChorizos = array_diff($allNames, $noChorizos);
        
        // People who don't drink alcohol
        $noDrink = ['Pedro', 'Camila', 'Florencia'];
        $drink = array_diff($allNames, $noDrink);

        // Expenses
        $expenses = [
            [
                'description' => 'Carne (10kg asado)',
                'amount' => 45000,
                'payer' => 'Martin',
                'split' => $allNames,
                'category' => 'food',
            ],
            [
                'description' => 'Chorizos y morcillas',
                'amount' => 8000,
                'payer' => 'Lucas',
                'split' => $eatChorizos,
                'category' => 'food',
            ],
            [
                'description' => 'Vino tinto (6 botellas)',
                'amount' => 18000,
                'payer' => 'Sofia',
                'split' => $drink,
                'category' => 'drinks',
            ],
            [
                'description' => 'Carbón y leña',
                'amount' => 5000,
                'payer' => 'Juan',
                'split' => $allNames,
                'category' => 'other',
            ],
            [
                'description' => 'Pan y ensaladas',
                'amount' => 6000,
                'payer' => 'Camila',
                'split' => $allNames,
                'category' => 'food',
            ],
            [
                'description' => 'Gaseosas',
                'amount' => 4000,
                'payer' => 'Pedro',
                'split' => $noDrink,
                'category' => 'drinks',
            ],
            [
                'description' => 'Helado postre',
                'amount' => 12000,
                'payer' => 'María',
                'split' => $allNames,
                'category' => 'food',
            ],
            [
                'description' => 'Fernet',
                'amount' => 9000,
                'payer' => 'Agustín',
                'split' => $drink,
                'category' => 'drinks',
            ],
        ];

        foreach ($expenses as $expenseData) {
            $payer = $participants[$expenseData['payer']];
            $splitParticipants = array_map(fn($name) => $participants[$name], $expenseData['split']);
            $splitCount = count($splitParticipants);
            $amountPerPerson = $expenseData['amount'] / $splitCount;

            $expense = Expense::create([
                'room_id' => $room->id,
                'payer_id' => $payer->id,
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'category' => $expenseData['category'],
            ]);

            foreach ($splitParticipants as $participant) {
                ExpenseSplit::create([
                    'expense_id' => $expense->id,
                    'participant_id' => $participant->id,
                    'amount_owed' => $amountPerPerson,
                ]);
            }
        }

        $this->command->info('Asado data seeded successfully!');
        $this->command->info('Participants: ' . count($participants));
        $this->command->info('Expenses: ' . count($expenses));
    }
}
