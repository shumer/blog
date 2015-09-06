<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\Plugin\views\row\DataFieldRow.
 *
 * Inspired by rest core module.
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\search_autocomplete\Plugin\views\row;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Plugin which displays fields as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "callback_field",
 *   title = @Translation("Fields"),
 *   help = @Translation("Use fields as row data."),
 *   display_types = {"callback"}
 * )
 */
class DataFieldRow extends RowPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::$usesFields.
   */
  protected $usesFields = TRUE;

  /**
   * Stores an array of prepared field aliases from options.
   *
   * @var array
   */
  protected $replacementAliases = array();

  /**
   * Stores the content types defined. This is used for machine to human name
   * conversion of content types.
   *
   * @var array
   */
  protected $types = array();

  protected $rowOptions = array();

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::init().
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    if (!empty($this->options['field_options'])) {
      $options = (array) $this->options['field_options'];
      // Prepare a trimmed version of replacement aliases.
      $aliases = static::extractFromOptionsArray('alias', $options);
      $this->replacementAliases = array_filter(array_map('trim', $aliases));
    }

    $this->types = NodeType::loadMultiple();
  }

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::buildOptionsForm().
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    return $options;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::buildOptionsForm().
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['field_options'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Field'), $this->t('Alias')),
      '#empty' => $this->t('You have no fields. Add some to your view.'),
      '#tree' => TRUE,
    );

    $options = $this->options['field_options'];

    if ($fields = $this->view->display_handler->getOption('fields')) {
      foreach ($fields as $id => $field) {
        $form['field_options'][$id]['field'] = array(
          '#markup' => $id,
        );
        $form['field_options'][$id]['alias'] = array(
          '#title' => $this->t('Alias for @id', array('@id' => $id)),
          '#title_display' => 'invisible',
          '#type' => 'textfield',
          '#default_value' => isset($options[$id]['alias']) ? $options[$id]['alias'] : '',
          '#element_validate' => array(array($this, 'validateAliasName')),
        );
      }
    }
  }

  /**
   * Setter for rowOptions. This will contains the specific options for the
   * display.
   *
   * @param array $options
   *   The options grouping, link and value of the display.
   */
  public function setRowOptions($options) {
    $this->rowOptions = $options;
  }

  /**
   * Form element validation handler for
   * \Drupal\search_autocomplete\Plugin\views\row\DataFieldRow::buildOptionsForm().
   */
  public function validateAliasName($element, FormStateInterface $form_state) {
    if (preg_match('@[^A-Za-z0-9_-]+@', $element['#value'])) {
      $form_state->setError($element, $this->t('The machine-readable name must contain only letters, numbers, dashes and underscores.'));
    }
  }

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::validateOptionsForm().
   */
  public function validateOptionsForm(&$form, FormStateInterface $form_state) {
    // Collect an array of aliases to validate.
    $aliases = static::extractFromOptionsArray('alias', $form_state->getValue(array('row_options', 'field_options')));

    // If array filter returns empty, no values have been entered. Unique keys
    // should only be validated if we have some.
    if (($filtered = array_filter($aliases)) && (array_unique($filtered) !== $filtered)) {
      $form_state->setErrorByName('aliases', $this->t('All field aliases must be unique'));
    }
  }

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::render().
   */
  public function render($row) {
    $output = array();

    // Render all fields.
    foreach ($this->view->field as $id => $field) {

      // Render the value.
      $value = $field->advancedRender($row);

      // Convert content type machine names to human names.
      if ($id == 'type' && isset($this->types[$value])) {
        $value = $this->types[$value]->get('name');
      }

      // Add input link.
      if ($this->rowOptions['input_link'] == $id) {
        $output['link'] = $value;
      }

      // Add value link.
      if ($this->rowOptions['input_label'] == $id) {
        $output['value'] = $value;
      }

      // Add label if defined.
      if ($field->options['label']) {
        $value = $field->options['label'] . ': ' . $value;
      }

      // If field is not excluded: include in response.
      if (!$field->options['exclude']) {
        $output['fields'][$this->getFieldKeyAlias($id)] = $value;
      }
    }

    return $output;
  }

  /**
   * Return an alias for a field ID, as set in the options form.
   *
   * @param string $id
   *   The field id to lookup an alias for.
   *
   * @return string
   *   The matches user entered alias, or the original ID if nothing is found.
   */
  public function getFieldKeyAlias($id) {
    if (isset($this->replacementAliases[$id])) {
      return $this->replacementAliases[$id];
    }

    return $id;
  }

  /**
   * Extracts a set of option values from a nested options array.
   *
   * @param string $key
   *   The key to extract from each array item.
   * @param array $options
   *   The options array to return values from.
   *
   * @return array
   *   A regular one dimensional array of values.
   */
  protected static function extractFromOptionsArray($key, $options) {
    return array_map(function($item) use ($key) {
      return isset($item[$key]) ? $item[$key] : NULL;
    }, $options);
  }

}
