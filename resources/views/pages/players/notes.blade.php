<?php

use App\Concerns\PlayerNoteValidationRules;
use App\Contracts\Repositories\PlayerNoteRepositoryInterface;
use App\Models\Player;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Player Notes')] class extends Component {
    use AuthorizesRequests, PlayerNoteValidationRules, WithPagination;

    #[Locked]
    public int $playerId;

    #[Locked]
    public string $playerName = '';

    public string $content = '';

    /**
     * Mount the component with the player model.
     */
    public function mount(Player $player): void
    {
        $this->authorize('view player notes');

        $this->playerId = $player->id;
        $this->playerName = $player->name;
    }

    /**
     * Add a new note for this player.
     */
    public function addNote(PlayerNoteRepositoryInterface $repository): void
    {
        $this->authorize('add player notes');

        $this->validate($this->noteRules());

        $player = Player::findOrFail($this->playerId);

        $repository->create(
            player: $player,
            author: Auth::user(),
            content: $this->content,
        );

        $this->reset('content');
        $this->resetPage();
        $this->dispatch('note-added');
    }

    /**
     * Check if the current user can add notes.
     */
    #[Computed]
    public function canAddNotes(): bool
    {
        return Auth::user()->can('add player notes');
    }

    /**
     * Get paginated notes for this player.
     */
    #[Computed]
    public function notes(): mixed
    {
        $repository = app(PlayerNoteRepositoryInterface::class);

        return $repository->getByPlayer(
            Player::findOrFail($this->playerId),
        );
    }
}; ?>

<section class="w-full max-w-4xl mx-auto">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">
            {{ __('Notes for :name', ['name' => $playerName]) }}
        </flux:heading>
        <flux:subheading size="lg" class="mb-6">
            {{ __('View and manage internal notes for this player.') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @if ($this->canAddNotes)
        <div class="mb-6">
            <form wire:submit="addNote" class="space-y-4">
                <flux:textarea
                    wire:model="content"
                    :label="__('New Note')"
                    :placeholder="__('Write a note about this player...')"
                    rows="3"
                    required
                />

                @error('content')
                    <flux:text class="!text-red-500 text-sm">{{ $message }}</flux:text>
                @enderror

                <div class="flex items-center gap-4">
                    <flux:button variant="primary" type="submit">
                        {{ __('Add Note') }}
                    </flux:button>

                    <x-action-message class="me-3" on="note-added">
                        {{ __('Note added.') }}
                    </x-action-message>
                </div>
            </form>
        </div>

        <flux:separator class="my-6" />
    @endif

    <div class="space-y-4">
        @forelse ($this->notes as $note)
            <flux:card class="p-4">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:heading size="sm">
                                {{ $note->user->name }}
                            </flux:heading>
                            <flux:badge size="sm" color="zinc">
                                {{ $note->created_at->format('M d, Y \a\t h:i A') }}
                            </flux:badge>
                        </div>
                        <flux:text>
                            {{ $note->content }}
                        </flux:text>
                    </div>
                </div>
            </flux:card>
        @empty
            <flux:text class="text-center py-8">
                {{ __('No notes yet for this player.') }}
            </flux:text>
        @endforelse
    </div>

    @if ($this->notes->hasPages())
        <div class="mt-6">
            {{ $this->notes->links() }}
        </div>
    @endif
</section>
