<?php

namespace Drupal\class_session\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\class_session\ClassSessionUtilities;

class CheckOutMultipleForm  extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'class_session_check_in_multiple';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
//**
//first - we need to get "classes of today", then - class rosters
// (that is - students that are registered for the classes), then - check who already signed up
// (have class sessions opened for today).
// Then - present a multi-checkin form with those already checked in marked. Consider storing some of it in the
// Session

    $arrTodaysClasses = ClassSessionUtilities::getTodaysClasses();

    /*
        foreach($loop as $row){
          $options[$row->id] = $row->name;
        }*/

    $options = array();
    $options[0] = 'Choose the class';

    if (is_array($arrTodaysClasses) && !empty($arrTodaysClasses)) {
      foreach ($arrTodaysClasses as $classID => $className) {
        $options[$classID] = $className;
      }
    }

    $form['class_select'] = [
      // This is our select dropdown.
      '#type' => 'select',
      '#title' => $this->t('Classes To Ccheck Out From'),
      '#options' =>  $options,
    ];

    $form['question_type_submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Choose'),
      //#attributes' => ['class' => ['ajax-example-inline']],
      // No need to validate when submitting this.
      '#limit_validation_errors' => [],
      '#validate' => [],
    ];

    // This fieldset just serves as a container for the part of the form
    // that gets rebuilt. It has a nice line around it so you can see it.
    /*    $form['questions_fieldset'] = [
          '#type' => 'details',
          '#title' => $this->t('Stuff will appear here'),
          '#open' => TRUE,
          // We set the ID of this fieldset to questions-fieldset-wrapper so the
          // AJAX command can replace it.
          '#attributes' => [
            'id' => 'questions-fieldset-wrapper',
            'class' => ['questions-wrapper'],
          ],
        ];*/
    $selected_class = $form_state->getValue('class_select');
    if (!empty($selected_class) && $selected_class !== '0') {
      $arrClassesStudents  = ClassSessionUtilities::getClassRoster($selected_class);
      $strCheckdInStudents = ClassSessionUtilities::getCheckedInStudents($selected_class);

      if (!empty($arrClassesStudents)) {

        $students = array();

        foreach (  $arrClassesStudents['students'] as $studentID => $studentName) {
          $students[$studentID] = $studentName;
        }
        $form['students_fieldset']['students'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Students in this class:'),
          '#options' => $students,
        ];

        $boolMessageNeeded = false;

        foreach (  $arrClassesStudents['students'] as $studentID => $studentName) {
          if (empty($strCheckdInStudents) || !in_array($studentID,$strCheckdInStudents)) {
              $form['students_fieldset']['students'] [$studentID] = [
                '#disabled' => TRUE,
              ];
              if(!$boolMessageNeeded)
                $boolMessageNeeded = true;
          }
        }
        if($boolMessageNeeded){
          $messenger = $this->messenger();
          $messenger->addMessage($this->t('If any or all students are disabled - those students either not Checked In, or already Checked Out!'));
        }



        $form['students_fieldset']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Check Out'),
        ];
      }
    }



    return $form;
  }
  /**
   * Implements a form submit handler.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Find out what was submitted.
    if ($form_state->getValue('question_type_submit') == 'Choose') {
      $form_state->setValue('class_select', $form_state->getUserInput()['class_select']);
      $form_state->setRebuild();
    }
    /** @var \Drupal\class_session\Entity\ClassSession $entity */

    if ($form_state->getValue('submit') == 'Check Out') {
      $form_state->setRebuild(FALSE);
      $answer = $form_state->getValue('students');
      $check_out_array = [];
      $selected_class = $form_state->getValue('class_select');
      //$strCheckdInStudents = ClassSessionUtilities::getCheckedInStudents($selected_class);
      foreach ($answer as $key => $value) {
        if ($value) {
          $check_out_array[] = $key;
        }
      }
      if (!empty($check_out_array)) {
        ClassSessionUtilities::checkOutStudents($selected_class, $check_out_array);
      }

      return;
    }
  }
}
