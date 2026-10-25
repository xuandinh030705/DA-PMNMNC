<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_pending_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.approve', $event));

        $response->assertSessionHas('success');
        $this->assertEquals(Event::STATUS_PUBLISHED, $event->fresh()->status);
    }

    public function test_admin_can_reject_pending_event_back_to_draft(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->pending()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.reject', $event));

        $response->assertSessionHas('warning');
        $this->assertEquals(Event::STATUS_DRAFT, $event->fresh()->status);
    }

    public function test_admin_can_cancel_event(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->published()->create();

        $response = $this->actingAs($admin)->post(route('admin.events.cancel', $event));

        $response->assertSessionHas('success');
        $this->assertEquals(Event::STATUS_CANCELLED, $event->fresh()->status);
    }

    public function test_admin_cannot_delete_category_with_existing_events(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        Event::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_admin_can_toggle_user_lock_status(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post(route('admin.users.toggle', $customer));

        $response->assertSessionHas('success');
        $this->assertFalse((bool) $customer->fresh()->is_active);

        // Toggle back to active
        $this->actingAs($admin)->post(route('admin.users.toggle', $customer));
        $this->assertTrue((bool) $customer->fresh()->is_active);
    }
}
