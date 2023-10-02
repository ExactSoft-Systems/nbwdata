<?php

namespace Drupal\youth_application_waiver;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a youth application waiver entity type.
 */
interface YouthApplicationWaiverInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
