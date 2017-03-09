<?php

namespace Drupal\express_block_place\Controller;

use Drupal\Core\Url;

class ExpressBlockPlaceController {
  public function edit_layout() {
    // Link to the current page with a query parameter.
    $query = \Drupal::request()->query->all();
    $wrapper_class = '';
    $status_class = '';
    $description = '';
    if (isset($query['block-place'])) {
      $status_class = 'active';
      $wrapper_class = 'is-active';
      $description = t('Exit Place block mode.');
      unset($query['block-place']);
      unset($query['destination']);
    }
    else {
      $status_class = 'inactive';
      $description = t('Show regions to Place blocks.');
      $query['block-place'] = '1';
      // Setting destination is both a work-around for the toolbar "Back to site"
      // link in escapeAdmin.js and used for the destination after picking a
      // block.
      $query['destination'] = Url::fromRoute('<current>')->toString();
    }

    // Remove on Admin routes.
    $admin_route = \Drupal::service('router.admin_context')->isAdminRoute();
    // Remove on Block Demo page.
    $admin_demo = \Drupal::routeMatch()->getRouteName() === 'block.admin_demo';
    $access = (\Drupal::currentUser()->hasPermission('administer blocks') && !$admin_route && !$admin_demo);

    // The 'Place Block' tab is a simple link, with no corresponding tray.
    $items['block_place'] = [
      '#cache' => [
        'contexts' => ['user.permissions', 'url.query_args'],
      ],
      '#type' => 'toolbar_item',
      'tab' => [
        '#access' => $access,
        '#type' => 'link',
        '#title' => t('Place block'),
        '#url' => Url::fromRoute('<current>', [], ['query' => $query]),
        '#attributes' => [
          'title' => $description,
          'class' => ['toolbar-icon', 'toolbar-icon-place-block-' . $status_class],
        ],
      ],
      '#wrapper_attributes' => [
        'class' => ['toolbar-tab', 'block-place-toolbar-tab', $wrapper_class],
      ],
      '#weight' => 100,
      '#attached' => [
        'library' => [
          'express_block_place/drupal.express_block_place.icons',
        ],
      ],
    ];
    return $items;
  }
}
