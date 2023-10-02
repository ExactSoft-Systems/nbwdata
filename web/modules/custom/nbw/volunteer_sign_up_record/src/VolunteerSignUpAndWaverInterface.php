<?php

namespace Drupal\volunteer_sign_up_record;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a volunteer sign up and waver entity type.
 */
interface VolunteerSignUpAndWaverInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
