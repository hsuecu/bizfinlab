<?php

namespace Drupal\Tests\views_url_alias\Kernel;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\content_translation\ContentTranslationManager;

/**
 * Kernel tests for the views_url_alias functionality.
 *
 * @group views_url_alias
 */
class EntitySaveInViewsUrlAliasTableTest extends KernelTestBase {
  use ContentTypeCreationTrait;

  /**
   * DB connection object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Modules to enable during the test.
   *
   * @var string[]
   */
  protected static $modules = [
    'node',
    'field',
    'path_alias',
    'system',
    'user',
    'language',
    'views_url_alias',
    'content_translation',
  ];

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The content translation manager service.
   *
   * @var \Drupal\content_translation\ContentTranslationManager
   */
  protected ContentTranslationManager $contentTranslationManager;

  /**
   * The test node entity.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected Node $node;

  /**
   * The base alias for the node.
   *
   * @var string
   */
  protected string $alias = '/test-alias';

  /**
   * List of language codes to create translations for.
   *
   * @var string[]
   */
  protected array $translationLanguages = ['fr', 'br', 'de'];

  /**
   * Sets up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    // Inject required services.
    $this->database = $this->container->get('database');
    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->contentTranslationManager = $this->container->get('content_translation.manager');

    // Install necessary schemas and configurations.
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['field', 'language', 'system']);
    $this->installEntitySchema('path_alias');
    $this->installSchema('views_url_alias', ['views_url_alias']);

    // Setup default language.
    $this->config('system.site')
      ->set('default_langcode', 'en')
      ->save();

    // Create additional languages.
    $language_storage = $this->entityTypeManager->getStorage('configurable_language');
    foreach ($this->translationLanguages as $language) {
      $language_storage->create(['id' => $language])->save();
    }

    // Create a content type for the node.
    $node_type_storage = $this->entityTypeManager->getStorage('node_type');
    $node_type_storage->create([
      'type' => 'basic_page',
      'name' => 'Basic Page',
    ])->save();

    // Enable translation for the content type.
    $this->contentTranslationManager->setEnabled('node', 'basic_page', TRUE);

    // Create the node entity.
    $node_storage = $this->entityTypeManager->getStorage('node');
    $this->node = $node_storage->create([
      'type' => 'basic_page',
      'title' => 'Test node',
      'langcode' => 'en',
    ]);
    $this->node->save();

    // Create path alias for the original node.
    $pathAliasStorage = $this->entityTypeManager->getStorage('path_alias');
    $pathAliasStorage->create([
      'path' => '/node/' . $this->node->id(),
      'alias' => $this->alias,
      'langcode' => 'en',
    ])->save();

    // Create translations and corresponding path aliases.
    foreach ($this->translationLanguages as $language) {
      $translation = $this->node->addTranslation($language, [
        'title' => 'Test for language ' . $language,
      ]);
      $translation->save();

      $pathAliasStorage->create([
        'path' => '/node/' . $translation->id(),
        'alias' => '/' . $language . $this->alias,
        'langcode' => $language,
      ])->save();
    }
  }

  /**
   * Asserts a views_url_alias record exists with the expected values.
   *
   * @param string $langcode
   *   The language code to assert.
   * @param string $expectedAlias
   *   The expected alias.
   */
  protected function assertViewsUrlAliasRecord(string $langcode, string $expectedAlias): void {
    $record = $this->database->select('views_url_alias', 'v')
      ->fields('v')
      ->condition('entity_id', $this->node->id())
      ->condition('entity_type', 'node')
      ->condition('langcode', $langcode)
      ->execute()
      ->fetchAssoc();

    $this->assertNotEmpty($record);
    $this->assertEquals($expectedAlias, $record['alias']);
    $this->assertEquals($langcode, $record['langcode']);
  }

  /**
   * Executes the views_url_alias rebuild batch.
   */
  protected function executeViewsUrlAliasRebuildBatch(): void {
    $this->container->get('module_handler')->loadInclude('views_url_alias', 'module');

    views_url_alias_rebuild_path();

    $batch = &batch_get();
    $this->assertNotEmpty($batch, 'Batch definition was not created.');
    $this->assertNotEmpty($batch['sets'][0]['operations'], 'No batch operations were registered.');

    foreach ($batch['sets'][0]['operations'] as $operation) {
      [$callback, $args] = $operation;

      $context = [
        'sandbox' => [],
        'results' => [],
        'finished' => 1,
        'message' => '',
      ];

      $callback(...array_merge($args, [&$context]));
    }
  }

  /**
   * Tests that the default language alias is saved in views_url_alias table.
   */
  public function testEntitySaveInViewsUrlAliasTable(): void {
    $defaultLanguage = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $this->assertViewsUrlAliasRecord($defaultLanguage, $this->alias);
  }

  /**
   * Tests translation aliases are saved correctly in views_url_alias table.
   */
  public function testEntityTranslationSaveInViewsUrlAliasTable(): void {
    foreach ($this->translationLanguages as $language) {
      $this->assertViewsUrlAliasRecord($language, '/' . $language . $this->alias);
    }
  }

  /**
   * Tests rebuild of views_url_alias table.
   */
  public function testRebuildOfViewsUrlAliasTable(): void {
    $this->database->truncate('views_url_alias')->execute();
    $this->executeViewsUrlAliasRebuildBatch();

    $defaultLanguage = \Drupal::languageManager()->getDefaultLanguage()->getId();
    $this->assertViewsUrlAliasRecord($defaultLanguage, $this->alias);

    foreach ($this->translationLanguages as $language) {
      $this->assertViewsUrlAliasRecord($language, '/' . $language . $this->alias);
    }
  }

}
