<?php

namespace Drupal\social_media_links;

class IconsetFinder {

  private static $installDirs = array();
  private static $searchDirs = array();
  private static $iconsets = FALSE;

  public static function getPath($id) {
    $iconsets = self::getIconsets();

    return isset($iconsets[$id]) ? $iconsets[$id] : FALSE;
  }

  public static function getSearchDirs() {
    if (empty(self::$searchDirs)) {
      return self::setSearchDirs();
    }
    return self::$searchDirs;
  }

  /**
   * Defines a list of the locations, where icon sets are searched.
   *
   * @return array
   */
  protected static function setSearchDirs() {
    $profile = drupal_get_path('profile', drupal_get_profile());
    $site_path = \Drupal::service('kernel')->getSitePath();

    // Similar to 'modules' and 'themes' directories inside an installation
    // profile, installation profiles may want to place libraries into a
    // 'libraries' directory.
    if (strpos($profile, "core") === FALSE) {
      $searchdirs[] = "$profile/libraries";
    }

    // Search sites/all/libraries for backwards-compatibility.
    $searchdirs[] = 'sites/all/libraries';

    // Always search the root 'libraries' directory.
    $searchdirs[] = 'libraries';

    // Also search sites/<domain>/*.
    $searchdirs[] = "$site_path/libraries";

    // Add the social_media_links module directory.
    $searchdirs[] = drupal_get_path('module', 'social_media_links') . '/libraries';

    return self::$searchDirs = $searchdirs;
  }

  public static function getInstallDirs() {
    if (empty(self::$installDirs)) {
      self::setInstallDirs();
    }

    return self::$installDirs;
  }

  public static function setInstallDirs() {
    $searchdirs = self::getSearchDirs();

    // Remove the core and soacial_media_links module directory from the
    // possible target directories for installation.
    foreach ($searchdirs as $key => $dir) {
      if (preg_match("/core|social_media_links/", $dir)) {
        unset($searchdirs[$key]);
      }
    }

    return self::$installDirs = $searchdirs;
  }

  protected static function getIconsets() {
    if (self::$iconsets === FALSE) {
      return self::setIconsets();
    }
    return self::$iconsets;
  }

  /**
   * Searches the directories for libraries (e.g. Icon Sets).
   *
   * Returns an array of library directories from the all-sites directory
   * (i.e. sites/all/libraries/), the profiles directory, and site-specific
   * directory (i.e. sites/somesite/libraries/). The returned array will be keyed
   * by the library name. Site-specific libraries are prioritized over libraries
   * in the default directories. That is, if a library with the same name appears
   * in both the site-wide directory and site-specific directory, only the
   * site-specific version will be listed.
   *
   * Most of the code in this function are borrowed from the libraries module
   * (http://drupal.org/project/libraries).
   *
   */
  protected static function setIconsets() {
    $searchdirs = self::getSearchDirs();

    // Retrieve list of directories.
    $directories = array();
    $nomask = array('CVS');
    foreach ($searchdirs as $dir) {
      if (is_dir($dir) && $handle = opendir($dir)) {
        while (FALSE !== ($file = readdir($handle))) {
          if (!in_array($file, $nomask) && $file[0] != '.') {
            if (is_dir("$dir/$file")) {
              $directories[$file] = "$dir/$file";
            }
          }
        }
        closedir($handle);
      }
    }

    return self::$iconsets = $directories;
  }

}
