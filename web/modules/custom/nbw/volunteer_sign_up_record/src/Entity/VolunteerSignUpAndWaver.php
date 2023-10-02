<?php

namespace Drupal\volunteer_sign_up_record\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerTrait;
use Drupal\volunteer_sign_up_record\VolunteerSignUpAndWaverInterface;

/**
 * Defines the volunteer sign up and waver entity class.
 *
 * @ContentEntityType(
 *   id = "volunteer_sign_up_and_waver",
 *   label = @Translation("Volunteer Sign Up And Waver"),
 *   label_collection = @Translation("Volunteer Sign Up And Wavers"),
 *   label_singular = @Translation("volunteer sign up and waver"),
 *   label_plural = @Translation("volunteer sign up and wavers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count volunteer sign up and wavers",
 *     plural = "@count volunteer sign up and wavers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\volunteer_sign_up_record\VolunteerSignUpAndWaverListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\volunteer_sign_up_record\Form\VolunteerSignUpAndWaverForm",
 *       "edit" = "Drupal\volunteer_sign_up_record\Form\VolunteerSignUpAndWaverForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "volunteer_sign_up_and_waver",
 *   revision_table = "volunteer_sign_up_and_waver_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer volunteer sign up and waver",
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
 *     "collection" = "/admin/content/volunteer-sign-up-and-waver",
 *     "add-form" = "/volunteer-sign-up-and-waver/add",
 *     "canonical" = "/volunteer-sign-up-and-waver/{volunteer_sign_up_and_waver}",
 *     "edit-form" = "/volunteer-sign-up-and-waver/{volunteer_sign_up_and_waver}/edit",
 *     "delete-form" = "/volunteer-sign-up-and-waver/{volunteer_sign_up_and_waver}/delete",
 *   },
 *   field_ui_base_route = "entity.volunteer_sign_up_and_waver.settings",
 * )
 */
class VolunteerSignUpAndWaver extends RevisionableContentEntityBase implements VolunteerSignUpAndWaverInterface {

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

    $fields['notes'] = BaseFieldDefinition::create('text_long')
      ->setRevisionable(TRUE)
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
      ->setDescription(t('The time that the volunteer sign up and waver was created.'))
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
      ->setDescription(t('The time that the volunteer sign up and waver was last edited.'));

    return $fields;
  }

}
