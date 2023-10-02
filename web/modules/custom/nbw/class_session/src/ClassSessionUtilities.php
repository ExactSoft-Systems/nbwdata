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
        $students[$student_reference["target_id"]]['first name'] = $firstName;
        $students[$student_reference["target_id"]]['last name'] = $lastName;
        $students[$student_reference["target_id"]]['email'] = $email;
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

//    $query = \Drupal::entityQuery('node')
//      ->condition('type', 'class_roster')
//      ->condition('field_class_name', $classID)//field_class_name
//      ->condition('status', 1)
//      ->sort('title', 'ASC');
//    $nid = $query->execute();
//    \Drupal::messenger()->addMessage($nid);

    //$storage_session = \Drupal::entityTypeManager()->getStorage('class_session');

    $query = \Drupal::entityQuery('node')
      ->condition('type', 'class_roster')
      ->condition('field_class_name', $class_id)//field_class_name
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
      //->condition('status', 1)
      ->sort('created', 'DESC')
      ->condition('field_start_time', $start_date, '>')
      ->notExists('field_end_time');

    $classSessionIds = $query->execute();

    foreach ($classSessionIds as $sessionID) {
      $classSession = \Drupal::entityTypeManager()->getStorage('class_session')->load($sessionID);
      $classEntityRef = $classSession->get('field_class_name')->first();
      $classID = $classEntityRef->get('entity')->getTargetIdentifier();
      if ($classID != $class_id) {
        continue;
      }
      //we need: sessionID, YouthId, CheckIn Time, Phone Number (from Youth user account)
      //$youthRefID =  $classSession->get('field_youth_attending')->first();
      $youthRefIDs = $classSession->get('field_youth_attending');
      if (empty($youthRefIDs)) {
        return array();
      }
      foreach ($youthRefIDs as $youthRefID) {

        $checkInTime = $classSession->get('field_start_time')->first()->getValue();
        /*      $localtime = strtotime("-4 hours", strtotime($checkInTime['value']));
              $localTimeFormatted = date('Y-m-d H:i:s P', $localtime);*/
        //dpm($checkInTime);
        /*      $time1 = strtotime($checkInTime['value']);
              //$timestamp =$checkInTime->getTimestamp();
              $formatted = \Drupal::service('date.formatter')->format(
                $time1, 'custom', 'Y-m-d H:i:s P'
              );*/
        //$today = date('d-m-YTH:i:s',strtotime($checkInTime['value']));
        $today_date = new DrupalDateTime($checkInTime['value'], 'UTC');
        $timezone = \Drupal::config('system.date')->get('timezone')['default'];
        $today_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $checkin_date_time = $today_date->format('d/m/Y h:i:s A');
        $checkin_time = $today_date->format('h:i A');
        $youthID = $youthRefID->get('entity')->getTargetIdentifier();
        $account = \Drupal::entityTypeManager()->getStorage('user')->load($youthID);
        $address = $account->get('field_address')->getValue();
        //$email = $account->get('mail')->getValue()[0]['value'];
        $email = $account->getEmail();
        $phone = $account->get('field_primary_phone')->first()->getValue();
        $firstName = $address[0]['given_name'];
        $lastName = $address[0]['family_name'];
        //$arrCheckedInStudents[$youthID] = $firstName . " " . $lastName . " " . $email;
        if (!in_array($youthID, $arrCheckedInStudents)) {
          $arrCheckedInStudents[$youthID] = array(
            'session_id' => $sessionID,
            'check_in_time' => $checkInTime,
            'phone' => $phone,
          );
        }
      }
    }

    return $arrCheckedInStudents;
  }

  public static function getCheckedInYouthByAttRec($class_id): array
  {
    $arrCheckedInYouth = [];

    $start = date('d-m-YT08:00:00',time());

    $start_date = new DrupalDateTime($start);
    $start_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $start_date = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $query = \Drupal::entityQuery('class_attendance_record')
      //->condition('status', 1)
      ->sort('created', 'DESC')
      ->condition('field_class', $class_id)
      ->condition('field_check_in_time', $start_date, '>')
      ->notExists('field_check_out_time');

    $classAttendRecordIds = $query->execute();

    foreach ($classAttendRecordIds as $recordID) {
      $attendRecord = \Drupal::entityTypeManager()->getStorage('class_attendance_record')->load($recordID);
/*      $classEntityRef = $classSession->get('field_class_name')->first();
      $classID = $classEntityRef->get('entity')->getTargetIdentifier();
      if ($classID != $class_id) {
        continue;
      }*/
      //we need: sessionID, YouthId, CheckIn Time, Phone Number (from Youth user account)
      //$youthRefID =  $classSession->get('field_youth_attending')->first();
      $youthRefID = $attendRecord->get('field_youth')->first();
      if (empty($youthRefID)) {
        continue;
      }
      //foreach ($youthRefIDs as $youthRefID) {

        $checkInTime = $attendRecord->get('field_check_in_time')->first()->getValue();
        /*      $localtime = strtotime("-4 hours", strtotime($checkInTime['value']));
              $localTimeFormatted = date('Y-m-d H:i:s P', $localtime);*/
        //dpm($checkInTime);
        /*      $time1 = strtotime($checkInTime['value']);
              //$timestamp =$checkInTime->getTimestamp();
              $formatted = \Drupal::service('date.formatter')->format(
                $time1, 'custom', 'Y-m-d H:i:s P'
              );*/
        //$today = date('d-m-YTH:i:s',strtotime($checkInTime['value']));
        $today_date = new DrupalDateTime($checkInTime['value'], 'UTC');
        $timezone = \Drupal::config('system.date')->get('timezone')['default'];
        $today_date->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        $checkin_date_time = $today_date->format('d/m/Y h:i:s A');
        $checkin_time = $today_date->format('h:i A');
        $youthID = $youthRefID->get('entity')->getTargetIdentifier();
        $account = \Drupal::entityTypeManager()->getStorage('user')->load($youthID);
        $address = $account->get('field_address')->getValue();
        //$email = $account->get('mail')->getValue()[0]['value'];
        $email = $account->getEmail();
        $phone = $account->get('field_primary_phone')->first()->getValue();
        $firstName = $address[0]['given_name'];
        $lastName = $address[0]['family_name'];
        //$arrCheckedInStudents[$youthID] = $firstName . " " . $lastName . " " . $email;
        if (!in_array($youthID, $arrCheckedInYouth)) {
          $arrCheckedInYouth[$youthID] = array(
            'attend_record_id' => $recordID,
            'youth' => $firstName . " " . $lastName . " " . $email,
            'check_in_time' => $checkInTime,
            'phone' => $phone,
          );
        }
      //}
    }

    return $arrCheckedInYouth;
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


    $start = date('d-m-YT08:00:00',time());

    $start_date = new DrupalDateTime($start);
    $start_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $start_date = $start_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $query = \Drupal::entityQuery('class_session')
      //->condition('status', 1)
      ->sort('created', 'DESC')
      ->condition('field_start_time', $start_date, '>')
      ->condition('field_class_name', $clas_id, '=')
      ->notExists('field_end_time');

    $sessionIDs = $query->execute();
    foreach ($sessionIDs as $sessionID) {
      $class_session =  \Drupal::entityTypeManager()->getStorage('class_session')->load($sessionID);

      $youth_attending = $class_session->get('field_youth_attending');

      foreach ($student_ids as $student) {
        $youth_attending->appendItem($student);
      }

/*      $now = date('d-m-YTH:i:s',time());
      $now_time = new DrupalDateTime($now);
      $now_time->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
      $now_time = $now_time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

      $class_session->set('field_youth_attending', $now_time);*/
      //$class_session->set('field_youth_attending', $youth_attenting);
      try {
        $class_session->save();
      }
      catch (\Exception $e) {
        \Drupal::logger('CheckInMultipleForm::checkInStudents')->error(self::t('A problem occurred when addint new Youth to a session.'));
        \Drupal::logger('CheckInMultipleForm::checkInStudents')->error($e->getMessage());
      }


    }
    //return;

    $today = date('d-m-Y',time());
    $today_date = new DrupalDateTime($today);
    $today_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $today_date = $today_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    $now = date('d-m-YTH:i:s',time());
    $now_time = new DrupalDateTime($now);
    $now_time->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $now_time = $now_time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);

    foreach ($student_ids as $student) {
      $storage = \Drupal::entityTypeManager()->getStorage('class_attendance_record');
      // Create new content.
      $content = $storage->create([]);
      $content->set('field_class', $clas_id);
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

  public static function getEntity(){

    $route_match = \Drupal::routeMatch();
    // Entity will be found in the route parameters.
    if (($route = $route_match->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && strpos($options['type'], 'entity:') === 0) {
          $entity = $route_match->getParameter($name);
          if ($entity instanceof \Drupal\Core\Entity\ContentEntityInterface && $entity->hasLinkTemplate('canonical')) {
            return $entity;
          }
          // Since entity was found, no need to iterate further.
          return NULL;
        }
      }
    }
  }

}
