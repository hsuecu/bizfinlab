<?php

declare(strict_types=1);

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure no status page errors with module enabled.
 *
 * @coversDefaultClass \Drupal\views_url_alias\Form\ViewsURLAliasAdminForm
 * @group views_url_alias
 */
class ViewsUrlAliasLoadTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['views_url_alias', 'views', 'views_ui', 'path_alias', 'path', 'system', 'user'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Required setting.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->user = $this->drupalCreateUser([
      'administer site configuration',
      'administer views',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Tests that the config page loads with a 200 response.
   */
  public function testLoad() {
    $this->drupalGet(Url::fromRoute('views_url_alias.views_url_alias_admin_form'));
    $this->assertSession()->statusCodeEquals(200);
  }

}
