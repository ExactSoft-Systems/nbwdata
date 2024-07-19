<?php

declare(strict_types=1);

namespace Drupal\nbw_tools\Form;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a NBW tools form (Check In/Check Out).
 */
final class CheckInOut extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('entity_type.manager')
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
    // Classes.
    $query = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery();
    $query->condition('type', 'class_registration');
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
    $form['students_container']['students'] = [
      '#markup' => $this->t('Please select a class.'),
    ];
    $selected_class = $form_state->getValue('class');
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
        '#type' => 'checkboxes',
        '#required' => FALSE,
        '#title' => $this->t('Students'),
        '#default_value' => [],
        '#options' => [],
      ];
      $class_registration = Node::load($selected_class);
      foreach ($class_registration->field_students->referencedEntities() as $student) {
        $name = [
          $student->field_address->given_name,
          $student->field_address->family_name,
          $student->field_address->additional_name,
        ];
        $name = implode(' ', $name);
        $form['students_container']['students']['#options'][$student->id()] = $name;
      }
    }
    // Date
    $form['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date'),
      '#required' => TRUE,
      '#default_value' => date('Y-m-d'),
    ];
    $form['check_in_out'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#title' => $this->t('Check In / Out'),
      '#title_display' => 'invisible',
      '#options' => [
        'check_in' => $this->t('Check In'),
        'check_out' => $this->t('Check Out'),
      ],
    ];
    $form['time'] = [
      '#type' => 'datetime',
      '#date_date_element' => 'none',
      '#date_time_element' => 'time',
      '#default_value' => new DrupalDateTime('now'),
    ];
    // Hours & Miles.
    $form['hours_wrapper'] = [
      '#type' => 'container',
    ];
    $form['hours_wrapper']['hours'] = [
      '#type' => 'number',
      '#default_value' => 0,
      '#title' => $this->t('Hours'),
      '#description' => $this->t('Hours Lost / Earned'),
    ];
    $form['hours_wrapper']['earned_lost'] = [
      '#type' => 'radios',
      '#required' => TRUE,
      '#options' => [
        'earned' => $this->t('Earned'),
        'lost' => $this->t('Lost'),
      ],
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
    $triggering_element = $form_state->getTriggeringElement();
    if (isset($triggering_element['#internal_id']) && $triggering_element['#internal_id'] === 'students_select_all') {
      // TODO: Workaround, see: https://www.drupal.org/project/drupal/issues/1100170#comment-15586080
      foreach ($form['students_container']['students']['#options'] as $key => $student) {
        $form['students_container']['students'][$key]['#attributes']['checked'] = 'checked';
      }
    }
    return $form['students_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $aaaaaaaaaaaaaaaaaaaaaaaaa = 'a'; // TODO: DEBUG Breakpoint!
  }

}
