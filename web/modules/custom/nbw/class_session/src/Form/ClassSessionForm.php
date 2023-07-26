<?php

namespace Drupal\class_session\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the class session entity edit forms.
 */
class ClassSessionForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New class session %label has been created.', $message_arguments));
        $this->logger('class_session')->notice('Created new class session %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The class session %label has been updated.', $message_arguments));
        $this->logger('class_session')->notice('Updated class session %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.class_session.canonical', ['class_session' => $entity->id()]);

    return $result;
  }

}
