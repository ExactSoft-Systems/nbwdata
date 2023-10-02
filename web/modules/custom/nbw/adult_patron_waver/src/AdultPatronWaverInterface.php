<?php

namespace Drupal\adult_patron_waver;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a bike church patron waiver entity type.
 */
interface AdultPatronWaverInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
