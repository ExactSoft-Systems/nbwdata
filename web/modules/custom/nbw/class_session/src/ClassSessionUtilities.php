<?php

namespace Drupal\class_session;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class ClassSessionUtilities
{
  /**
   * A parsed associative array of the active market states.
   *
   * Holds only title and region code data per state.
   *
   * @return array
   *   Returns active States parsed array
   */
  private static function parseNodes(array $nodes, $class_id): array
  {
    $data = [];

    foreach ($nodes as $key => $node) {

      //field_class_name will have an ID of the "Class" Event node, field_students will have IDs of Youth that are registered for the class

      $title = trim(str_ireplace('Test ', '', $node->getTitle()));

      // Get the Administrative Area code.
      $classEntityRef = $node->get('field_class_name')->first();
      $classEventID = $classEntityRef->get('entity')->getTargetIdentifier();
      if ($classEventID != $class_id){
        continue;
      }
      //#entityTypeId: "node_type" #type: "event"
      $classEvent =  \Drupal::entityTypeManager()->getStorage('node')->load($classEventID);
      $classTitle = $classEvent->getTitle();
      $studentRefs = $node->get('field_students')->getValue();
      $students = [];
      foreach ($studentRefs as $student_reference) {
        //$id = $student_reference["target_id"];
        $account = \Drupal::entityTypeManager()->getStorage('user')->load($student_reference["target_id"]); // pass youth uid
        $address =  $account->get('field_address')->getValue();
        $email = $account->getEmail();
        $firstName = $address[0]['given_name'];
        $lastName = $address[0]['family_name'];
        $students[$student_reference["target_id"]] = $firstName . " " . $lastName . " " . $email;;
      }

      $data = [
        'class_name' => $classTitle,
        'students' => $students,
      ];

    }
    return $data;
  }

  public static function getClassRoster($class_id): array
  {

    $items = [];

    //$storage_session = \Drupal::entityTypeManager()->getStorage('class_session');

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'class_roster')
      ->condition('status', 1)
      ->sort('title', 'ASC');

    $nids = $query->execute();

    if (is_array($nids) && !empty($nids)) {
      $class_rosters =  \Drupal::entityTypeManager()
        ->getStorage('node')->loadMultiple($nids);
      $items = self::parseNodes($class_rosters,$class_id);
    }

    //field_class_name will have an ID of the "Class" Event node, field_students will have IDs of Youth that are registered for the class

    return $items;
  }
  public static function getCheckedInStudents($class_id): array
  {
    $arrCheckedInStudents = [];

    $start = date('d-m-YT08:00:00',time());

    $start_date = new DrupalDateTime($start);
    $start_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $start_date = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $query = \Drupal::entityQuery('class_session')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->condition('field_check_in_time', $start_date, '>')
      ->notExists('field_check_out_time');

    $classSessionIds = $query->execute();

    foreach ($classSessionIds as $sessionID) {
      $classSession =  \Drupal::entityTypeManager()->getStorage('class_session')->load($sessionID);
      $classEntityRef = $classSession->get('field_class_name')->first();
      $classID = $classEntityRef->get('entity')->getTargetIdentifier();
      if ($classID != $class_id){
        continue;
      }
      $youthRefID =  $classSession->get('field_youth')->first();
      $youthID = $youthRefID->get('entity')->getTargetIdentifier();
      $account = \Drupal::entityTypeManager()->getStorage('user')->load($youthID);
      $address =  $account->get('field_address')->getValue();
      //$email = $account->get('mail')->getValue()[0]['value'];
      $email = $account->getEmail();
      $firstName = $address[0]['given_name'];
      $lastName = $address[0]['family_name'];
      //$arrCheckedInStudents[$youthID] = $firstName . " " . $lastName . " " . $email;
      if(!in_array($youthID,$arrCheckedInStudents)){
        $arrCheckedInStudents[] = $youthID;
      }
    }

    return $arrCheckedInStudents;
  }

  public static function getTodaysClasses(): array
  {
    $classes = [];
    //date range - today, today + 12 hours
    $todayMorning = strtotime('today 8am');

    $todayEndOfDay = strtotime('today 9PM');

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'event')
      ->condition('status', 1)
      ->sort('created', 'DESC')
      ->condition('field_when', $todayMorning, '>')
      ->condition('field_when.end_value', $todayEndOfDay, '<');

    $classEntityIDs = $query->execute();
    foreach ($classEntityIDs as $classID) {
      $classEvent =  \Drupal::entityTypeManager()->getStorage('node')->load($classID);
      $classTitle = $classEvent->getTitle();
      $classes[$classID] = $classTitle;
    }


    return $classes;
  }

  public static function checkInStudents($clas_id, array $student_ids)
  {
    /*    $entity_type = \Drupal::entityTypeManager()->getDefinition('class_session');
        $bundle_key = $entity_type->getKey('bundle');

        // Create new content.
        $content = \Drupal::entityTypeManager()->getStorage('class_session')->create([
          $entity_type->getKey('bundle') => $this->getBundleValue(),
        ]);*/
    //$today = date('d-m-YT00:00:00',time());
    $today = date('d-m-Y',time());
    $today_date = new DrupalDateTime($today);
    $today_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $today_date = $today_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $now = date('d-m-YTH:i:s',time());
    $now_time = new DrupalDateTime($now);
    $now_time->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $now_time = $now_time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    foreach ($student_ids as $student) {
      $storage = \Drupal::entityTypeManager()->getStorage('class_session');
      // Create new content.
      $content = $storage->create([]);
      $content->set('field_class_name', $clas_id);
      $content->set('field_youth', $student);
      $content->set('field_check_in_time', $now_time);
      $content->set('field_class_date', $today_date);
      // Save content.
      try {
        $content->save();
      }
      catch (\Exception $e) {
        \Drupal::logger('CheckInMultipleForm::checkInStudents')->error(self::t('A problem occurred when creating a new content.'));
        \Drupal::logger('CheckInMultipleForm::checkInStudents')->error($e->getMessage());
      }

    }

  }
  public static function checkOutStudents($clas_id, array $student_ids)
  {
    $start = date('d-m-YT08:00:00',time());

    $start_date = new DrupalDateTime($start);
    $start_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $start_date = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    foreach ($student_ids as $student) {
      $query = \Drupal::entityQuery('class_session')
        ->condition('status', 1)
        ->sort('created', 'DESC')
        ->condition('field_youth', $student, '=')
        ->condition('field_class_name', $clas_id, '=')
        ->condition('field_check_in_time', $start_date, '>')
        ->notExists('field_check_out_time');

      $sessionIDs = $query->execute();
      foreach ($sessionIDs as $sessionID) {
        $class_session =  \Drupal::entityTypeManager()->getStorage('class_session')->load($sessionID);

        $now = date('d-m-YTH:i:s',time());
        $now_time = new DrupalDateTime($now);
        $now_time->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
        $now_time = $now_time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

        $class_session->set('field_check_out_time', $now_time);
        $class_session->save();

      }
    }


  }
}
