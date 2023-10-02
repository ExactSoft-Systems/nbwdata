<?php

namespace Drupal\adult_patron_waver\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the bike church patron waiver entity edit forms.
 */
class AdultPatronWaverForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New bike church patron waiver %label has been created.', $message_arguments));
        $this->logger('adult_patron_waver')->notice('Created new bike church patron waiver %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The bike church patron waiver %label has been updated.', $message_arguments));
        $this->logger('adult_patron_waver')->notice('Updated bike church patron waiver %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.adult_patron_waver.canonical', ['adult_patron_waver' => $entity->id()]);

    return $result;
  }
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\adult_patron_waver\Entity\AdultPatronWaver */
    $form = parent::buildForm($form, $form_state);

    // we're looking for Bike Church Patron Waiver Text, nodeID = 46
      $docID = 46;
      $docWaiver =  \Drupal::entityTypeManager()->getStorage('node')->load($docID);
      $docTitle = $docWaiver->getTitle();
      If ($docTitle = "Bike Church Patron Waiver Text") {
        // Get the body field value.
        $bodyValue = $docWaiver->get('body')->getValue();
        // Extract the text value from the body field.
        $bodyText = !empty($bodyValue[0]['value']) ? $bodyValue[0]['value'] : '';
      }
      $waiver_text = "";
      If (!empty($bodyText)){
        $waiver_text = $bodyText;
      } else {
        $waiver_text = "Hardcoded Waiver Text";
      }


    $form['field_waver_element'] = array(
      '#markup' => '<br><div class="custom-form-header">'.$waiver_text. '</div>',
      '#weight' => -1,  // Adjust the weight to control the element's position.
    );
    //$form['field_signature']['#weight']=4;

    //kint($form);
    return $form;
  }
}
