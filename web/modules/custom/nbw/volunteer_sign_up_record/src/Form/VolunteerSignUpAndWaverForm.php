<?php

namespace Drupal\volunteer_sign_up_record\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the volunteer sign up and waver entity edit forms.
 */
class VolunteerSignUpAndWaverForm extends ContentEntityForm {

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
        $this->messenger()->addStatus($this->t('New volunteer sign up and waver %label has been created.', $message_arguments));
        $this->logger('volunteer_sign_up_record')->notice('Created new volunteer sign up and waver %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The volunteer sign up and waver %label has been updated.', $message_arguments));
        $this->logger('volunteer_sign_up_record')->notice('Updated volunteer sign up and waver %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.volunteer_sign_up_and_waver.canonical', ['volunteer_sign_up_and_waver' => $entity->id()]);

    return $result;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\adult_patron_waver\Entity\AdultPatronWaver */
    $form = parent::buildForm($form, $form_state);

    // we're looking for Volunteer Sign Up Waver Text, nodeID = 34
    $docID = 34;
    $docWaiver =  \Drupal::entityTypeManager()->getStorage('node')->load($docID);
    $docTitle = $docWaiver->getTitle();
    If ($docTitle = "Volunteer Sign Up Waver") {
      // Get the body field value.
      $bodyValue = $docWaiver->get('body')->getValue();
      // Extract the text value from the body field.
      $bodyText = !empty($bodyValue[0]['value']) ? $bodyValue[0]['value'] : '';
    }
    $waiver_text = "";
    If (!empty($bodyText)){
      $waiver_text = $bodyText;
    } else {
      $waiver_text = "Hardcoded Volunteer Sign Up Waver Waiver Text";
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
