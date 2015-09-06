<?php
/**
 * @file
 * Contains Drupal\bsb_core\Controller\AjaxController
 */
namespace Drupal\bsb_core\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InsertCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\user\Entity\User;

class AjaxController extends ControllerBase {

  public function userModal($user = '') {
    $options = ['width' => '50%'];
    $user = User::load($user);
    $builder = \Drupal::entityManager()->getViewBuilder('user');
    $renderer = \Drupal::service('renderer');
    $build = $builder->view($user, 'default');
    $content = $renderer->render($build);
    //$content = 'AAA';

    $response = new AjaxResponse();
    //$response->addCommand( new OpenModalDialogCommand(t('User info'), $content, $options) );
    $response->addCommand( new HtmlCommand('.bsb-ajax-info', $content) );
    return $response;
  }
}
