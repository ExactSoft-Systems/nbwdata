<?php

namespace Drupal\class_session;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a class session entity type.
 */
interface ClassSessionInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
