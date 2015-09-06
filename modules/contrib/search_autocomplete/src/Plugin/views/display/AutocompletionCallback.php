<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\Plugin\views\display\AutocompletionCallback.
 *
 * Inspired by rest core module.
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\search_autocomplete\Plugin\views\display;

use Drupal\Core\State\StateInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\ContentNegotiation;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\PathPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouteCollection;
use Drupal\Component\Utility\String;

/**
 * The plugin that handles Data response callbacks for REST resources.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "autocompletion_callback",
 *   title = @Translation("Autocompletion Callback"),
 *   help = @Translation("Create an autocompletion callback resource."),
 *   uses_route = TRUE,
 *   admin = @Translation("Autocompletion Callback"),
 *   returns_response = TRUE,
 *   autocompletion_callback_display = TRUE
 * )
 */
class AutocompletionCallback extends PathPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAJAX.
   */
  protected $usesAJAX = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesPager.
   */
  protected $usesPager = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesMore.
   */
  protected $usesMore = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesAreas.
   */
  protected $usesAreas = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\display\DisplayPluginBase::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * Overrides the content type of the data response, if needed.
   *
   * @var string
   */
  protected $contentType = 'json';

  /**
   * The mime type for the response.
   *
   * @var string
   */
  protected $mimeType;

  /**
   * The content negotiation library.
   *
   * @var \Drupal\Core\ContentNegotiation
   */
  protected $contentNegotiation;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   * @param \Drupal\Core\ContentNegotiation $content_negotiation
   *   The content negotiation library.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteProviderInterface $route_provider, StateInterface $state, ContentNegotiation $content_negotiation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_provider, $state);
    $this->contentNegotiation = $content_negotiation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('router.route_provider'),
      $container->get('state'),
      $container->get('content_negotiation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function initDisplay(ViewExecutable $view, array &$display, array &$options = NULL) {
    parent::initDisplay($view, $display, $options);

    $request_content_type = $this->contentNegotiation->getContentType($this->view->getRequest());
    // Only use the requested content type if it's not 'html'. If it is then
    // default to 'json' to aid debugging.
    // @todo Remove the need for this when we have better content negotiation.
    if ($request_content_type != 'html') {
      $this->setContentType($request_content_type);
    }

    $this->setMimeType($this->view->getRequest()->getMimeType($this->contentType));
  }

  /**
   * {@inheritdoc}
   */
  protected function getType() {
    return 'data';
  }

  /**
   * Sets the request content type.
   *
   * @param string $mime_type
   *   The response mime type. E.g. 'application/json'.
   */
  public function setMimeType($mime_type) {
    $this->mimeType = $mime_type;
  }

  /**
   * Gets the mime type.
   *
   * This will return any overridden mime type, otherwise returns the mime type
   * from the request.
   *
   * @return string
   *   The response mime type. E.g. 'application/json'.
   */
  public function getMimeType() {
    return $this->mimeType;
  }

  /**
   * Sets the content type.
   *
   * @param string $content_type
   *   The content type machine name. E.g. 'json'.
   */
  public function setContentType($content_type) {
    $this->contentType = $content_type;
  }

  /**
   * Gets the content type.
   *
   * @return string
   *   The content type machine name. E.g. 'json'.
   */
  public function getContentType() {
    return $this->contentType;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    // Set the default style plugin to 'json'.
    $options['style']['contains']['type']['default'] = 'serializer';
    $options['defaults']['default']['style'] = FALSE;
    $options['defaults']['default']['row'] = FALSE;

    // Remove css/exposed form settings, as they are not used for the data display.
    unset($options['exposed_form']);
    unset($options['exposed_block']);
    unset($options['css_class']);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    unset($categories['page'], $categories['exposed']);
    // Hide some settings, as they aren't useful for pure data output.
    unset($options['show_admin_links'], $options['analyze-theme']);

    $categories['path'] = array(
      'title' => $this->t('Path settings'),
      'column' => 'second',
      'build' => array(
        '#weight' => -10,
      ),
    );

    $options['path']['category'] = 'path';
    $options['path']['title'] = $this->t('Path');

    // Remove css/exposed form settings, as they are not used for the data
    // display.
    unset($options['exposed_form']);
    unset($options['exposed_block']);
    unset($options['css_class']);
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    parent::execute();

    $output = $this->view->render();
    return new Response(drupal_render_root($output), 200, array('Content-type' => $this->getMimeType()));
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = array();
    $build['#markup'] = $this->view->style_plugin->render();

    // Wrap the output in a pre tag if this is for a live preview.
    // Also little trick added to preview a formatted json for easier debug.
    if (!empty($this->view->live_preview)) {
      $dump = json_decode($build['#markup']);
      $build['#prefix'] = '<pre>';
      $build['#markup'] = json_encode($dump, JSON_PRETTY_PRINT);
      $build['#suffix'] = '</pre>';
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * The DisplayPluginBase preview method assumes we will be returning a render
   * array. The data plugin will already return the serialized string.
   */
  public function preview() {
    return $this->view->render();
  }

}
