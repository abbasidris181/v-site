<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AnnouncementSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(AnnouncementSeeder::class);
    }

    public function test_super_admin_and_admin_can_access_announcements_index(): void
    {
        $superAdmin = User::where('email', 'superadmin@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $this->actingAs($superAdmin)->get('/admin/announcements')
            ->assertStatus(200)
            ->assertSee('System Announcements')
            ->assertSee('Create Announcement');

        $this->actingAs($admin)->get('/admin/announcements')
            ->assertStatus(200)
            ->assertSee('System Announcements');
    }

    public function test_staff_without_permission_receives_403(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();

        // Staff does not have announcements.manage by default
        $this->actingAs($staff)->get('/admin/announcements')
            ->assertStatus(403);
    }

    public function test_all_roles_that_have_permission_can_access_announcements(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();
        $permission = Permission::where('slug', 'announcements.manage')->first();
        $staffRole = Role::where('slug', 'staff')->first();

        // Grant permission to staff role
        $staffRole->permissions()->attach($permission->id);

        $this->actingAs($staff)->get('/admin/announcements')
            ->assertStatus(200)
            ->assertSee('System Announcements');
    }

    public function test_agent_and_end_user_cannot_access_announcements(): void
    {
        $agent = User::where('email', 'agent@vsite.ng')->first();
        $endUser = User::where('email', 'user@vsite.ng')->first();

        $this->actingAs($agent)->get('/admin/announcements')->assertStatus(403);
        $this->actingAs($endUser)->get('/admin/announcements')->assertStatus(403);
    }

    public function test_admin_can_create_new_announcement(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->post('/admin/announcements', [
            'title' => 'Scheduled System Upgrade',
            'content' => 'Server maintenance scheduled for midnight UTC.',
            'type' => 'warning',
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/announcements');
        $response->assertSessionHas('status', 'Announcement published successfully.');

        $this->assertDatabaseHas('announcements', [
            'title' => 'Scheduled System Upgrade',
            'type' => 'warning',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_edit_and_update_announcement(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $announcement = Announcement::first();

        $this->actingAs($admin)->get("/admin/announcements/{$announcement->id}/edit")
            ->assertStatus(200)
            ->assertSee($announcement->title);

        $updateResponse = $this->actingAs($admin)->put("/admin/announcements/{$announcement->id}", [
            'title' => 'Updated Maintenance Notice',
            'content' => 'All systems operational post maintenance.',
            'type' => 'success',
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect('/admin/announcements');
        $updateResponse->assertSessionHas('status', 'Announcement updated successfully.');

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated Maintenance Notice',
            'type' => 'success',
        ]);
    }

    public function test_admin_can_toggle_announcement_status(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $announcement = Announcement::first();
        $this->assertTrue($announcement->is_active);

        $response = $this->actingAs($admin)->post("/admin/announcements/{$announcement->id}/toggle-status");
        $response->assertStatus(302);

        $announcement->refresh();
        $this->assertFalse($announcement->is_active);
    }

    public function test_admin_can_delete_announcement(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $announcement = Announcement::first();

        $response = $this->actingAs($admin)->delete("/admin/announcements/{$announcement->id}");
        $response->assertRedirect('/admin/announcements');

        $this->assertDatabaseMissing('announcements', [
            'id' => $announcement->id,
        ]);
    }

    public function test_active_announcements_are_rendered_on_user_dashboard_while_inactive_are_hidden(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        // Create an active announcement
        Announcement::create([
            'title' => 'Active Test Announcement',
            'content' => 'This announcement must be visible to customers.',
            'type' => 'info',
            'is_active' => true,
        ]);

        // Create an inactive announcement
        Announcement::create([
            'title' => 'Hidden Secret Notice',
            'content' => 'This announcement must NEVER be visible to customers.',
            'type' => 'warning',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Active Test Announcement');
        $response->assertSee('This announcement must be visible to customers.');
        $response->assertDontSee('Hidden Secret Notice');
        $response->assertDontSee('This announcement must NEVER be visible to customers.');
    }
}
