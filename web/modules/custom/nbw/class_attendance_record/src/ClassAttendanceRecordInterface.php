<?php

namespace Drupal\class_attendance_record;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a class attendance record entity type.
 */
interface ClassAttendanceRecordInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
