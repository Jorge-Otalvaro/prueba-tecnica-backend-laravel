<?php

namespace App\Repositories;

use App\Contracts\Repositories\PlayerNoteRepositoryInterface;
use App\Models\Player;
use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final class EloquentPlayerNoteRepository implements PlayerNoteRepositoryInterface
{
    /**
     * Get paginated notes for a given player, most recent first.
     */
    public function getByPlayer(Player $player, int $perPage = 10): LengthAwarePaginator
    {
        return $player->notes()
            ->with('user:id,name')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new note for a player authored by a user.
     */
    public function create(Player $player, User $author, string $content): PlayerNote
    {
        return $player->notes()->create([
            'user_id' => $author->id,
            'content' => $content,
        ]);
    }
}
