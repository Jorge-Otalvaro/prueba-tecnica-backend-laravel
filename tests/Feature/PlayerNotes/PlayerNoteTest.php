<?php

namespace Tests\Feature\PlayerNotes;

use App\Models\Player;
use App\Models\PlayerNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PlayerNoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions and roles
        Permission::create(['name' => 'view player notes']);
        Permission::create(['name' => 'add player notes']);

        $support = Role::create(['name' => 'support']);
        $support->givePermissionTo(['view player notes', 'add player notes']);

        // Role with only view permission (no add)
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo(['view player notes']);
    }

    public function test_guests_are_redirected_from_player_notes(): void
    {
        $player = Player::factory()->create();

        $response = $this->get(route('players.notes', $player));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_player_notes_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('players.notes', $player));

        $response->assertOk();
    }

    public function test_user_with_permission_can_add_note(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', 'This player shows great improvement.')
            ->call('addNote')
            ->assertHasNoErrors()
            ->assertDispatched('note-added');

        $this->assertDatabaseHas('player_notes', [
            'player_id' => $player->id,
            'user_id' => $user->id,
            'content' => 'This player shows great improvement.',
        ]);
    }

    public function test_note_content_is_required(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', '')
            ->call('addNote')
            ->assertHasErrors(['content' => 'required']);
    }

    public function test_note_content_has_max_length(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', str_repeat('a', 1001))
            ->call('addNote')
            ->assertHasErrors(['content' => 'max']);
    }

    public function test_notes_are_displayed_for_player(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        PlayerNote::factory()->count(3)->create([
            'player_id' => $player->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->assertSee($player->name);
    }

    public function test_user_without_add_permission_cannot_see_add_form(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer'); // has 'view' but not 'add'
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->assertDontSee(__('Add Note'));
    }

    public function test_form_resets_after_adding_note(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', 'A note about the player.')
            ->call('addNote')
            ->assertSet('content', '');
    }

    public function test_user_without_add_permission_cannot_add_note(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer'); // has 'view' but not 'add'
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', 'Unauthorized note.')
            ->call('addNote')
            ->assertForbidden();

        $this->assertDatabaseMissing('player_notes', [
            'player_id' => $player->id,
            'content' => 'Unauthorized note.',
        ]);
    }

    public function test_guests_are_redirected_from_players_index(): void
    {
        $response = $this->get(route('players.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_is_forbidden_from_notes_page(): void
    {
        $user = User::factory()->create();
        $player = Player::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('players.notes', $player));

        $response->assertForbidden();
    }

    public function test_notes_are_ordered_by_newest_first(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        PlayerNote::factory()->create([
            'player_id' => $player->id,
            'user_id' => $user->id,
            'content' => 'Old note from the past',
            'created_at' => now()->subDays(2),
        ]);

        PlayerNote::factory()->create([
            'player_id' => $player->id,
            'user_id' => $user->id,
            'content' => 'New note from today',
            'created_at' => now(),
        ]);

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->assertSeeInOrder(['New note from today', 'Old note from the past']);
    }

    public function test_notes_from_other_players_are_not_shown(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');

        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();

        PlayerNote::factory()->create([
            'player_id' => $player1->id,
            'user_id' => $user->id,
            'content' => 'Note for player 1',
        ]);

        PlayerNote::factory()->create([
            'player_id' => $player2->id,
            'user_id' => $user->id,
            'content' => 'Note for player 2',
        ]);

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player1])
            ->assertSee('Note for player 1')
            ->assertDontSee('Note for player 2');
    }

    public function test_note_content_rejects_only_whitespace(): void
    {
        $user = User::factory()->create();
        $user->assignRole('support');
        $player = Player::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::players.notes', ['player' => $player])
            ->set('content', '     ')
            ->call('addNote')
            ->assertHasErrors(['content']);

        $this->assertDatabaseCount('player_notes', 0);
    }
}
