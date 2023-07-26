<?php

namespace Drupal\attendance_record;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining an attendance record entity type.
 */
interface AttendanceRecordInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the attendance record creation timestamp.
   *
   * @return int
   *   Creation timestamp of the attendance record.
   */
  public function getCreatedTime();

  /**
   * Sets the attendance record creation timestamp.
   *
   * @param int $timestamp
   *   The attendance record creation timestamp.
   *
   * @return \Drupal\attendance_record\AttendanceRecordInterface
   *   The called attendance record entity.
   */
  public function setCreatedTime($timestamp);

}
