<?php

namespace Drupal\attendance_record\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the attendance record entity edit forms.
 */
class AttendanceRecordForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    //$result = parent::save($form, $form_state);

    $miles = $form_state->getValue('field_miles_ridden')[0]['value'];
    $hours = $form_state->getValue('field_hours_added')[0]['value'];
    //dpm($miles);
    $attendees = $form_state->getValue('field_youth_attended');
    //dpm($attendees);

    foreach($attendees as $youth) {

      // Get NBW profiles by the YouthID
      $nbw_record_profiles = \Drupal::entityTypeManager()
        ->getStorage('profile')
        ->loadByProperties([
          'uid' => $youth,
          'type' => 'nbw_record',
        ]);

      foreach($nbw_record_profiles as $profile) {
        //dpm($profile->get('field_miles_total')->value);
        $miles_total_new = floatval($profile->get('field_miles_total')->value) + $miles;
        $hours_total_new = floatval($profile->get('field_hours_total')->value) + $hours;
        $profile->get('field_miles_total')->setValue($miles_total_new);
        $profile->get('field_hours_total')->setValue($hours_total_new);
        $profile->save();
      }

    }


    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New attendance record %label has been created.', $message_arguments));
      $this->logger('attendance_record')->notice('Created new attendance record %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The attendance record %label has been updated.', $message_arguments));
      $this->logger('attendance_record')->notice('Updated new attendance record %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.attendance_record.canonical', ['attendance_record' => $entity->id()]);
  }

}
