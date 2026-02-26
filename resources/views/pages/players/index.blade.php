<?php

use App\Models\Player;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Players')] class extends Component {
    use AuthorizesRequests, WithPagination;

    /**
     * Authorize access on component mount.
     */
    public function mount(): void
    {
        $this->authorize('view player notes');
    }

    /**
     * Get paginated players list with optimized query.
     */
    #[Computed]
    public function players(): mixed
    {
        return Player::select(['id', 'name', 'email'])
            ->withCount('notes')
            ->latest()
            ->paginate(15);
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">
            {{ __('Players') }}
        </flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('Manage players and their notes.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="space-y-2">
        @forelse ($this->players as $player)
            <flux:card class="p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="sm">{{ $player->name }}</flux:heading>
                        @if ($player->email)
                            <flux:text class="text-sm">{{ $player->email }}</flux:text>
                        @endif
                        <flux:badge size="sm" color="zinc" class="mt-1">
                            {{ trans_choice(':count note|:count notes', $player->notes_count) }}
                        </flux:badge>
                    </div>
                    <flux:button variant="ghost" size="sm" :href="route('players.notes', $player)" wire:navigate>
                        {{ __('View Notes') }}
                    </flux:button>
                </div>
            </flux:card>
        @empty
            <flux:text class="text-center py-8">
                {{ __('No players found.') }}
            </flux:text>
        @endforelse
    </div>

    @if ($this->players->hasPages())
        <div class="mt-6">
            {{ $this->players->links() }}
        </div>
    @endif
</section>
