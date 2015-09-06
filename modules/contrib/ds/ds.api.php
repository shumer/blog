<?php

/**
 * @file
 * Hooks provided by Display Suite module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Modify the list of available ds field plugins.
 *
 * This hook may be used to modify plugin properties after they have been
 * specified by other modules.
 *
 * @param array $plugins
 *   An array of all the existing plugin definitions, passed by reference.
 *
 * @see \Drupal\views\Plugin\DsPluginManager
 */
function hook_ds_fields_info_alter(&$plugins) {
  if (isset($plugins['title'])) {
    $plugins['title']['description'] = t('My title');
  }
}

/**
 * Alter fields defined by Display Suite just before they get
 * rendered on the Field UI. Use this hook to inject fields
 * which you can't alter with hook_ds_fields_info_alter().
 *
 * Use this in edge cases, see ds_extras_ds_fields_ui_alter()
 * which adds fields chosen in Views UI. This also runs
 * when a layout has been chosen.
 *
 * @param $fields
 *   An array with fields which can be altered just before they get cached.
 * @param $entity_type
 *   The name of the entity type.
 */
function hook_ds_fields_ui_alter(&$fields, $context) {
  $fields['title'] = t('Extra title');
}

/**
 * Define theme functions for fields.
 *
 * This only is necessary when you're using the field settings
 * plugin which comes with the DS extras module and you want to
 * expose a special field theming function to the interface.
 *
 * The theme function gets $variables as the only parameter.
 * The optional configuration through the UI is in $variables['ds-config'].
 *
 * Note that 'theme_ds_field_' is always needed, so the suggestions can work.
 *
 * @return $field_theme_functions
 *   A collection of field theming functions.
 */
function hook_ds_field_theme_functions_info() {
  return array('theme_ds_field_mine' => t('Theme field'));
}

/**
 * Return configuration summary for the field format.
 *
 * As soon as you have hook_ds_fields and one of the fields
 * has a settings key, Display Suite will call this hook for the summary.
 *
 * @param $field
 *   The configuration of the field.
 *
 * @return $summary
 *   The summary to show on the Field UI.
 */
function hook_ds_field_format_summary($field) {
  return 'Field summary';
}

/**
 * Modify the layout settings just before they get saved.
 *
 * @param $record
 *   The record just before it gets saved into the database.
 * @param $form_state
 *   The form_state values.
 */
function hook_ds_layout_settings_alter($record, $form_state) {
  $record['settings']['hide_page_title'] = TRUE;
}

/**
 * Modify the field settings before they get saved.
 *
 * @param $field_settings
 *   A collection of field settings which keys are fields.
 * @param $form
 *   The current form which is submitted.
 * @param $form_state
 *   The form state with all its values.
 */
function hook_ds_field_settings_alter(&$field_settings, $form, $form_state) {
  $field_settings['title']['region'] = 'left';
}

/**
 * Alter the layout render array.
 *
 * @param $layout_render_array
 *   The render array
 * @param $context
 *   An array with the context that is being rendered. Available keys are
 *   - entity
 *   - entity_type
 *   - bundle
 *   - view_mode
 * @param array $vars
 *   All variables available for render. You can use this to add css classes.
 */
function hook_ds_pre_render_alter(&$layout_render_array, $context, &$vars) {
  $layout_render_array['left'][] = array('#markup' => 'cool!', '#weight' => 20);
  $vars['attributes']['class'][] = 'custom';
}

/**
 * Alter the region options in the field UI screen.
 *
 * This function is only called when a layout has been chosen.
 *
 * @param $context
 *   A collection of keys for the context. The keys are 'entity_type',
 *   'bundle' and 'view_mode'.
 * @param $region_info
 *   A collection of info for regions. The keys are 'region_options'
 *   and 'table_regions'.
 */
function hook_ds_layout_region_alter($context, &$region_info) {
  $region_info['region_options']['my_region'] = 'New region';
  $region_info['table_regions']['my_region'] = array(
    'title' => \Drupal\Component\Utility\SafeMarkup::checkPlain('New region'),
    'message' => t('No fields are displayed in this region'),
  );
}

/**
 * Alter the field label options.
 *
 * Note that you will either
 * update the preprocess functions or the field.html.twig file when
 * adding new options.
 *
 * @param $field_label_options
 *   A collection of field label options.
 */
function hook_ds_label_options_alter(&$field_label_options) {
  $field_label_options['label_after'] = t('Label after field');
}

/**
 * Alter the view mode just before it's rendered by the DS views entity plugin.
 *
 * @param $view_mode
 *   The name of the view mode.
 * @param $context
 *   A collection of items which can be used to identify in what
 *   context an entity is being rendered. The variable contains 3 keys:
 *     - entity: The entity being rendered.
 *     - view_name: the name of the view.
 *     - display: the name of the display of the view.
 */
function hook_ds_views_view_mode_alter(&$view_mode, $context) {
  if ($context['view_name'] == 'my_view_name') {
    $view_mode = 'new_view_mode';
  }
}

/**
 * Theme an entity through an advanced function coming from the views entity plugin.
 *
 * @param $entity
 *   The entity
 * @param $view_mode
 *   The name of the view mode.
 */
function hook_ds_views_row_render_entity($entity, $view_mode) {
  $entity = entity_load('node', 1);
  return entity_view($entity, $view_mode);
}

/**
 * Theme an entity through an advanced function coming from the views entity plugin.
 *
 * @param $build
 *   The builded entity
 * @param $context
 *   Collection of parameters (row, view and view_mode)
 */
function hook_ds_views_row_render_entity_alter(&$build, $context) {
  // You can do whatever you want to here.
  $build['data'] = array(
    '#markup' => 'Sample text',
    '#weight' => 20,
  );
}

/**
 * Allow modules to provide additional classes for regions and layouts.
 */
function hook_ds_classes_alter(&$classes, $name) {
  if ('ds_classes_regions' === $name) {
    $classes['css-class-name'] = t('Custom Styling');
  }
}

/**
 * @} End of "addtogroup hooks".
 */
