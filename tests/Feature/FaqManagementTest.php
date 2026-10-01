<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\User;
use Database\Seeders\FaqSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(FaqSeeder::class);
    }

    public function test_admin_can_view_faqs_list(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->get('/admin/faqs');

        $response->assertStatus(200);
        $response->assertSee('Support Frequently Asked Questions');
        $response->assertSee('Add New FAQ');
        $response->assertSee('How does Instant API Verification work?');
        $response->assertSee('What is Partial-Batch Wallet Processing?');
    }

    public function test_staff_without_permission_cannot_access_faq_management(): void
    {
        $staff = User::where('email', 'staff@vsite.ng')->first();

        $response = $this->actingAs($staff)->get('/admin/faqs');
        $response->assertStatus(403);

        $postResponse = $this->actingAs($staff)->post('/admin/faqs', [
            'question' => 'Unauthorized Question?',
            'answer' => 'Unauthorized Answer',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_end_user_cannot_access_faq_management(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/admin/faqs');
        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_faq(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->post('/admin/faqs', [
            'question' => 'Can I fund other users from my wallet balance?',
            'answer' => 'Yes, agents and administrators can easily transfer funds directly to any customer account.',
            'category' => 'Wallet & Billing',
            'sort_order' => 4,
            'is_active' => '1',
        ]);

        $response->assertRedirect('/admin/faqs');
        $response->assertSessionHas('status', 'FAQ created successfully.');

        $this->assertDatabaseHas('faqs', [
            'question' => 'Can I fund other users from my wallet balance?',
            'category' => 'Wallet & Billing',
            'sort_order' => 4,
            'is_active' => true,
        ]);
    }

    public function test_faq_validation_requires_question_and_answer(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $response = $this->actingAs($admin)->post('/admin/faqs', [
            'question' => '',
            'answer' => '',
        ]);

        $response->assertSessionHasErrors(['question', 'answer']);
    }

    public function test_admin_can_update_existing_faq(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $faq = Faq::first();

        $editViewResponse = $this->actingAs($admin)->get("/admin/faqs/{$faq->id}/edit");
        $editViewResponse->assertStatus(200);
        $editViewResponse->assertSee($faq->question);

        $updateResponse = $this->actingAs($admin)->put("/admin/faqs/{$faq->id}", [
            'question' => 'Updated Frequently Asked Question?',
            'answer' => 'Updated comprehensive answer provided by admin.',
            'category' => 'Updated Category',
            'sort_order' => 99,
            'is_active' => '1',
        ]);

        $updateResponse->assertRedirect('/admin/faqs');
        $updateResponse->assertSessionHas('status', 'FAQ updated successfully.');

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => 'Updated Frequently Asked Question?',
            'answer' => 'Updated comprehensive answer provided by admin.',
            'category' => 'Updated Category',
            'sort_order' => 99,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_faq_status(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $faq = Faq::first();
        $this->assertTrue($faq->is_active);

        $response = $this->actingAs($admin)->post("/admin/faqs/{$faq->id}/toggle-status");
        $response->assertRedirect();
        $this->assertFalse($faq->fresh()->is_active);

        $response = $this->actingAs($admin)->post("/admin/faqs/{$faq->id}/toggle-status");
        $response->assertRedirect();
        $this->assertTrue($faq->fresh()->is_active);
    }

    public function test_admin_can_delete_faq(): void
    {
        $admin = User::where('email', 'admin@vsite.ng')->first();
        $faq = Faq::create([
            'question' => 'Temporary Question to Delete?',
            'answer' => 'Temporary Answer',
            'category' => 'General',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('faqs', ['id' => $faq->id]);

        $response = $this->actingAs($admin)->delete("/admin/faqs/{$faq->id}");
        $response->assertRedirect('/admin/faqs');
        $response->assertSessionHas('status', 'FAQ deleted successfully.');

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_active_faqs_are_displayed_on_user_support_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();

        $response = $this->actingAs($user)->get('/support');

        $response->assertStatus(200);
        $response->assertSee('Frequently Asked Questions');
        $response->assertSee('How does Instant API Verification work?');
        $response->assertSee('What is Partial-Batch Wallet Processing?');
        $response->assertSee('How do I access and print my official verification slips?');
    }

    public function test_inactive_faqs_are_hidden_from_user_support_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $faq = Faq::where('question', 'How does Instant API Verification work?')->firstOrFail();

        // Initially active
        $response = $this->actingAs($user)->get('/support');
        $response->assertSee('How does Instant API Verification work?');

        // Admin deactivates
        $this->actingAs($admin)->post("/admin/faqs/{$faq->id}/toggle-status");
        $this->assertFalse($faq->fresh()->is_active);

        // User views support - deactivated FAQ must not be seen
        $responseAfterDeactivate = $this->actingAs($user)->get('/support');
        $responseAfterDeactivate->assertDontSee('How does Instant API Verification work?');
        // Other active FAQs should still be visible
        $responseAfterDeactivate->assertSee('What is Partial-Batch Wallet Processing?');
    }

    public function test_deleted_faqs_are_removed_from_user_support_page(): void
    {
        $user = User::where('email', 'user@vsite.ng')->first();
        $admin = User::where('email', 'admin@vsite.ng')->first();

        $faq = Faq::where('question', 'What is Partial-Batch Wallet Processing?')->firstOrFail();

        // Admin deletes FAQ
        $this->actingAs($admin)->delete("/admin/faqs/{$faq->id}");
        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);

        // User views support
        $response = $this->actingAs($user)->get('/support');
        $response->assertDontSee('What is Partial-Batch Wallet Processing?');
        $response->assertSee('How does Instant API Verification work?');
    }
}
