<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdministratorUserResourceTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');
    }

    public function test_guest_cannot_open_administrator_list(): void
    {
        Livewire::test(ListUsers::class)
            ->assertForbidden();
    }

    public function test_authenticated_user_can_list_administrators(): void
    {
        $this->actingAs(User::factory()->create());

        $records = User::factory()->count(3)->create();

        Livewire::test(ListUsers::class)
            ->assertOk()
            ->assertCanSeeTableRecords($records);
    }

    public function test_authenticated_user_can_create_an_administrator(): void
    {
        $this->actingAs(User::factory()->create());

        $data = User::factory()->make();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => $data->name,
                'email' => $data->email,
                'password' => 'new-password-string',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified('Administrator created successfully.')
            ->assertRedirect(UserResource::getUrl('index'));

        $this->assertDatabaseHas(User::class, [
            'name' => $data->name,
            'email' => $data->email,
        ]);
    }

    public function test_create_requires_password(): void
    {
        $this->actingAs(User::factory()->create());

        $data = User::factory()->make();

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => $data->name,
                'email' => $data->email,
            ])
            ->call('create')
            ->assertHasFormErrors(['password' => 'required'])
            ->assertNotNotified();
    }

    public function test_authenticated_user_can_update_administrator_without_changing_password(): void
    {
        $this->actingAs(User::factory()->create());

        $target = User::factory()->create(['name' => 'Original Name']);

        Livewire::test(EditUser::class, ['record' => $target->getKey()])
            ->fillForm([
                'name' => 'Updated Name',
                'email' => $target->email,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(User::class, [
            'id' => $target->getKey(),
            'name' => 'Updated Name',
        ]);
    }

    public function test_delete_action_is_hidden_when_editing_own_account(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $admin->getKey()])
            ->assertOk()
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_authenticated_user_can_delete_another_administrator(): void
    {
        $this->actingAs(User::factory()->create());

        $other = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $other->getKey()])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $other->getKey()]);
    }
}
