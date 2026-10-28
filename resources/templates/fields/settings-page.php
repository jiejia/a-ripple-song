<?php
/**
 * Settings page with its field form.
 *
 * @var string $title Page title.
 * @var bool $saved Whether the last save succeeded.
 * @var array<string,string> $errors Validation errors of the last save.
 * @var string $action admin-post.php action, also the option name.
 * @var string $nonceAction Nonce action of the form.
 * @var bool $hasFields Whether the page has any fields.
 * @var Closure():void $renderFields Prints the fields.
 */

defined('ABSPATH') || exit;
?>
<div class="wrap ars-settings">
    <h1><?php echo esc_html($title); ?></h1>
    <hr class="wp-header-end">

    <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Settings saved.', 'a-ripple-song'); ?></p></div>
    <?php endif; ?>

    <?php if ($errors !== []) : ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('Settings were not saved. Please fix the following:', 'a-ripple-song'); ?></p>
            <ul>
                <?php foreach ($errors as $message) : ?>
                    <li><?php echo esc_html($message); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($hasFields) : ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="ars-settings__form">
            <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
            <?php wp_nonce_field($nonceAction); ?>
            <?php $renderFields(); ?>
            <div class="ars-settings__actions">
                <?php submit_button(__('Save Settings', 'a-ripple-song'), 'primary', 'submit', false); ?>
            </div>
        </form>
    <?php endif; ?>
</div>
