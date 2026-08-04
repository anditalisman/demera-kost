<?php

namespace Tests\Feature\Fashion;

use App\Domain\Fashion\Models\FashionLaunchSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FashionComingSoonTest extends TestCase
{
    use RefreshDatabase;

    public function test_fashion_pages_render(): void
    {
        $this->get('/fashion')->assertOk();
        $this->get('/fashion/products')->assertOk();
        $this->get('/fashion/categories')->assertOk();
        $this->get('/fashion/product/essential-cotton-tee')->assertOk();
    }

    public function test_unknown_product_slug_returns_404(): void
    {
        $this->get('/fashion/product/does-not-exist')->assertNotFound();
    }

    public function test_visitor_can_subscribe_for_launch_notification(): void
    {
        $response = $this->post('/fashion/subscribe', [
            'name' => 'Dinda',
            'email' => 'dinda@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fashion_launch_subscribers', ['email' => 'dinda@example.com']);
    }

    public function test_subscription_requires_email_or_whatsapp(): void
    {
        $response = $this->post('/fashion/subscribe', ['name' => 'Dinda']);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('fashion_launch_subscribers', 0);
    }

    public function test_subscription_rejects_duplicate_email(): void
    {
        FashionLaunchSubscriber::create(['email' => 'dinda@example.com', 'subscribed_at' => now()]);

        $response = $this->post('/fashion/subscribe', ['email' => 'dinda@example.com']);

        $response->assertSessionHasErrors('email');
    }
}
