<?php

namespace Drupal\adult_patron_attendance_record\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the adult patron attendance record entity edit forms.
 */
class AdultPatronAttendanceRecordForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New adult patron attendance record %label has been created.', $message_arguments));
        $this->logger('adult_patron_attendance_record')->notice('Created new adult patron attendance record %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The adult patron attendance record %label has been updated.', $message_arguments));
        $this->logger('adult_patron_attendance_record')->notice('Updated adult patron attendance record %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.adult_patron_attendance_record.canonical', ['adult_patron_attendance_record' => $entity->id()]);

    return $result;
  }

}
