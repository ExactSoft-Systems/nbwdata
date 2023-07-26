<?php

namespace Drupal\attendance_record;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the attendance record entity type.
 */
class AttendanceRecordAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view attendance record');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit attendance record', 'administer attendance record'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete attendance record', 'administer attendance record'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create attendance record', 'administer attendance record'], 'OR');
  }

}
