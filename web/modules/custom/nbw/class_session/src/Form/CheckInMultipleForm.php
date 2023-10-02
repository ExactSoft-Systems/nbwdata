<?php

namespace Drupal\class_session\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Views;
use Drupal\class_session\ClassSessionUtilities;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;

class CheckInMultipleForm extends FormBase {
  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The request object.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *    The request stack object.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *    The entity storage.
   */
  public function __construct(AccountInterface $account,RequestStack $request_stack, EntityStorageInterface $entity_storage) {
    $this->account = $account;
    $this->requestStack = $request_stack;
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('current_user'),
      $container->get('request_stack'),
      $container->get('entity_type.manager')->getStorage('class_session')
    );
  }
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

/*    $referring_entity_type = \Drupal::request()->query->get('referring_entity_type');
    $referring_entity_id = \Drupal::request()->query->get('referring_entity_id');

    $request = $this->requestStack->getCurrentRequest();
    $class_session = $this->requestStack->getCurrentRequest()->get('class_session');*/

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
        '#title' => $this->t('Classes to check-in for'),
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
      $arrCheckdInStudents = ClassSessionUtilities::getCheckedInStudents($selected_class);
      $arrCheckedInYouth = ClassSessionUtilities::getCheckedInYouthByAttRec($selected_class);


      if (!empty($arrClassesStudents)) {

        $header = array(
          'name' => t('Name'),
          'time_in' => t('Time In'),
          'time_out' => t('Time Out'),
          'phone_num' => t('Phone #'),
          'notes' => t('Notes'),
          'hours_earned' => t('Hours Earned'),
          'hours_lost' => t('Hours Lost'),
        );

/*        $options = array(
          array(
            'title' => 'How to Learn Drupal',
            'content_type' => 'Article',
            'status' => 'published',
            '#attributes' => array(
              'class' => array(
                'article-row',
              ),
            ),
          ),
          array(
            'title' => 'Privacy Policy',
            'content_type' => 'Page',
            'status' => 'published',
            '#attributes' => array(
              'class' => array(
                'page-row',
              ),
            ),
          ),
        );*/




/*        $form['students_fieldset']['students'] = array(
          '#type' => 'tableselect',
          '#header' => $header,
          '#empty' => t('No content available.'),
        );*/


        $students = array();
        $options = array();

        $commentfield = array(
          '#type' => 'textfield',
          '#default_value' => '',
          '#title' => 'Comment',
          '#title_display' => 'invisible',
          '#name' => 'commentfield'
        );

        foreach (  $arrClassesStudents['students'] as $studentID => $studentName) {
          $students[$studentID] = $studentName;



          $options[$studentID] = array(
            'name' => $studentName['first name'] . ' ' . $studentName['last name'],
            'time_in' => t('Time In'),
            'time_out' => t('Time Out'),
            'phone_num' => t('Phone #'),
            /*'notes' => array('data'=>array(
              '#type' => 'textarea',
              '#title' => 'notes for studentID_'.$studentID,
              '#title_display'=> 'invisible',
              '#default_value'=> '',
              '#name' => 'notes['.$studentID.']',
            )),
            'hours_earned' => array('data'=> array(
              '#type' => 'number',
              '#title' => 'hours earned for student_'.$studentID,
              '#title_display'=> 'invisible',
              '#default_value'=> '',
              '#name' => 'hours_earned['.$studentID.']',
            )),
            'hours_lost' => array('data'=> array(
              '#type' => 'number',
              '#title' => 'hours lost for student_'.$studentID,
              '#title_display'=> 'invisible',
              '#default_value'=> '',
              '#name' => 'hours_lost['.$studentID.']',
            )),*/
           );

          }


/*        $form['students_fieldset']['students'] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('Students in this class:'),
          '#options' => $students,
        ];*/

/*        $form['students_fieldset']['students'] = array(
          '#type' => 'tableselect',
          '#header' => $header,
          '#options' => $options,
          '#empty' => t('No content available.'),
        ); */

                $form['students'] = array(
                  '#type' => 'tableselect',
                  '#header' => $header,
                  '#options' => $options,
                  '#empty' => t('No content available.'),
                );

/*        $form['students']['notes'] = array(
          '#type' => 'value',
        );
        $form['students']['hours_earned'] = array(
          '#type' => 'value',
        );
        $form['students']['hours_lost'] = array(
          '#type' => 'value',
        );*/

       if (!empty($arrCheckdInStudents)) {
                  foreach($arrCheckdInStudents as $studentID => $studentData ) {
                    $form['students'] [$studentID] = [
                      '#value' => $studentID,
                      '#disabled' => TRUE,
                    ];
                  }
                }
/*        $form['students_fieldset']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Check In'),
        ];*/
        $form['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Check In'),
        ];

        $form['check_out_submit'] = [
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

    if ($form_state->getValue('submit') == 'Check In') {
      $form_state->setRebuild(FALSE);
      $answer = $form_state->getValue('students');
      $input = $form_state->getUserInput();
      $notes = $input['notes'];
      $hours_earned = $input['hours_earned'];
      $hours_lost = $input['hours_lost'];
      $checkin_array = [];
      $selected_class = $form_state->getValue('class_select');
      $strCheckdInStudents = ClassSessionUtilities::getCheckedInStudents($selected_class);
      foreach ($answer as $key => $value) {
        if($value && !in_array($key,$strCheckdInStudents)){
          $checkin_array[] = $key;
        }
      }
      if(!empty($checkin_array)){
        ClassSessionUtilities::checkInStudents($selected_class, $checkin_array);
      }

      return;
    }
  }



}
