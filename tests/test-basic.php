<?php
use PHPUnit\Framework\TestCase;

class SimplePlacesBasicTest extends TestCase {

    public function setUp(): void {
        // Ensure 'init' ran so CPT is registered.
        do_action('init');
    }

    public function test_post_type_is_registered() {
        $this->assertNotNull(
            get_post_type_object('simple_place'),
            'simple_place CPT should be registered'
        );
    }

    public function test_shortcode_outputs_some_markup_or_message() {
        $out = do_shortcode('[simple_places]');
        $this->assertIsString($out);

        // Accept any reasonable empty-state or list output.
        $normalized = strtolower($out);
        $this->assertTrue(
            strpos($normalized, 'no places') !== false    // empty-state message
            || strpos($normalized, 'simple-places-list') !== false // our list class
            || strpos($normalized, '<em') !== false
            || strpos($normalized, '<ul') !== false,      // theme may wrap differently
            "Shortcode should output a message or a list; got:\n$out"
        );
    }

    public function test_shortcode_lists_created_place() {
        // Create a Place and its meta.
        $pid = wp_insert_post([
            'post_type'   => 'simple_place',
            'post_status' => 'publish',
            'post_title'  => 'Connaught Place',
        ]);
        $this->assertIsInt($pid);
        update_post_meta($pid, 'sp_lat', 28.6315);
        update_post_meta($pid, 'sp_lng', 77.2167);

        $out = do_shortcode('[simple_places]');
        $this->assertIsString($out);
        $this->assertStringContainsString('simple-places-list', $out);
        $this->assertStringContainsString('Connaught Place', $out);
        $this->assertStringContainsString('28.6315', $out);
        $this->assertStringContainsString('77.2167', $out);
    }
}
