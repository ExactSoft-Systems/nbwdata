<?php

declare(strict_types=1);

namespace Drupal\nbw_migrations\Plugin\migrate\process;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a convert_wild_date plugin.
 *
 * Convert date string, source data convert with regular expression. Use named caption group: year, mon, day.
 * Destination format: @see https://www.php.net/manual/en/datetime.format.php
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: convert_wild_date
 *     source: something_date_field
 *     from_format: '@(?<mon>\d+)[/-](?<day>\d+)[/-](?<year>\d+)@'
 *     to_format: 'Y-m-d'
 * @endcode
 *
 * @MigrateProcessPlugin(id = "convert_wild_date")
 */
final class ConvertWildDate extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): mixed {
    if (empty($value) && $value !== '0' && $value !== 0) {
      return '';
    }
    $from_format = $this->configuration['from_format'];
    $to_format = $this->configuration['to_format'];
    // Check configuration items.
    if (empty($from_format)) {
      throw new MigrateException('Empty form_format configuration.');
    }
    if (empty($to_format)) {
      throw new MigrateException('Empty to_format configuration.');
    }
    $regexp_result = preg_match($from_format, $value, $source_date_elements);
    if ($regexp_result === FALSE) {
      throw new MigrateException('form_format configuration is missing.');
    }
    elseif ($regexp_result === 0) {
      throw new MigrateException('form_format pattern does not matches.');
    }
    foreach (['year', 'mon', 'day'] as $item) {
      if (!isset($source_date_elements[$item])) {
        throw new MigrateException("from_format: $item named caption group is missing");
      }
      elseif (strlen($source_date_elements[$item]) == 1) {
        // add leading zero
        $source_date_elements[$item] = '0' . $source_date_elements[$item];
      }
    }
    if (strlen($source_date_elements['year']) == 2) {
      // y: A two digit representation of a year (which is assumed to be in the range 1970-2069, inclusive.
      $source_date_elements['year'] = \DateTime::createFromFormat('y', $source_date_elements['year'])->format('Y');
    }
    // Convert 'Y-m-d' format and convert destination date format, if neccessary.
    $value = "${source_date_elements['year']}-${source_date_elements['mon']}-${source_date_elements['day']}";
    if ($to_format !== 'Y-m-d') {
      $value = DateTimePlus::createFromFormat('Y-m-d', $value)->format($to_format);
    }
    return $value;
  }
}
