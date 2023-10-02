<?php

namespace Drupal\class_session\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\class_session\ClassSessionInterface;
use Drupal\Core\Render\Markup;
use Drupal\user\EntityOwnerTrait;
use Drupal\class_session\ClassSessionUtilities;

/**
 * Defines the class session entity class.
 *
 * @ContentEntityType(
 *   id = "class_session",
 *   label = @Translation("Class Session"),
 *   label_collection = @Translation("Class Sessions"),
 *   label_singular = @Translation("class session"),
 *   label_plural = @Translation("class sessions"),
 *   label_count = @PluralTranslation(
 *     singular = "@count class sessions",
 *     plural = "@count class sessions",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\class_session\ClassSessionListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\class_session\Form\ClassSessionForm",
 *       "edit" = "Drupal\class_session\Form\ClassSessionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "class_session",
 *   admin_permission = "administer class session",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/content/class-session",
 *     "add-form" = "/class-session/add",
 *     "canonical" = "/class-session/{class_session}",
 *     "edit-form" = "/class-session/{class_session}/edit",
 *     "delete-form" = "/class-session/{class_session}/delete",
 *   },
 *   field_ui_base_route = "entity.class_session.settings",
 * )
 */
class ClassSession extends ContentEntityBase implements ClassSessionInterface {

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
      ->setLabel(t('Class Session Notes'))
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
      ->setDescription(t('The time that the class session was created.'))
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
      ->setDescription(t('The time that the class session was last edited.'));

    return $fields;
  }

  /**
   * Returns a rendered table of the students registered for the class
   * @return array drupal table render array
   */
  public function getClassSessionStudentsTable()
  {
  // \Drupal::messenger()->addMessage(' inside getClassSessionStudentsTable function.');
    $sessionID = $this->id();
    $classSession = \Drupal::entityTypeManager()->getStorage('class_session')->load($sessionID);
    $classEntityRef = $classSession->get('field_class_name')->first();
    $class_students = [];

    $classID = $classEntityRef->get('entity')->getTargetIdentifier();
    //dump($classID);
//    $query = \Drupal::entityQuery('node')
//      ->condition('type', 'class_roster')
//      ->condition('field_class_name', $classID)//field_class_name
//      ->condition('status', 1)
//      ->sort('title', 'ASC');
//    $nid = $query->execute();
//    \Drupal::messenger()->addMessage($nid);

    //dump($nid);
    //kint($nid);

    //$classID
      //$class_students = ClassSessionUtilities::parseNodes($class_roster,$classID);
    $class_students = ClassSessionUtilities::getClassRoster($classID);

    if(!empty($class_students)){
      $class_roster = \Drupal::entityTypeManager()
        ->getStorage('node')->loadMultiple($nid);
      /**
       * $class_roster here holds an array of one element, most likely
       */

      //dump($class_roster);
      kint($class_students);
      //->getStorage('node')->load($nid);
    }else{
      dump('No Roster for this class!');
      \Drupal::messenger()->addMessage(' No Roster for this class!');
    }
/*    $class_roster = \Drupal::entityTypeManager()
      ->getStorage('node')->loadMultiple($nids);
    dump($class_roster);
      //->getStorage('node')->load($nid);*/
    //$table = [];
    $rows = [];
    foreach ($class_students['students'] as $studentID => $studentData) {
      $row = [

        Markup::create($studentData['first name']),
        Markup::create($studentData['last name']),
        Markup::create($studentData['email'])
      ];
      $rows[$studentID] = $row;
    }

/*    $row = [

      Markup::create('Registered Youth First Name'),
      Markup::create('Last Name'),
      Markup::create('Email')
    ];
    $rows[] = $row;*/

    $table['table'] = [
      '#type' => 'table',
      '#caption' => t('Class Name: ' . $class_students['class_name'] . '. Youth Registered for this class: '),
      '#header' => array(t('First Name'), t('Last Name'), t('Email')),
      '#rows' => $rows,
      '#empty' => t('No students to show. Enroll some!')
    ];

    return [
      '#type' => '#markup',
      '#markup' => \Drupal::service('renderer')->render($table)
    ];

  }



}
