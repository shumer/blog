<?php
/**
 * @file
 * Contains \Drupal\bsb_core\Plugin\CKEditorPlugin\Codesnippet.
 */
namespace Drupal\bsb_core\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User is logged in" condition.
 *
 * @Condition(
 *   id = "logged_user",
 *   label = @Translation("UserIsCurrent"),
 *   context = {
 *     "current_user" = @ContextDefinition("entity:user", label = @Translation("Current user")),
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 * 
 */
class LoggedUser extends ConditionPluginBase implements ContainerFactoryPluginInterface
{

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Creates a new Vocabulary instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
        $container->get('entity.manager')->getStorage('taxonomy_vocabulary'),
        $configuration,
        $plugin_id,
        $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('The logged in user route');
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    $current_user = $this->getContextValue('current_user');
    $user_from_route = $this->getContextValue('user');
    return (($current_user == $user_from_route) && !$this->isNegated());
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration();
  }
}
