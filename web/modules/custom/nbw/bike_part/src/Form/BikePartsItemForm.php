<?php

namespace Drupal\bike_part\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the bike parts item entity edit forms.
 */
class BikePartsItemForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New bike parts item %label has been created.', $message_arguments));
        $this->logger('bike_part')->notice('Created new bike parts item %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The bike parts item %label has been updated.', $message_arguments));
        $this->logger('bike_part')->notice('Updated bike parts item %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.bike_parts_item.canonical', ['bike_parts_item' => $entity->id()]);

    return $result;
  }

}
