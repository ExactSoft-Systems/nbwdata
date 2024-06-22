<?php

declare(strict_types=1);

namespace Drupal\nbw_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a convert_wild_date plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: convert_wild_date
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "convert_wild_date")
 */
final class ConvertWildDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): mixed {
    // @todo Transform the value here.
    return $value;
  }

}
