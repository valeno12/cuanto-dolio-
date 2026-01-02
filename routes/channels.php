<?php

use App\Models\Room;
use Illuminate\Http\Request;
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
 * We use a custom authorization since we're not using Laravel's auth system.
 */
Broadcast::channel('room.{roomId}', function (Request $request, string $roomId) {
    $participant = $request->participant();
    
    if (!$participant) {
        return false;
    }
    
    return $participant->room_id === $roomId;
});
