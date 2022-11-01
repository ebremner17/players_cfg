<?php

namespace Drupal\players_cfg\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * A column layout base.
 */
class PlayersColumnLayoutBase extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    // Get Parents configuration form (which by default adds the Admin Label).
    $form = parent::buildConfigurationForm($form, $form_state);

    // Get the config for this layout.
    $configuration = $this->getConfiguration();

    // The options for the column widths.
    $columnOptions = $this->getColumnOptions();

    // The form element for the column widths.
    $form['column_class'] = [
      '#type' => 'select',
      '#title' => $this->t('Column widths'),
      '#default_value' => !empty($configuration['column_class']) ? $configuration['column_class'] : $columnOptions['default'],
      '#options' => $columnOptions['columns'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

    // Call parent and let it do its thing (like set the label).
    parent::submitConfigurationForm($form, $form_state);

    // Set the column class in the config.
    $this->configuration['column_class'] = $form_state->getValue(
      ['column_class'],
      NULL
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {

    // Build the render array as usual.
    $build = parent::build($regions);

    // Retrieve the config for the layout.
    $configuration = $this->getConfiguration();

    // Set the column class to be used in the layout template.
    $build['#settings']['column_class'] = $configuration['column_class'];

    return $build;
  }

  /**
   * Helper function to get column options defined in *.layout.yml file.
   *
   * @return array[]
   *   an array containing string options and the default column.
   */
  public function getColumnOptions() {
    return $this->getPluginDefinition()->get('column_options');
  }

}
