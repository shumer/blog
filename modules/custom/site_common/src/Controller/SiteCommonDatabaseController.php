<?php

/**
 * @file
 * Contains Drupal\site_common\Controller\SiteCommonDatabaseController.
 */
namespace Drupal\site_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Core\Config\FileStorage;
use \Drupal\Core\Archiver\Zip;

class SiteCommonDatabaseController extends ControllerBase {

  public function exportDB() {

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

  public function exportConfig() {

    // Empty destination dir and then write all .yml files there.
    $source_storage = \Drupal::service('config.storage');
    $destination_storage = new FileStorage('/tmp/config');

    // Export configuration
    $file = 'config_export_' . time() . '.zip';
    $filename = '/tmp/config/' . $file;
    file_put_contents($filename, '');
    $zip = new Zip($filename);

    foreach ($source_storage->listAll() as $name) {
      $destination_storage->write($name, $source_storage->read($name));
      $zip->add($destination_storage->getFilePath($name));
    }

    $response = new BinaryFileResponse($filename);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file);
    return $response;

  }

}
