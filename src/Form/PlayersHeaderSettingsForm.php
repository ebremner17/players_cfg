<?php

namespace Drupal\players_cfg\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure header settings for this site.
 */
class PlayersHeaderSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'players_heading.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'players_heading_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get the config.
    $config = $this->config(static::SETTINGS);

    // Need this for the color picker.
    $form['#prefix'] = '<div class="form-type-color-picker">';
    $form['#suffix'] = '</div>';

    // The text element.
    $form['header_text'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Header text'),
      '#cols' => 60,
      '#rows' => 5,
      '#format' => $config->get('header_text')['format'] ?? 'full_html',
      '#default_value' => $config->get('header_text')['value'] ?? NULL,
    ];

    // Details for the background color.
    $form['color'] = [
      '#type' => 'details',
      '#title' => $this->t('Background Color'),
      '#open' => TRUE,
    ];

    // The background color.
    $form['color']['bg_color'] = [
      '#type' => 'color_picker',
      '#title' => $this->t('Background Colour'),
      '#description' => $this->t('Select the background colour.'),
      '#default_value' => $config->get('bg_color') ?? '#FFFFFF',
      '#color_values' => _players_cfg_colours(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get the values from the form state.
    $values = $form_state->getValues();

    // Set the config.
    $this->config(static::SETTINGS)
      ->set('header_text', $values['header_text'])
      ->set('bg_color', $values['bg_color'])
      ->save();

    // Clear all caches so the new header shows up.
    drupal_flush_all_caches();

    parent::submitForm($form, $form_state);
  }

}
