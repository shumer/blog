<?php
/**
 * Helper functions.
 */

/**
 * Check if current theme is admin.
 * @param string $theme
 * @return boolean
 */
function dart_tools_is_admin_theme($theme = NULL) {
  if (empty($theme)) {
    $theme = \Drupal::theme()->getActiveTheme()->getName();
  }

  $admin_theme = \Drupal::config('system.theme')->get('admin');
  return !empty($admin_theme) && $admin_theme == $theme || empty($admin_theme);
}
