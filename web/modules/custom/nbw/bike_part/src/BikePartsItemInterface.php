<?php

namespace Drupal\bike_part;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a bike parts item entity type.
 */
interface BikePartsItemInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
