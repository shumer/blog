<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\Plugin\views\style\Serializer.
 *
 * Inspired by rest core module.
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\search_autocomplete\Plugin\views\style;

use Drupal\node\Entity\NodeType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Drupal\Component\Utility\String;

/**
 * The style plugin for serialized output formats.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "serializer",
 *   title = @Translation("Serializer"),
 *   help = @Translation("Serializes views row data using the Serializer component."),
 *   display_types = {"callback"}
 * )
 */
class Serializer extends StylePluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\style\StylePluginBase::$usesRowPlugin.
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Overrides Drupal\views\Plugin\views\style\StylePluginBase::$usesFields.
   */
  protected $usesGrouping = TRUE;

  /**
   * The serializer which serializes the views result.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * Stores the content types defined. This is used for machine to human name
   * conversion of content types.
   *
   * @var array
   */
  protected $types = array();

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('serializer'),
      $container->getParameter('serializer.formats')
    );
  }

  /**
   * Constructs a Plugin object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SerializerInterface $serializer, array $serializer_formats) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->definition = $plugin_definition + $configuration;
    $this->serializer = $serializer;
    $this->formats = $serializer_formats;
    $this->types = NodeType::loadMultiple();
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Unset unecessary configurations.
    unset($form['grouping']['0']['rendered']);
    unset($form['grouping']['0']['rendered_strip']);
    unset($form['grouping']['0']['rendered_strip']);
    unset($form['grouping']['1']);

    // Add custom options.
    $field_labels = $this->displayHandler->getFieldLabels(TRUE);

    // Build the input field option.
    $input_label_descr = (empty($field_labels) ? '<b>' . t('Warning') . ': </b> ' . t('Requires at least one field in the view.') . '<br/>' : '') . t('Select the autocompletion input value. If the autocompletion settings are set to auto-submit, this value will be submitted as the suggestion is selected.');
    $form['input_label'] = array(
      '#title'          => t('Input Label'),
      '#type'           => 'select',
      '#description'    => String::checkPlain($input_label_descr),
      '#default_value'  => $this->options['input_label'],
      '#disabled'       => empty($field_labels),
      '#required'       => TRUE,
      '#options'        => $field_labels,
    );

    // Build the link field option.
    $input_link_descr = (empty($field_labels) ? '<b>' . t('Warning') . ': </b> ' . t('Requires at least one field in the view.') . '<br/>' : '') . t('Select the autocompletion input link. If the autocompletion settings are set to auto-redirect, this link is where the user will be redirected as the suggestion is selected.');
    $form['input_link'] = array(
      '#title'          => t('Input Link'),
      '#type'           => 'select',
      '#description'    => String::checkPlain($input_link_descr),
      '#default_value'  => $this->options['input_link'],
      '#disabled'       => empty($field_labels),
      '#required'       => TRUE,
      '#options'        => $field_labels,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {

    // Group the rows according to the grouping instructions, if specified.
    $groups = $this->renderGrouping(
        $this->view->result,
        $this->options['grouping'],
        TRUE
    );

    return $this->serializer->serialize($groups, 'json');
  }

  /**
   * {@inheritdoc}
   */
  public function renderGrouping($records, $groupings = array(), $group_rendered = NULL) {

    $rows = array();
    $groups = array();

    // Iterate through all records for transformation.
    foreach ($records as $index => $row) {

      $this->view->rowPlugin->setRowOptions($this->options);

      // Render the row according to our custom needs.
      $rendered_row = $this->view->rowPlugin->render($row);

      // Case when it takes grouping.
      if ($groupings) {

        // Iterate through configured grouping field.
        // Currently only one level of grouping allowed.
        foreach ($groupings as $info) {

          $group_field_name = $info['field'];
          $group_id = '';
          $group_name = '';

          // Extract group data if available.
          if (isset($this->view->field[$group_field_name])) {
            // Extract group_id and transform it to machine name.
            $group_id = strtolower(str_replace(' ', '-', $this->getField($index, $group_field_name)));
            // Extract group displayed value.
            $group_name = $this->renderField($index, $group_field_name) . 's';
          }

          // Create the group if it does not exist yet.
          if (empty($groups[$group_id])) {
            $groups[$group_id]['group']['group_id'] = $group_id;
            $groups[$group_id]['group']['group_name'] = $group_name;
            $groups[$group_id]['rows'] = array();
          }

          // Move the set reference into the row set of the group
          // we just determined.
          $rows = &$groups[$group_id]['rows'];
        }
      }
      else {
        // Create the group if it does not exist yet.
        if (empty($groups[''])) {
          $groups['']['group'] = '';
          $groups['']['rows'] = array();
        }
        $rows = &$groups['']['rows'];
      }
      // Add the row to the hierarchically positioned
      // row set we just determined.
      $rows[] = $rendered_row;
    }

    /*
     * Build the result from previous array.
     * @todo: find a more straight forward way to make it.
     */
    $return = array();
    foreach ($groups as $group_id => $group) {
      // Add group info on first row lign.
      if (isset($group['rows']) && isset($group['rows'][0])) {
        $group['rows'][0]['group'] = $group['group'];
      }
      // Add rows of this group to result.
      $return = array_merge($return, $group['rows']);
    }
    return $return;
  }

  /**
   * This methods returns the render value of a field, plus, in the case of
   * content types, return the human name instead of machine name.
   *
   * @param String $index
   *   The index of the field to render.
   * @param String $field_type
   *   The field type to be rendered.
   *
   * @return string|null
   *   The output of the field (with content type converted as a human name if
   *   applicable), or NULL if it was empty.
   */
  protected function renderField($index, $field_type) {
    $value = $this->getField($index, $field_type);
    // Convert content type machine names to human names.
    if ($field_type == 'type' && isset($this->types[$value])) {
      $value = $this->types[$value]->get('name');
    }
    return $value;
  }
}
