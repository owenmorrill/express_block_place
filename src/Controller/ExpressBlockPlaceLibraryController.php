<?php

namespace Drupal\express_block_place\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Drupal\block\Controller\BlockLibraryController;

class ExpressBlockPlaceLibraryController extends BlockLibraryController {

  /**
   * Shows a list of blocks that can be added to a theme's layout.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param string $theme
   *   Theme key of the block list.
   *
   * @return array
   *   A render array as expected by the renderer.
   */
  public function listBlocks(Request $request, $theme) {
    // Since modals do not render any other part of the page, we need to render
    // them manually as part of this listing.
    if ($request->query->get(MainContentViewSubscriber::WRAPPER_FORMAT) === 'drupal_modal') {
      $build['local_actions'] = $this->buildLocalActions();
    }

    $headers = [
      ['data' => $this->t('Block')],
      ['data' => $this->t('Category')],
      ['data' => $this->t('Operations')],
    ];

    // Only add blocks which work without any available context.
    $definitions = $this->blockManager->getDefinitionsForContexts($this->contextRepository->getAvailableContexts());
    // Order by category, and then by admin label.
    $definitions = $this->blockManager->getSortedDefinitions($definitions);

    $region = $request->query->get('region');
    $weight = $request->query->get('weight');
    $rows = [];
    foreach ($definitions as $plugin_id => $plugin_definition) {
      $row = [];
      $row['title']['data'] = [
        '#type' => 'inline_template',
        '#template' => '<div class="block-filter-text-source">{{ label }}</div>',
        '#context' => [
          'label' => $plugin_definition['admin_label'],
        ],
      ];
      $row['category']['data'] = $plugin_definition['category'];
      $links['add'] = [
        'title' => $this->t('Place block'),
        'url' => Url::fromRoute('express_block_place.admin_add', ['plugin_id' => $plugin_id, 'theme' => $theme]),
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
          ]),
        ],
      ];
      if ($region) {
        $links['add']['query']['region'] = $region;
      }
      if (isset($weight)) {
        $links['add']['query']['weight'] = $weight;
      }
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];
      $rows[] = $row;
    }

    $build['#attached']['library'][] = 'block/drupal.block.admin';

    $build['filter'] = [
      '#type' => 'search',
      '#title' => $this->t('Filter'),
      '#title_display' => 'invisible',
      '#size' => 30,
      '#placeholder' => $this->t('Filter by block name'),
      '#attributes' => [
        'class' => ['block-filter-text'],
        'data-element' => '.block-add-table',
        'title' => $this->t('Enter a part of the block name to filter by.'),
      ],
    ];

    $build['blocks'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No blocks available.'),
      '#attributes' => [
        'class' => ['block-add-table'],
      ],
    ];

    return $build;
  }

}