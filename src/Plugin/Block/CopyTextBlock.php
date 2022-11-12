<?php

namespace Drupal\players_cfg\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Copy text block.
 *
 * @Block(
 *   id = "cbl_copy_text",
 *   admin_label = @Translation("Copy text"),
 * )
 */
class CopyTextBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a BlockComponentRenderArray object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The config factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {

    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // The render of the copy text using the text format.
    $copy_text['text'] = [
      '#type' => 'processed_text',
      '#text' => $this->configuration['copy_text']['value'],
      '#format' => $this->configuration['copy_text']['format'],
    ];

    $copy_text['bg_colour'] = $this->configuration['bg_colour'];

    // Return custom template with variable.
    return [
      '#theme' => 'cbl_copy_text',
      '#copy_text' => $copy_text,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div class="form-type-color-picker">';
    $form['#suffix'] = '</div>';

    // The copy text element.
    $form['copy_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Copy text'),
      '#cols' => 60,
      '#rows' => 5,
      '#format' => $this->configuration['copy_text']['format'] ?? 'full_html',
      '#default_value' => $this->configuration['copy_text']['value'] ?? NULL,
    ];

    $form['colours'] = [
      '#type' => 'details',
      '#title' => $this->t('Colours'),
      '#open' => TRUE,
    ];

    $form['colours']['bg_colour'] = [
      '#type' => 'color_picker',
      '#title' => $this->t('Background Colour'),
      '#description' => $this->t('Select the background colour.'),
      '#default_value' => $this->configuration['bg_colour'] ?? '#FFFFFF',
      '#color_values' => _players_cfg_colours(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {

    // Load in the values from the form_sate.
    $values = $form_state->getValues();

    // Set the config for the copy text block.
    $this->configuration['copy_text'] = $values['copy_text'];

    // Set the config for the colours.
    $this->configuration['bg_colour'] = $values['colours']['bg_colour'];
    $this->configuration['text_colour'] = $values['colours']['text_colour'];
  }

}

