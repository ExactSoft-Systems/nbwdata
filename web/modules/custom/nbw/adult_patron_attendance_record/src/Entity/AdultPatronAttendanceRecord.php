<?php

namespace Drupal\adult_patron_attendance_record\Entity;

use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\adult_patron_attendance_record\AdultPatronAttendanceRecordInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the adult patron attendance record entity class.
 *
 * @ContentEntityType(
 *   id = "adult_patron_attendance_record",
 *   label = @Translation("Adult Patron Attendance Record"),
 *   label_collection = @Translation("Adult Patron Attendance Records"),
 *   label_singular = @Translation("adult patron attendance record"),
 *   label_plural = @Translation("adult patron attendance records"),
 *   label_count = @PluralTranslation(
 *     singular = "@count adult patron attendance records",
 *     plural = "@count adult patron attendance records",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\adult_patron_attendance_record\AdultPatronAttendanceRecordListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\adult_patron_attendance_record\Form\AdultPatronAttendanceRecordForm",
 *       "edit" = "Drupal\adult_patron_attendance_record\Form\AdultPatronAttendanceRecordForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "adult_patron_attendance_record",
 *   revision_table = "adult_patron_attendance_record_revision",
 *   show_revision_ui = TRUE,
 *   admin_permission = "administer adult patron attendance record",
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
 *     "collection" = "/admin/content/adult-patron-attendance-record",
 *     "add-form" = "/adult-patron-attendance-record/add",
 *     "canonical" = "/adult-patron-attendance-record/{adult_patron_attendance_record}",
 *     "edit-form" = "/adult-patron-attendance-record/{adult_patron_attendance_record}/edit",
 *     "delete-form" = "/adult-patron-attendance-record/{adult_patron_attendance_record}/delete",
 *   },
 *   field_ui_base_route = "entity.adult_patron_attendance_record.settings",
 * )
 */
class AdultPatronAttendanceRecord extends RevisionableContentEntityBase implements AdultPatronAttendanceRecordInterface {

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
      ->setDescription(t('The time that the adult patron attendance record was created.'))
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
      ->setDescription(t('The time that the adult patron attendance record was last edited.'));

    return $fields;
  }

}
