<?php

namespace Tests\Feature;

use Tests\TestCase;

class FilamentPanelSmokeTest extends TestCase
{
    public function test_the_guest_login_page_renders_successfully(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('email', escape: false);
    }

    public function test_public_self_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_the_guest_password_reset_request_page_renders_successfully(): void
    {
        $this->get('/password-reset/request')->assertOk();
    }
}
