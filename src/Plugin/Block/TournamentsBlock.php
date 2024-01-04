<?php

namespace Drupal\players_cfg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\players_reserve\Service\PlayersService;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Games block.
 *
 * @Block(
 *   id = "cbl_tournaments_block",
 *   admin_label = @Translation("Tournaments"),
 * )
 */
class TournamentsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The players service.
   *
   * @var \Drupal\players_reserve\Service\PlayersService
   */
  protected $playersService;

  /**
   * Constructs a BlockComponentRenderArray object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\players_reserve\Service\PlayersService $playersService
   *   The players service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    PlayersService $playersService,
    EntityTypeManager $entityTypeManager
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->playersService = $playersService;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('players_reserve.players_service'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the tournaments.
    $games['tourneys'] = $this->playersService->getTournaments();
    $games['type'] = 'tournament';

    // Return custom template with variable.
    return [
      '#theme' => 'cbl_tournaments',
      '#games' => $games,
      '#attached' => [
        'library' => [
          'players_theme/owl',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

  }

}

