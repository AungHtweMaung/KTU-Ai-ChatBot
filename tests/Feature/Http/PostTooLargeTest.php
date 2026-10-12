<?php

namespace Tests\Feature\Http;

use Tests\TestCase;

/**
 * A body larger than PHP's `post_max_size` is refused by the PHP handler
 * itself. ValidatePostSize runs in the global middleware stack, ahead of the
 * session, so Laravel can never turn the failure into a validation error on the
 * form — the user just gets a 413.
 *
 * What it must NOT get is the framework's bare error page, which Inertia shows
 * verbatim inside its error modal and which reads as a crash. These tests pin
 * the application's own error page in its place.
 */
class PostTooLargeTest extends TestCase
{
    /** Comfortably past any sane post_max_size. */
    private const OVERSIZED = 99999999;

    private function oversizedUpload(array $headers = [])
    {
        return $this->call('POST', '/admin/timetable', [], [], [], [
            'CONTENT_LENGTH' => self::OVERSIZED,
        ] + $headers);
    }

    public function test_oversized_upload_returns_413(): void
    {
        $this->oversizedUpload()->assertStatus(413);
    }

    public function test_oversized_upload_renders_the_app_error_page_for_inertia(): void
    {
        $response = $this->oversizedUpload(['HTTP_X_INERTIA' => 'true']);

        $response->assertStatus(413);
        $this->assertTrue(
            $response->headers->has('X-Inertia'),
            'An Inertia request should get an Inertia response, not the framework HTML page.',
        );

        $page = json_decode($response->getContent(), true);

        $this->assertSame('Error', $page['component'] ?? null);
        $this->assertSame(413, $page['props']['status'] ?? null);
    }

    public function test_oversized_upload_renders_the_app_shell_for_plain_requests(): void
    {
        $response = $this->oversizedUpload();

        $response->assertStatus(413);

        // The Inertia root template, not the framework's "Oops! An Error
        // Occurred" page.
        $this->assertStringContainsString('data-page', $response->getContent());
        $this->assertStringNotContainsString('Oops! An Error Occurred', $response->getContent());
    }
}
