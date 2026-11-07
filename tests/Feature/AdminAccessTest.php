<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BookingFixtures;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use BookingFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->atLomeTime();
        $this->seedPackages();
        Notification::fake();
    }

    public static function privateRoutes(): array
    {
        return [
            ['/admin'], ['/admin/reservations'], ['/admin/clients'], ['/admin/calendrier'],
            ['/admin/packages'], ['/admin/parametres'], ['/admin/journal'], ['/admin/notifications'],
            ['/admin/export/clients.csv'],
        ];
    }

    #[DataProvider('privateRoutes')]
    public function test_guests_are_redirected_to_login(string $uri): void
    {
        $this->get($uri)->assertRedirect(route('login'));
    }

    public function test_guest_cannot_post_admin_actions(): void
    {
        $booking = app(BookingService::class)->create($this->bookingData());

        $this->post(route('admin.bookings.confirm', $booking))->assertRedirect(route('login'));
        $this->post(route('admin.bookings.payment', $booking), ['method' => 'cash', 'confirm_received' => 1])->assertRedirect(route('login'));
        $this->assertFalse($booking->fresh()->isPaid());
    }

    public function test_admin_can_access_every_admin_page(): void
    {
        $admin = $this->admin();
        $booking = app(BookingService::class)->create($this->bookingData());

        foreach (array_merge(array_column(self::privateRoutes(), 0), ['/admin/reservations/'.$booking->id, '/admin/clients/'.$booking->customer_id, '/admin/packages/nouveau', '/admin/calendrier?day=2026-12-01']) as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
    }

    public function test_assistant_cannot_manage_packages_settings_or_export(): void
    {
        $assistant = $this->admin(UserRole::Assistant);

        $this->actingAs($assistant)->get('/admin')->assertOk();
        $this->actingAs($assistant)->get('/admin/reservations')->assertOk();
        $this->actingAs($assistant)->get('/admin/packages')->assertForbidden();
        $this->actingAs($assistant)->get('/admin/parametres')->assertForbidden();
        $this->actingAs($assistant)->get('/admin/export/clients.csv')->assertForbidden();
        $this->actingAs($assistant)->put(route('admin.packages.update', $this->starter()), ['name' => 'x', 'price' => 1, 'duration_minutes' => 90, 'sort_order' => 1])->assertForbidden();
        $this->assertSame(15000, $this->starter()->fresh()->price);
    }

    public function test_deactivated_account_cannot_log_in_or_browse(): void
    {
        $user = $this->admin();
        $user->forceFill(['is_active' => false])->save();

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_login_works_and_is_rate_limited(): void
    {
        $user = $this->admin();

        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post('/deconnexion');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/connexion', ['email' => $user->email, 'password' => 'mauvais']);
        }
        $this->post('/connexion', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Trop de tentatives', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_admin_pages_are_not_indexable(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    public function test_uploaded_files_are_only_served_to_staff(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $booking = app(BookingService::class)->create($this->bookingData(), ['logo' => \Illuminate\Http\UploadedFile::fake()->image('l.png')]);
        $file = $booking->attachments()->first();

        $this->get(route('admin.bookings.attachment', [$booking, $file]))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->get(route('admin.bookings.attachment', [$booking, $file]))->assertOk();
    }

    public function test_create_admin_command_never_hardcodes_a_password(): void
    {
        $this->artisan('ocd:create-admin', ['--email' => 'boss@example.com'])
            ->expectsOutputToContain('Mot de passe généré')
            ->assertSuccessful();

        $user = User::where('email', 'boss@example.com')->sole();
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertNotSame('password', $user->password);
    }
}
