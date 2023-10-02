<?php

namespace Drupal\bicycle;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a bike entity type.
 */
interface BikeInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
