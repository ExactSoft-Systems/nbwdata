<?php

namespace Drupal\nbw_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Create hash from string or array.
 *
 * @see https://roytanck.com/2021/10/17/generating-short-hashes-in-php/
 *
 * created hash length default value: 8
 *
 * Usage:
 *
 * @code
 * process:
 *   baz:
 *     plugin: create_hash
 *     source:
 *       - foo
 *       - bar
 *     length: 10
 * @endcode
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "create_hash"
 * )
 */
class CreateHash extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      $value = implode(':', $value);
    }
    $length = $this->configuration['lenght'] ?? 8;
    // Create a raw binary sha256 hash and base64 encode it.
    $hash_base64 = base64_encode(hash('sha256', $value, true ));
    // Replace non-urlsafe chars to make the string urlsafe.
    $hash_urlsafe = strtr($hash_base64, '+/', '-_');
    // Trim base64 padding characters from the end.
    $hash_urlsafe = rtrim($hash_urlsafe, '=');
    // Shorten the string before returning.
    return substr($hash_urlsafe, 0, $length);
  }
}
