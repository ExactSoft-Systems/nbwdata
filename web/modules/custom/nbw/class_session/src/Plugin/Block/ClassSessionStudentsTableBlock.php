<?php

namespace  Drupal\class_session\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * @Block(
 *   id = "class_session_students_table_block",
 *   admin_label = @Translation("Class Session Students Table block"),
 *   category = @Translation("Shows the table of enrolled students with some extra info"),
 * )
 */

class ClassSessionStudentsTableBlock extends BlockBase implements ContainerFactoryPluginInterface
{
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
   * Constructs a new ClassSessionStudentsTableBlock instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack object.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, EntityStorageInterface $entity_storage)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->requestStack = $request_stack;
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    /*\Drupal::messenger()->addMessage(' inside Class Session Block, create.');*/
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack'),
      $container->get('entity_type.manager')->getStorage('class_session')
    );
  }

  /**
   * The enrolled students table
   */
  public function build() {
    // \Drupal::messenger()->addMessage(' inside Class Session Block, build.');
    $classSession = $this->requestStack->getCurrentRequest()->get('class_session');
    if(!$classSession) {
      return null;
    }
    return $classSession->getClassSessionStudentsTable();
  }

  /**
   * Cache per page
   */
/*  public function getCacheContexts() {
    return ['url.path'];
  }*/

  /**
   * Invalidate caches when there are new bids
   */
/*  public function getCacheTags() {
    $classSession = $this->requestStack->getCurrentRequest()->get('class_session');
    $classSessionId = 0;
    if(!empty($classSession)){
      $classSessionId = $classSession->id();
    }
    return Cache::mergeTags(parent::getCacheTags(), ['class_session:'.$classSessionId]);
  }*/
  public function getCacheMaxAge() {
    return 0;
  }
}
