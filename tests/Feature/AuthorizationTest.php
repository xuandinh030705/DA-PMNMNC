<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_dashboard_returns_403(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_access_organizer_dashboard_returns_403(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('organizer.dashboard'));

        $response->assertStatus(403);
    }

    public function test_organizer_cannot_edit_other_organizer_event_returns_403(): void
    {
        $org1 = User::factory()->organizer()->create();
        $org2 = User::factory()->organizer()->create();

        $eventOfOrg2 = Event::factory()->draft()->create([
            'organizer_id' => $org2->id,
        ]);

        $response = $this->actingAs($org1)->get(route('organizer.events.edit', $eventOfOrg2));

        $response->assertStatus(403);
    }

    public function test_customer_cannot_view_other_customer_order_returns_403(): void
    {
        $customer1 = User::factory()->customer()->create();
        $customer2 = User::factory()->customer()->create();

        $orderOfCustomer2 = Order::factory()->create([
            'user_id' => $customer2->id,
        ]);

        $response = $this->actingAs($customer1)->get(route('orders.show', $orderOfCustomer2->order_code));

        $response->assertStatus(403);
    }

    public function test_admin_cannot_self_lock_or_self_demote(): void
    {
        $admin = User::factory()->admin()->create();

        // Attempt to self-lock
        $responseLock = $this->actingAs($admin)->from(route('admin.users.index'))->post(route('admin.users.toggle', $admin));
        $responseLock->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);

        // Attempt to self-demote
        $responseDemote = $this->actingAs($admin)->from(route('admin.users.edit', $admin))->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'role' => User::ROLE_CUSTOMER,
            'is_active' => 1,
        ]);
        $responseDemote->assertSessionHas('error');
        $this->assertEquals(User::ROLE_ADMIN, $admin->fresh()->role);
    }
}
