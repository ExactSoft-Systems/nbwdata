<?php

namespace Drupal\adult_patron_attendance_record;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining an adult patron attendance record entity type.
 */
interface AdultPatronAttendanceRecordInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
