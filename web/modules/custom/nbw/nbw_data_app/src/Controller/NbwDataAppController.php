<?php

namespace Drupal\nbw_data_app\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for NBW Data App routes.
 */
class NbwDataAppController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
