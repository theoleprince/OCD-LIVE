<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Models\User;
use App\Notifications\ContactMessageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SimpleContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_needs_only_an_email_and_a_message(): void
    {
        Notification::fake();
        RateLimiter::clear('contact-submit:127.0.0.1');
        $admin = User::factory()->create(['is_active' => true]);

        Livewire::test(ContactForm::class)
            ->set('email', 'ama@example.com')->set('message', 'Bonjour, comment réserver un live ?')
            ->call('submit')->assertHasNoErrors()->assertSet('sent', true);

        Notification::assertSentTo($admin, ContactMessageNotification::class,
            fn ($notification) => $notification->email === 'ama@example.com' && $notification->fullName === '');
    }
}
