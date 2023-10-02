<?php

namespace Drupal\volunteer_time_record\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the volunteer time record entity edit forms.
 */
class VolunteerTimeRecordForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New volunteer time record %label has been created.', $message_arguments));
        $this->logger('volunteer_time_record')->notice('Created new volunteer time record %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The volunteer time record %label has been updated.', $message_arguments));
        $this->logger('volunteer_time_record')->notice('Updated volunteer time record %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.volunteer_time_record.canonical', ['volunteer_time_record' => $entity->id()]);

    return $result;
  }

}
