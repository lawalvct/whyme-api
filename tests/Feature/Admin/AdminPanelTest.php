<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Users\Pages\ViewUser;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use Database\Seeders\StaffRolesSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(StaffRolesSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_the_staff_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_staff_with_users_view_can_list_users(): void
    {
        $user = User::factory()->create(['name' => 'Ngozi Eze']);

        $this->actingAs($this->staff('support'), 'admin')
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Ngozi Eze');
    }

    public function test_staff_without_permission_are_refused(): void
    {
        $this->actingAs($this->staff(), 'admin')
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_a_marketplace_user_cannot_reach_the_staff_console(): void
    {
        $this->actingAs(User::factory()->create(), 'web')
            ->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_a_moderator_can_suspend_and_it_signs_the_user_out_and_is_audited(): void
    {
        $user = User::factory()->create();
        $user->createToken('device:test');
        $moderator = $this->staff('moderator');
        $moderator->givePermissionTo('users.manage');

        $this->actingAs($moderator, 'admin');
        Livewire::test(ViewUser::class, ['record' => $user->getKey()])
            ->callAction('suspend', data: ['reason' => 'Asked buyers to pay outside WHY ME'])
            ->assertHasNoActionErrors();

        $this->assertSame(UserStatus::Suspended, $user->fresh()->status);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user.suspended',
            'actor_type' => 'admin',
            'actor_id' => (string) $moderator->id,
            'subject_id' => $user->id,
        ]);
    }

    public function test_a_suspended_user_with_a_live_session_is_refused_by_the_api(): void
    {
        Sanctum::actingAs(User::factory()->suspended()->create());

        $this->getJson('/api/v1/me')
            ->assertForbidden()
            ->assertJsonPath('code', 'account_suspended');
    }

    private function staff(?string $role = null): Admin
    {
        $admin = Admin::create(['name' => 'Staff', 'email' => fake()->unique()->safeEmail(), 'password' => 'password']);
        if ($role) {
            $admin->assignRole($role);
        }

        return $admin;
    }
}
