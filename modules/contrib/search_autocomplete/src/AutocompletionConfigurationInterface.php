<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\AutocompletionConfigurationInterface.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\search_autocomplete;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Doctrine\Common\Annotations\Annotation\Enum;
use Drupal\migrate\Plugin\migrate\process\Callback;

/**
 * Provides an interface defining an autocompletion configuration entity.
 */
interface AutocompletionConfigurationInterface extends ConfigEntityInterface {

  /* -----------------------------
   * ---------  GETTERS  ---------
   */

  /**
   * Returns the field selector to apply the autocompletion on.
   *
   * @return string
   *   The field selector to apply the autocompletion on.
   */
  public function getSelector();

  /**
   * Returns the configuration status.
   *
   * @return boolean
   *   TRUE/FALSE depending if the configuration is set active.
   */
  public function getStatus();

  /**
   * Define how much characters needs to be entered in the field before
   * autocompletion occurs.
   *
   * @var int
   */
  public function getMinChar();

  /**
   * Returns how many suggestions should be displayed among matching suggestions
   * available.
   *
   * @return int
   *   The maximum number of suggestions to be displayed.
   */
  public function getMaxSuggestions();

  /**
   * Returns a suggestion label displayed when no results are available.
   *
   * @return string
   *   The suggestion label displayed when no results are available.
   */
  public function getNoResultLabel();

  /**
   * Returns a suggestion value entered when "no results" is choosen.
   *
   * @return string
   *   The suggestion value entered when "no results" is choosen.
   */
  public function getNoResultValue();

  /**
   * Returns a suggestion link redirection for when no results is selected.
   *
   * @return string
   *   The link user is redirected to, when "no results" is choosen.
   */
  public function getNoResultLink();

  /**
   * Returns a suggestion label displayed when more results are available.
   *
   * @return string
   *   The suggestion label displayed when more results are available.
   */
  public function getMoreResultsLabel();

  /**
   * Returns a suggestion value entered when "more results" is choosen.
   *
   * @return string
   *   The suggestion value entered when "more results" is choosen.
   */
  public function getMoreResultsValue();

  /**
   * Returns a suggestion link redirection for when "no results" is selected.
   *
   * @return string
   *   The link user is redirected to, when "more results" is selected.
   */
  public function getMoreResultsLink();

  /**
   * Returns the source of to retrieve suggestions.
   *
   * @return string
   *   The source name to retrieve suggestions.
   */
  public function getSource();

  /**
   * Returns the theme to use.
   *
   * @return string
   *   The CSS file name.
   */
  public function getTheme();

  /* -----------------------------
   * ---------  SETTERS  ---------
   */

  /**
   * Sets the field selector to apply the autocompletion on.
   *
   * @param string $selector
   *   The field selector to apply the autocompletion on.
   */
  public function setSelector($selector);

  /**
   * Sets the configuration status : wheter it is active or not.
   *
   * @param boolean $status
   *   TRUE/FALSE depending if the configuration is set active.
   */
  public function setStatus($status);

  /**
   * Sets how many characters needs to be entered in the field before
   * autocompletion occurs.
   *
   * @param int $min_char
   *   The number of characters to enter before autocompletion starts.
   */
  public function setMinChar($min_char);

  /**
   * Sets how many suggestions should be displayed among matching suggestions
   * available.
   *
   * @param int $max_suggestions
   *   The maximum number of suggestions to be displayed.
   */
  public function setMaxSuggestions($max_suggestions);

  /**
   * Sets a label when no result are available.
   *
   * @param string $no_result_label
   *   The label for "no result available" custom suggestion.
   */
  public function setNoResultLabel($no_result_label);

  /**
   * Sets a value when no result are available.
   *
   * @param string $no_result_value
   *   The value for "no result available" custom suggestion.
   */
  public function setNoResultValue($no_result_value);

  /**
   * Sets a link when no result are available.
   *
   * @param string $no_result_link
   *   The link for "no result available" custom suggestion.
   */
  public function setNoResultLink($no_result_link);

  /**
   * Sets a label when more result are available.
   *
   * @param string $more_results_label
   *   The label for "more result available" custom suggestion.
   */
  public function setMoreResultsLabel($more_results_label);

  /**
   * Sets a value when more result are available.
   *
   * @param string $more_results_value
   *   The value for "more result available" custom suggestion.
   */
  public function setMoreResultsValue($more_results_value);

  /**
   * Sets a link when more result are available.
   *
   * @param string $more_results_link
   *   The link for "more result available" custom suggestion.
   */
  public function setMoreResultsLink($more_results_link);

  /**
   * Sets the source to retrieve suggestions.
   *
   * @param string $source
   *   The source name to retrieve suggestions.
   */
  public function setSource($source);

  /**
   * Sets the theme to use for autocompletion display.
   *
   * @param string $theme
   *   The CSS filename for the theme.
   */
  public function setTheme($theme);
}
