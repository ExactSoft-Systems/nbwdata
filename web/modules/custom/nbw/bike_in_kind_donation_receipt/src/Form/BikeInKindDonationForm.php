<?php

namespace Drupal\bike_in_kind_donation_receipt\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the bicycle/in-kind donation receipt entity edit forms.
 */
class BikeInKindDonationForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New bicycle/in-kind donation receipt %label has been created.', $message_arguments));
        $this->logger('bike_in_kind_donation_receipt')->notice('Created new bicycle/in-kind donation receipt %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The bicycle/in-kind donation receipt %label has been updated.', $message_arguments));
        $this->logger('bike_in_kind_donation_receipt')->notice('Updated bicycle/in-kind donation receipt %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.bike_in_kind_donation.canonical', ['bike_in_kind_donation' => $entity->id()]);

    return $result;
  }

}
