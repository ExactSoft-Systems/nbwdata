<?php

namespace Drupal\youth_application_waiver\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the youth application waiver entity edit forms.
 */
class YouthApplicationWaiverAddStep1 extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    //$result = parent::save($form, $form_state);

    // Redirect to step 2.
    $entity = $this->getEntity();
    $entity->save();
    $id = $entity->id();
    /**
     * Also we need to check if the Guardian is new, if yes - create a new user for them
     */
    $values = $form_state->getValues();
    $guard_email = $form_state->getValue('field_guardian_email')[0]['value'];
    $guard_address = $form_state->getValue('field_guardian_address')[0]['address'];
    $form_state->setRedirect('youth_application_waiver.step2', ['youth_application_waiver' => $id]);

/*    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New youth application waiver %label has been created.', $message_arguments));
        $this->logger('youth_application_waiver')->notice('Created new youth application waiver %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The youth application waiver %label has been updated.', $message_arguments));
        $this->logger('youth_application_waiver')->notice('Updated youth application waiver %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.youth_application_waiver.canonical', ['youth_application_waiver' => $entity->id()]);*/

    //return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\youth_application_waiver\Entity\YouthApplicationWaiver */
    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit']['#value'] = t('Save and proceed');
    $form['revision_log']['#access'] = FALSE;
    return $form;
  }

  protected function actions(array $form, FormStateInterface $form_state)
  {
    $actions = parent::actions($form, $form_state);
    $actions['cancel'] = [
      '#type' => 'submit',
      '#value' => $this->t('Cancel'),
      '#submit' => ['::cancelSubmit'],
      '#weight' => 90,
      '#limit_validation_errors' => []
    ];
    if (array_key_exists('delete', $actions)) {
      unset($actions['delete']);
    }
    $actions['#prefix'] = '<i>Step 1 of 9</i>';
    return $actions;
  }
  public function cancelSubmit(array $form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.youth_application_waiver.collection');
  }

}
