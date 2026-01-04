<?php

namespace App\Console\Commands;

use App\Models\Room;
use Illuminate\Console\Command;

class CleanupExpiredRooms extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rooms:cleanup';

    /**
     * The console command description.
     */
    protected $description = 'Delete expired rooms and all related data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredRooms = Room::expired()->get();
        $count = $expiredRooms->count();

        if ($count === 0) {
            $this->info('No expired rooms found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$count} expired room(s). Deleting...");

        foreach ($expiredRooms as $room) {
            // Delete related data (cascade)
            $room->settlements()->delete();
            $room->expenses()->each(function ($expense) {
                $expense->splits()->delete();
                $expense->delete();
            });
            $room->participants()->delete();
            $room->delete();

            $this->line("  - Deleted room: {$room->code} ({$room->name})");
        }

        $this->info("Successfully deleted {$count} expired room(s).");

        return Command::SUCCESS;
    }
}
