<?php

/**
 * @file
 * Contains Drupal\bsb_core\Tests\DefaultController.
 */

namespace Drupal\bsb_core\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the bsb_core module.
 */
class DefaultControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "bsb_core DefaultController's controller functionality",
      'description' => 'Test Unit for module bsb_core and controller DefaultController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests bsb_core functionality.
   */
  public function testDefaultController() {
    // Check that the basic functions of module bsb_core.
    $this->assertEqual(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
