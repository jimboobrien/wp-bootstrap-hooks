<?php

namespace benignware\wp\bootstrap_hooks;

function get_bootstrap_theme() {
  $theme_json = _bootstrap_get_theme_json();
  $resolve_preset = _bootstrap_get_preset_resolver($theme_json);
  $background_color = query_object($theme_json, 'styles.color.background') ?: (
    get_background_color()
      ? '#' . get_background_color()
      : ''
  );
  $bg_color_value = $resolve_preset($background_color);

  $bs_theme = '';

  if ($bg_color_value) {
    $bg_brightness = intval(brightness($bg_color_value));
    $is_dark = $bg_brightness <= 80;

    if ($is_dark) {
      $bs_theme = 'dark';
    }
  }

  return apply_filters('bootstrap_theme', $bs_theme);
}

function set_body_theme_attribute() {
  $bs_theme = get_bootstrap_theme();

  if ($bs_theme) {
    printf('<script>document.body.setAttribute("data-bs-theme","%s");</script>', esc_attr($bs_theme));
  }
}

add_action('wp_body_open', 'benignware\wp\bootstrap_hooks\set_body_theme_attribute', 0);