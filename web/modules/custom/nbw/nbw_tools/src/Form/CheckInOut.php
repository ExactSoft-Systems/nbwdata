<?php

declare(strict_types=1);

namespace Drupal\nbw_tools\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides a NBW tools form (Check In/Check Out).
 */
final class CheckInOut extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RequestStack $requestStack) {
    $this->entityTypeManager = $entityTypeManager;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'nbw_tools_check_in_out';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $triggering_element = $form_state->getTriggeringElement();
    // Classes.
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery();
    $query->condition('type', 'class_roster');
    $query->condition('status', 1);
    $query->sort('field_class_name.entity:node.title');
    $node_classes = Node::loadMultiple($query->accessCheck(TRUE)
      ->execute());
    $class_list = [];
    foreach ($node_classes as $nid => $node) {
      $class_list[$nid] = $node->field_class_name->entity->label();
    }
    $form['class'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Class Name'),
      '#empty_option' => $this->t('- Select -'),
      '#empty_value' => '',
      '#options' => $class_list,
      '#default_value' => $this->requestStack->getCurrentRequest()->query->get('class'),
      '#ajax' => [
        'callback' => '::ajaxStudents',
        'wrapper' => 'students-container',
      ],
    ];
    // Students.
    $form['students_container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'students-container'],
    ];
    if ($form_state->hasValue('class')) {
      $selected_class = $form_state->getValue('class');
    }
    elseif ($this->requestStack->getCurrentRequest()->query->has('class')) {
      $selected_class = $this->requestStack
        ->getCurrentRequest()
        ->query
        ->get('class');
    }
    else {
      $selected_class = NULL;
    }
    if ($selected_class !== NULL) {
      $form['students_container']['students_select_all'] = [
        '#type' => 'button',
        '#value' => $this->t('Select All'),
        '#internal_id' => 'students_select_all',
        '#executes_submit_callback' => FALSE,
        '#limit_validation_errors' => [['class']],
        '#ajax' => [
          'callback' => '::ajaxStudents',
          'wrapper' => 'students-container',
        ],
      ];
      $form['students_container']['students'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Students'),
        '#tree' => TRUE,
      ];
      $class_registration = Node::load($selected_class);
      if (!$class_registration || $class_registration->getType() !== 'class_roster') {
        throw new NotFoundHttpException();
      }
      foreach ($class_registration->field_students->referencedEntities() as $student) {
        $name = [
          $student->field_address->given_name,
          $student->field_address->family_name,
          $student->field_address->additional_name,
        ];
        $name = implode(' ', $name);
        $form['students_container']['students'][$student->id()] = [
          '#type' => 'checkbox',
          '#title' => $name,
          '#disabled' => FALSE,
        ];
        if (isset($triggering_element['#internal_id'])
          && $triggering_element['#internal_id'] === 'students_select_all'
          && !$form['students_container']['students'][$student->id()]['#disabled'])
        {
          // TODO: Workaround, see: https://www.drupal.org/project/drupal/issues/1100170#comment-15586080
          $form['students_container']['students'][$student->id()]['#attributes']['checked'] = 'checked';
        }
      }
    }
    else {
      // Class not selected / prepopulated.
      $form['students_container']['students'] = [
        '#markup' => $this->t('Please select a class.'),
        '#prefix' => '<strong>',
        '#suffix' => '</strong>',
      ];
    }
    // Date
    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#required' => TRUE,
      '#default_value' => date('Y-m-d'),
    ];
    $form['check_in_out_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Check in / Out'),
    ];
    $form['check_in_out_wrapper']['checkin_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $form['check_in_out_wrapper']['checkin_wrapper']['checkin'] = [
      '#type' => 'radio',
      '#required' => TRUE,
      '#title' => $this->t('Check In'),
      '#parents' => ['check_in_out_wrapper'],
      '#return_value' => 'checkin',
    ];
    $form['check_in_out_wrapper']['checkin_wrapper']['checkin_time'] = [
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#default_value' => new DrupalDateTime('now'),
    ];
    $form['check_in_out_wrapper']['checkout_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $form['check_in_out_wrapper']['checkout_wrapper']['checkout'] = [
      '#type' => 'radio',
      '#required' => TRUE,
      '#title' => $this->t('Check Out'),
      '#parents' => ['check_in_out_wrapper'],
      '#return_value' => 'checkout',
    ];
    $form['check_in_out_wrapper']['checkout_wrapper']['checkout_time'] = [
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#default_value' => new DrupalDateTime('now'),
    ];
    // Hours & Miles.
    $form['hours_earned_lost_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Hours'),
    ];
    $form['hours_earned_lost_wrapper']['hours_earned_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $form['hours_earned_lost_wrapper']['hours_earned_wrapper']['earned'] = [
      '#type' => 'radio',
      '#required' => FALSE,
      '#title' => $this->t('Earned'),
      '#parents' => ['hours_earned_lost_wrapper'],
      '#return_value' => 'hours_earned',
    ];
    $form['hours_earned_lost_wrapper']['hours_earned_wrapper']['hours_earned'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('Hours earned'),
      '#title_display' => 'invisible'
    ];
    $form['hours_earned_lost_wrapper']['hours_lost_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];
    $form['hours_earned_lost_wrapper']['hours_lost_wrapper']['lost'] = [
      '#type' => 'radio',
      '#required' => FALSE,
      '#title' => $this->t('Lost'),
      '#parents' => ['hours_earned_lost_wrapper'],
      '#return_value' => 'hours_lost',
    ];
    $form['hours_earned_lost_wrapper']['hours_lost_wrapper']['hours_lost'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('Hours lost'),
      '#title_display' => 'invisible'
    ];
    $form['miles_wrapper'] = [
      '#type' => 'container',
    ];
    $form['miles_wrapper']['miles'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('Miles'),
      '#description' => $this->t('Miles Reidden'),
    ];
    // Notes & Submit.
    $form['notes'] = [
      '#type' => 'textarea',
      '#required' => FALSE,
      '#title' => $this->t('Notes'),
      '#cols' => 60,
      '#rows' => 5,
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    return $form;
  }

  /**
   * Return with students container.
   */
  public function ajaxStudents($form, FormStateInterface $form_state) {
    return $form['students_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element['#type'] === 'submit') {
      // Check students list.
      $empty_students = TRUE;
      foreach ($form_state->getValue('students') as $item) {
        if ($item) {
          $empty_students = FALSE;
          break;
        }
      }
      if ($empty_students) {
        $form_state->setErrorByName('students', 'Students should not be empty.');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Prepopulate class
    $this->requestStack
      ->getCurrentRequest()
      ->query
      ->add([
      'class' => $form_state->getValue('class')
    ]);
    // Create nodes
    $class_roster = Node::load($form_state->getValue('class'));
    foreach ($form_state->getValue('students') as $student_uid => $value) {
      if ($value != 0) {
        Node::create([
          'type' => 'attendance_record',
          'field_class_name' => $class_roster->field_class_name->target_id,
          'field_checkin_time' => $form_state->getValue('date'),
          'field_hours_earned' => $form_state->getValue('hours_earned'),
          'field_hours_lost' => $form_state->getValue('hours_lost'),
          'field_miles_ridden' => $form_state->getValue('miles'),
          'body' => $form_state->getValue('notes'),
          'field_student' => $student_uid,
          'field_time_in' => $form_state->getValue('checkin_time')->format('U'),
          'field_time_out' => $form_state->getValue('checkout_time')->format('U'),
        ])->save();
      }
    }
  }
}
