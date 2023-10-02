<?php

namespace Drupal\bike_in_kind_donation_receipt;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a bicycle/in-kind donation receipt entity type.
 */
interface BikeInKindDonationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
