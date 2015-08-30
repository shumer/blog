<?php

/**
 * @file
 * Contains Drupal\site_common\Controller\SiteCommonDatabaseController.
 */

namespace Drupal\site_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


class SiteCommonDatabaseController extends ControllerBase {

  public function export() {

    $connection = \Drupal\Core\Database\Database::getConnection('default');
    $options = $connection->getConnectionOptions();

    $db_name = $options['database'];
    $db_password = $options['password'];
    $db_user = $options['username'];

    $file = 'db_dump_' . time() . '.sql';
    $filename = file_create_filename($file, file_directory_temp());

    $command = "mysqldump -u$db_user -p$db_password $db_name > $filename";

    $result = `$command`;

    $response = new BinaryFileResponse($filename);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
    return $response;
  }
}
