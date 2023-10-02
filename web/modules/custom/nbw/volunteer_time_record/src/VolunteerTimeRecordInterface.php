<?php

namespace Drupal\volunteer_time_record;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a volunteer time record entity type.
 */
interface VolunteerTimeRecordInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
