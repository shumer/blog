<?php

/**
 * @file
 * Contains \Drupal\search_api\Tests\Processor\ProcessorIntegrationTest.
 */

namespace Drupal\search_api\Tests\Processor;

use Drupal\search_api\Entity\Index;
use Drupal\search_api\Tests\WebTestBase;

/**
 * Tests the admin UI for processors.
 *
 * @group search_api
 */
// @todo Move this whole class into a single IntegrationTest check*() method?
// @todo Add tests for the "Aggregated fields" and "Role filter" processors.
class ProcessorIntegrationTest extends WebTestBase {

  /**
   * The ID of the search index used by this test.
   *
   * @var string
   */
  protected $indexId;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);

    $this->indexId = 'test_index';
    Index::create(array(
      'name' => 'Test index',
      'id' => $this->indexId,
      'status' => 1,
      'datasources' => array('entity:node'),
    ))->save();
  }

  /**
   * Tests the admin UI for processors.
   *
   * Calls the other test methods in this class, named check*Integration(), to
   * avoid the overhead of having one test per processor.
   */
  public function testProcessorIntegration() {
    $this->checkContentAccessIntegration();
    $this->checkHighlightIntegration();
    $this->checkHtmlFilterIntegration();
    $this->checkIgnoreCaseIntegration();
    $this->checkIgnoreCharactersIntegration();
    $this->checkLanguageIntegration();
    $this->checkNodeStatusIntegration();
    $this->checkRenderedItemIntegration();
    $this->checkStopWordsIntegration();
    $this->checkTokenizerIntegration();
    $this->checkTransliterationIntegration();
    $this->checkUrlFieldIntegration();
  }

  /**
   * Tests the UI for the "Content access" processor.
   */
  public function checkContentAccessIntegration() {
    $this->enableProcessor('content_access');
  }

  /**
   * Tests the UI for the "Highlight" processor.
   */
  public function checkHighlightIntegration() {
    $this->enableProcessor('highlight');

    $edit = array(
      'processors[highlight][settings][highlight]' => 'never',
      'processors[highlight][settings][excerpt]' => FALSE,
      'processors[highlight][settings][excerpt_length]' => 128,
      'processors[highlight][settings][prefix]' => '<em>',
      'processors[highlight][settings][suffix]' => '</em>',
    );
    $this->editSettingsForm($edit, 'highlight');
  }

  /**
   * Tests the UI for the "HTML filter" processor.
   */
  public function checkHtmlFilterIntegration() {
    // Enable the "HTML filter" processor in the same request as setting the
    // settings, since we otherwise run into a weird bug only present in the
    // testing environment regarding the YAML in the "tags" setting.
    $edit = array(
      'status[html_filter]' => 1,
      'processors[html_filter][settings][fields][search_api_language]' => FALSE,
      'processors[html_filter][settings][title]' => FALSE,
      'processors[html_filter][settings][alt]' => FALSE,
      'processors[html_filter][settings][tags]' => 'h1: 10'
    );
    $this->editSettingsForm($edit, 'html_filter');
  }

  /**
   * Tests the UI for the "Ignore case" processor.
   */
  public function checkIgnoreCaseIntegration() {
    $this->enableProcessor('ignorecase');

    $edit = array(
      'processors[ignorecase][settings][fields][search_api_language]' => FALSE,
    );
    $this->editSettingsForm($edit, 'ignorecase');
  }

  /**
   * Tests the UI for the "Ignore characters" processor.
   */
  public function checkIgnoreCharactersIntegration() {
    $this->enableProcessor('ignore_character');

    $edit = array(
      'processors[ignore_character][settings][fields][search_api_language]' => FALSE,
      'processors[ignore_character][settings][ignorable]' => '[¿¡!?,.]',
      'processors[ignore_character][settings][strip][character_sets][Cc]' => TRUE,
    );
    $this->editSettingsForm($edit, 'ignore_character');
  }

  /**
   * Tests the UI for the "Language" processor.
   */
  public function checkLanguageIntegration() {
    $index = $this->loadIndex();
    $processors = $index->getOption('processors', array());
    $this->assertTrue(!empty($processors['language']), 'The "language" processor is enabled by default.');
    unset($processors['language']);
    $index->setOption('processors', $processors)->save();
    $processors = $this->loadIndex()->getProcessors();
    $this->assertTrue(!empty($processors['language']), 'The "language" processor cannot be disabled.');
  }

  /**
   * Tests the UI for the "Node status" processor.
   */
  public function checkNodeStatusIntegration() {
    $this->enableProcessor('node_status');
  }

  /**
   * Tests the UI for the "Rendered item" processor.
   */
  public function checkRenderedItemIntegration() {
    $this->enableProcessor('rendered_item');

    $edit = array(
      'processors[rendered_item][settings][roles][]' => 'authenticated',
      'processors[rendered_item][settings][view_mode][entity:node][page]' => 'default',
      'processors[rendered_item][settings][view_mode][entity:node][article]' => 'default',
    );
    $this->editSettingsForm($edit, 'rendered_item');
  }

  /**
   * Tests the UI for the "Stopwords" processor.
   */
  public function checkStopWordsIntegration() {
    $this->enableProcessor('stopwords');

    $edit = array(
      'processors[stopwords][settings][stopwords]' => 'the',
    );
    $this->editSettingsForm($edit, 'stopwords');
  }

  /**
   * Tests the UI for the "Tokenizer" processor.
   */
  public function checkTokenizerIntegration() {
    $this->enableProcessor('tokenizer');

    $edit = array(
      'processors[tokenizer][settings][spaces]' => '',
      'processors[tokenizer][settings][overlap_cjk]' => FALSE,
      'processors[tokenizer][settings][minimum_word_size]' => 2,
    );
    $this->editSettingsForm($edit, 'tokenizer');
  }

  /**
   * Tests the UI for the "Transliteration" processor.
   */
  public function checkTransliterationIntegration() {
    $this->enableProcessor('transliteration');

    $edit = array(
      'processors[transliteration][settings][fields][search_api_language]' => FALSE,
    );
    $this->editSettingsForm($edit, 'transliteration');
  }

  /**
   * Tests the UI for the "URL field" processor.
   */
  public function checkUrlFieldIntegration() {
    $this->enableProcessor('add_url');
  }

  /**
   * Tests that a processor can be enabled.
   *
   * @param string $processor_id
   *   The ID of the processor to enable.
   */
  protected function enableProcessor($processor_id) {
    $this->loadProcessorsTab();

    $edit = array(
      "status[$processor_id]" => 1,
    );
    $this->drupalPostForm(NULL, $edit, $this->t('Save'));
    $processors = $this->loadIndex()->getProcessors();
    $this->assertTrue(!empty($processors[$processor_id]), "Successfully enabled the '$processor_id' processor.'");
  }

  /**
   * Edits a processor's settings.
   *
   * @param array $edit
   *   The settings to set for the processor.
   * @param string $processor_id
   *   The ID of the processor whose settings are edited.
   */
  protected function editSettingsForm($edit, $processor_id) {
    $this->loadProcessorsTab();

    $this->drupalPostForm(NULL, $edit, $this->t('Save'));

    $processors = $this->loadIndex()->getProcessors();
    // @todo Actually test something here. Idea: pass in a $configuration array,
    //   convert to POST format in here automatically and then check if the
    //   processor's configuration matches.
    if (isset($processors[$processor_id])) {
      $configuration = $processors[$processor_id]->getConfiguration();
      $this->assertTrue(empty($configuration['fields']['search_api_language']));
    }
    else {
      $this->fail($processor_id . ' settings not applied.');
    }
  }

  /**
   * Loads the test index's "Processors" tab in the test browser, if necessary.
   */
  protected function loadProcessorsTab() {
    $settings_path = 'admin/config/search/search-api/index/' . $this->indexId . '/processors';
    if ($this->getAbsoluteUrl($settings_path) != $this->getUrl()) {
      $this->drupalGet($settings_path);
    }
  }

  /**
   * Loads the search index used by this test.
   *
   * @return \Drupal\search_api\IndexInterface
   *   The search index used by this test.
   */
  protected function loadIndex() {
    return entity_load('search_api_index', $this->indexId, TRUE);
  }

}
