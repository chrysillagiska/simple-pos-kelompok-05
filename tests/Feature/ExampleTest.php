<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guest is redirected to the POS page from the home page.
     */
    public function test_guest_is_redirected_to_pos_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/pos');
    }
}