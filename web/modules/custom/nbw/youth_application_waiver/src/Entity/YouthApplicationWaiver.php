<?php

namespace Drupal\youth_application_waiver\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\youth_application_waiver\YouthApplicationWaiverInterface;

/**
 * Defines the youth application waiver entity class.
 *
 * @ContentEntityType(
 *   id = "youth_application_waiver",
 *   label = @Translation("Youth Application Waiver"),
 *   label_collection = @Translation("Youth Application Waivers"),
 *   label_singular = @Translation("youth application waiver"),
 *   label_plural = @Translation("youth application waivers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count youth application waivers",
 *     plural = "@count youth application waivers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\youth_application_waiver\YouthApplicationWaiverListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\youth_application_waiver\YouthApplicationWaiverAccessControlHandler",
 *     "form" = {
 *       "add" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverForm",
 *       "step_1" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep1",
 *       "step_2" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep2",
 *       "step_3" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep3",
 *       "step_4" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep4",
 *       "step_5" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep5",
 *       "step_6" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep6",
 *       "step_7" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep7",
 *       "step_8" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep8",
 *       "step_9" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverAddStep9",
 *       "edit" = "Drupal\youth_application_waiver\Form\YouthApplicationWaiverForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "youth_application_waiver",
 *   data_table = "youth_application_waiver_field_data",
 *   revision_table = "youth_application_waiver_revision",
 *   revision_data_table = "youth_application_waiver_field_revision",
 *   show_revision_ui = FALSE,
 *   translatable = TRUE,
 *   admin_permission = "administer youth application waiver",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "langcode" = "langcode",
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
 *     "collection" = "/admin/content/youth-waivers",
 *     "add" = "/youth-waivers/create",
 *     "canonical" = "/youth-waiver/{youth_application_waiver}",
 *     "edit-form" = "/youth-waiver/{youth_application_waiver}/edit",
 *     "delete-form" = "/youth-waiver/{youth_application_waiver}/delete",
 *     "create" = "/youth-waivers/create"
 *   },
 *   field_ui_base_route = "entity.youth_application_waiver.settings",
 * )
 */
class YouthApplicationWaiver extends RevisionableContentEntityBase implements YouthApplicationWaiverInterface {

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

    $fields['notes'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setLabel(t('Notes'))
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
      ->setTranslatable(TRUE)
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
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the youth application waiver was created.'))
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
      ->setTranslatable(TRUE)
      ->setDescription(t('The time that the youth application waiver was last edited.'));

    return $fields;
  }

}
