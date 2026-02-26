<?php

namespace App\Contracts\Repositories;

use App\Models\Player;
use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface PlayerNoteRepositoryInterface
{
    /**
     * Get paginated notes for a given player, most recent first.
     */
    public function getByPlayer(Player $player, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new note for a player authored by a user.
     */
    public function create(Player $player, User $author, string $content): PlayerNote;
}
