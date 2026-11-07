<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Customer;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class CustomerAndBusinessTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
    }

    public function test_a_booking_creates_the_customer(): void
    {
        app(BookingService::class)->create($this->bookingData());

        $customer = Customer::sole();
        $this->assertSame('Dupont', $customer->last_name);
        $this->assertSame('Cameroun', $customer->country);
        $this->assertSame('Africa/Douala', $customer->timezone);
        $this->assertSame('jean@example.com', $customer->email);
    }

    public function test_a_booking_creates_the_business_with_products_and_food_details(): void
    {
        app(BookingService::class)->create($this->bookingData());

        $business = Business::with('products')->sole();
        $this->assertSame('Commerce alimentaire', $business->name);
        $this->assertTrue($business->is_food);
        $this->assertSame('Glacière', $business->food_conservation);
        $this->assertSame(['facebook' => 'facebook.com/test'], $business->social_links);
        $this->assertCount(2, $business->products);
        $this->assertSame(20, $business->products->firstWhere('name', 'Ndolé')->quantity_available);
    }

    public function test_a_returning_customer_is_reused_not_duplicated(): void
    {
        $service = app(BookingService::class);
        $service->create($this->bookingData());
        $service->create($this->bookingData(['starts_at' => '2026-12-02 10:00']));

        $this->assertSame(1, Customer::count());
        $this->assertSame(1, Business::count());
        $this->assertSame(2, Customer::sole()->bookings()->count());
    }
}
