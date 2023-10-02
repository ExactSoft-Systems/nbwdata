<?php

namespace Drupal\bike_in_kind_donation_receipt\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\bike_in_kind_donation_receipt\BikeInKindDonationInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the bicycle/in-kind donation receipt entity class.
 *
 * @ContentEntityType(
 *   id = "bike_in_kind_donation",
 *   label = @Translation("Bicycle/In-Kind Donation Receipt"),
 *   label_collection = @Translation("Bicycle/In-Kind Donation Receipts"),
 *   label_singular = @Translation("bicycle/in-kind donation receipt"),
 *   label_plural = @Translation("bicycle/in-kind donation receipts"),
 *   label_count = @PluralTranslation(
 *     singular = "@count bicycle/in-kind donation receipts",
 *     plural = "@count bicycle/in-kind donation receipts",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\bike_in_kind_donation_receipt\BikeInKindDonationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\bike_in_kind_donation_receipt\Form\BikeInKindDonationForm",
 *       "edit" = "Drupal\bike_in_kind_donation_receipt\Form\BikeInKindDonationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "bike_in_kind_donation",
 *   revision_table = "bike_in_kind_donation_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer bike in kind donation",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "collection" = "/admin/content/bike-in-kind-donation",
 *     "add-form" = "/bike-in-kind-donation/add",
 *     "canonical" = "/bike-in-kind-donation/{bike_in_kind_donation}",
 *     "edit-form" = "/bike-in-kind-donation/{bike_in_kind_donation}/edit",
 *     "delete-form" = "/bike-in-kind-donation/{bike_in_kind_donation}/delete",
 *   },
 *   field_ui_base_route = "entity.bike_in_kind_donation.settings",
 * )
 */
class BikeInKindDonation extends RevisionableContentEntityBase implements BikeInKindDonationInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setRevisionable(TRUE)
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setRevisionable(TRUE)
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(static::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the bicycle/in-kind donation receipt was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the bicycle/in-kind donation receipt was last edited.'));

    return $fields;
  }

}
