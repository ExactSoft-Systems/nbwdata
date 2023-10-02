<?php

namespace Drupal\class_registration_submission;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a class registration submission entity type.
 */
interface ClassRegistrationSubmissionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
