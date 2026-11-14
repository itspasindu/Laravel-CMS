<?php

namespace Tests\Feature;

use Tests\TestCase;

class OidcStubTest extends TestCase
{
    public function test_oidc_redirect_falls_back_to_login_when_disabled(): void
    {
        config(['oidc.enabled' => false]);

        $response = $this->get(route('oidc.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
    }

    public function test_oidc_callback_falls_back_when_disabled(): void
    {
        config(['oidc.enabled' => false]);

        $response = $this->get(route('oidc.callback', ['code' => 'demo']));

        $response->assertRedirect(route('login'));
    }
}
