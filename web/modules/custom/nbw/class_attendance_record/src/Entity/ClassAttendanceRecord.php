<?php

namespace Drupal\class_attendance_record\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\class_attendance_record\ClassAttendanceRecordInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the class attendance record entity class.
 *
 * @ContentEntityType(
 *   id = "class_attendance_record",
 *   label = @Translation("Class Attendance Record"),
 *   label_collection = @Translation("Class Attendance Records"),
 *   label_singular = @Translation("class attendance record"),
 *   label_plural = @Translation("class attendance records"),
 *   label_count = @PluralTranslation(
 *     singular = "@count class attendance records",
 *     plural = "@count class attendance records",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\class_attendance_record\ClassAttendanceRecordListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\class_attendance_record\Form\ClassAttendanceRecordForm",
 *       "edit" = "Drupal\class_attendance_record\Form\ClassAttendanceRecordForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "class_attendance_record",
 *   admin_permission = "administer class attendance record",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/class-attendance-record",
 *     "add-form" = "/class-attendance-record/add",
 *     "canonical" = "/class-attendance-record/{class_attendance_record}",
 *     "edit-form" = "/class-attendance-record/{class_attendance_record}/edit",
 *     "delete-form" = "/class-attendance-record/{class_attendance_record}/delete",
 *   },
 *   field_ui_base_route = "entity.class_attendance_record.settings",
 * )
 */
class ClassAttendanceRecord extends ContentEntityBase implements ClassAttendanceRecordInterface {

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
      ->setDescription(t('The time that the class attendance record was created.'))
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
      ->setDescription(t('The time that the class attendance record was last edited.'));

    return $fields;
  }

}
