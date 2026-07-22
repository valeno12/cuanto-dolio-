<?php

use App\Models\Expense;
use App\Models\Participant;
use App\Models\Room;
use App\Services\DebtSimplificationService;
use Illuminate\Support\Str;

/**
 * Crea una sala con los participantes indicados (por nombre).
 * Devuelve [Room, array<string, Participant>].
 */
function makeRoomWith(array $names): array
{
    $room = Room::create([
        'code' => strtoupper(Str::random(6)),
        'name' => 'Test',
    ]);

    $participants = [];
    foreach ($names as $name) {
        $participants[$name] = Participant::create([
            'room_id' => $room->id,
            'name' => $name,
        ]);
    }

    return [$room, $participants];
}

/**
 * Registra un gasto pagado por $payer, con sus splits.
 * $splits: array de ['participant' => Participant, 'amount' => float].
 */
function recordExpense(Room $room, Participant $payer, float $amount, array $splits): void
{
    $expense = Expense::create([
        'room_id' => $room->id,
        'payer_id' => $payer->id,
        'amount' => $amount,
        'description' => 'Gasto',
    ]);

    foreach ($splits as $split) {
        $expense->splits()->create([
            'participant_id' => $split['participant']->id,
            'amount_owed' => $split['amount'],
        ]);
    }
}

it('calcula el balance neto (pagado menos lo que le tocaba) de cada participante', function () {
    [$room, $p] = makeRoomWith(['Vale', 'Nico']);

    // Vale paga 100 y se divide 50/50.
    recordExpense($room, $p['Vale'], 100, [
        ['participant' => $p['Vale'], 'amount' => 50],
        ['participant' => $p['Nico'], 'amount' => 50],
    ]);

    $balances = app(DebtSimplificationService::class)->calculateBalances($room);

    expect($balances[$p['Vale']->id]['balance'])->toBe(50.0)   // pagó 100, le tocaba 50 → a favor
        ->and($balances[$p['Nico']->id]['balance'])->toBe(-50.0); // no pagó, le tocaba 50 → en contra
});

it('simplifica a un solo pago cuando uno le debe a otro', function () {
    [$room, $p] = makeRoomWith(['Vale', 'Nico']);

    recordExpense($room, $p['Vale'], 100, [
        ['participant' => $p['Vale'], 'amount' => 50],
        ['participant' => $p['Nico'], 'amount' => 50],
    ]);

    $settlements = app(DebtSimplificationService::class)->calculate($room);

    expect($settlements)->toHaveCount(1)
        ->and($settlements[0]['from']['name'])->toBe('Nico')
        ->and($settlements[0]['to']['name'])->toBe('Vale')
        ->and($settlements[0]['amount'])->toBe(50.0);
});

it('no genera ningún pago cuando el grupo está a mano', function () {
    [$room, $p] = makeRoomWith(['Vale', 'Nico']);

    // Cada uno paga 100, ambos gastos 50/50: nadie queda debiendo.
    recordExpense($room, $p['Vale'], 100, [
        ['participant' => $p['Vale'], 'amount' => 50],
        ['participant' => $p['Nico'], 'amount' => 50],
    ]);
    recordExpense($room, $p['Nico'], 100, [
        ['participant' => $p['Vale'], 'amount' => 50],
        ['participant' => $p['Nico'], 'amount' => 50],
    ]);

    $settlements = app(DebtSimplificationService::class)->calculate($room);

    expect($settlements)->toBeEmpty();
});

it('minimiza las transferencias con varios deudores hacia un acreedor', function () {
    [$room, $p] = makeRoomWith(['Ana', 'Beto', 'Caro']);

    // Ana paga 90, dividido en 3 (30 c/u). Beto y Caro le deben 30 cada uno.
    recordExpense($room, $p['Ana'], 90, [
        ['participant' => $p['Ana'], 'amount' => 30],
        ['participant' => $p['Beto'], 'amount' => 30],
        ['participant' => $p['Caro'], 'amount' => 30],
    ]);

    $settlements = app(DebtSimplificationService::class)->calculate($room);

    expect($settlements)->toHaveCount(2)
        ->and(collect($settlements)->every(fn ($s) => $s['to']['name'] === 'Ana'))->toBeTrue()
        ->and(round(collect($settlements)->sum('amount'), 2))->toBe(60.0);
});

it('reparte los centavos sin perder plata (100 entre 3)', function () {
    [$room, $p] = makeRoomWith(['Ana', 'Beto', 'Caro']);

    // 100 entre 3 → 33.34 / 33.33 / 33.33 (como los arma el front). Paga Ana.
    recordExpense($room, $p['Ana'], 100, [
        ['participant' => $p['Ana'], 'amount' => 33.34],
        ['participant' => $p['Beto'], 'amount' => 33.33],
        ['participant' => $p['Caro'], 'amount' => 33.33],
    ]);

    $settlements = app(DebtSimplificationService::class)->calculate($room);

    // Ana queda a favor 66.66; la suma de lo que le pagan cierra exacto, sin centavo perdido.
    expect($settlements)->toHaveCount(2)
        ->and(round(collect($settlements)->sum('amount'), 2))->toBe(66.66);
});
