<?php

use App\Models\Participant;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Authorize a participant to listen to a room's private channel.
 * 
 * The $user parameter is now the Participant (resolved by our custom guard).
 */
Broadcast::channel('room.{roomId}', function (Participant $user, string $roomId) {
    return $user->room_id === $roomId;
});
