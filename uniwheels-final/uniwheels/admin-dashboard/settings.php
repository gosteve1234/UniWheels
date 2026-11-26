<?php
/**
 * Editable CMS Settings: Policies, Reservation Defaults & Showroom Contact Info
 */

require __DIR__ . '/lib.php';

$fields = [
    'default_policy'   => ['label' => 'Default Booking & Rental Policy', 'type' => 'textarea', 'required' => true],
    'default_deposit'  => ['label' => 'Default Reservation Deposit (PHP)', 'type' => 'number', 'required' => true],
    'max_hold_days'    => ['label' => 'Maximum Hold Duration (Days)', 'type' => 'number', 'required' => true],
    'hero_title'       => ['label' => 'Homepage Hero Headline', 'type' => 'text', 'required' => true],
    'hero_subtitle'    => ['label' => 'Homepage Hero Subtext', 'type' => 'textarea', 'required' => true],
    'showroom_address' => ['label' => 'Showroom Address (Visit Section)', 'type' => 'text', 'required' => true],
    'footer_address'   => ['label' => 'Footer Location Text', 'type' => 'text', 'required' => true],
    'showroom_hours'   => ['label' => 'Operating Hours', 'type' => 'text', 'required' => true],
    'showroom_phone'   => ['label' => 'Phone & Mobile Number', 'type' => 'text', 'required' => true],
    'showroom_email'   => ['label' => 'Contact Email', 'type' => 'text', 'required' => true],
    'messenger_url'    => ['label' => 'Messenger Link', 'type' => 'text', 'required' => true],
];

$values = uw_get_settings($pdo);
$errors = [];

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    foreach ($fields as $key => $cfg) {
        $val = trim((string) ($_POST[$key] ?? ''));
        if ($cfg['required'] && $val === '') {
            $errors[$key] = 'This field is required.';
        }
        $values[$key] = $val;
    }

    if ((float) $values['default_deposit'] < 0) {
        $errors['default_deposit'] = 'Deposit cannot be negative.';
    }
    $maxDays = (int) $values['max_hold_days'];
    if ($maxDays < 1 || $maxDays > 30) {
        $errors['max_hold_days'] = 'Please enter a number between 1 and 30 days.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        foreach ($fields as $key => $cfg) {
            $stmt->execute([$key, $values[$key]]);
        }
        flash('Website policies and showroom settings were updated.');
        header('Location: settings.php');
        exit;
    }
}

page_start('Policies & Content', 'settings');
show_flash();
?>

<p><a href="index.php" class="link">&larr; Back to Our Cars</a></p>
<h1>Policies &amp; Website Content</h1>
<p class="hint">Update booking policies, deposit rules, and showroom contact details without touching website code.</p>

<?php if ($errors): ?>
  <div class="notice notice-error" role="alert">Please check the fields marked in red.</div>
<?php endif; ?>

<form method="post" class="car-form">
  <?= csrf_field() ?>

  <section class="form-section">
    <h2>Booking &amp; Reservation Rules</h2>

    <div class="field">
      <label for="default_policy"><?= h($fields['default_policy']['label']) ?> <span class="required">required</span></label>
      <textarea id="default_policy" name="default_policy" rows="4" required><?= h($values['default_policy']) ?></textarea>
      <?php if (isset($errors['default_policy'])): ?><p class="field-error"><?= h($errors['default_policy']) ?></p><?php endif; ?>
    </div>

    <div class="field-row-2">
      <div class="field">
        <label for="default_deposit"><?= h($fields['default_deposit']['label']) ?> <span class="required">required</span></label>
        <input type="number" id="default_deposit" name="default_deposit" min="0" step="100" value="<?= h($values['default_deposit']) ?>" required>
        <?php if (isset($errors['default_deposit'])): ?><p class="field-error"><?= h($errors['default_deposit']) ?></p><?php endif; ?>
      </div>

      <div class="field">
        <label for="max_hold_days"><?= h($fields['max_hold_days']['label']) ?> <span class="required">required</span></label>
        <input type="number" id="max_hold_days" name="max_hold_days" min="1" max="30" value="<?= h($values['max_hold_days']) ?>" required>
        <?php if (isset($errors['max_hold_days'])): ?><p class="field-error"><?= h($errors['max_hold_days']) ?></p><?php endif; ?>
      </div>
    </div>
  </section>

  <section class="form-section">
    <h2>Homepage &amp; Showroom Details</h2>

    <div class="field">
      <label for="hero_title"><?= h($fields['hero_title']['label']) ?> <span class="required">required</span></label>
      <input type="text" id="hero_title" name="hero_title" value="<?= h($values['hero_title']) ?>" required>
      <?php if (isset($errors['hero_title'])): ?><p class="field-error"><?= h($errors['hero_title']) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="hero_subtitle"><?= h($fields['hero_subtitle']['label']) ?> <span class="required">required</span></label>
      <textarea id="hero_subtitle" name="hero_subtitle" rows="2" required><?= h($values['hero_subtitle']) ?></textarea>
      <?php if (isset($errors['hero_subtitle'])): ?><p class="field-error"><?= h($errors['hero_subtitle']) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="showroom_address"><?= h($fields['showroom_address']['label']) ?> <span class="required">required</span></label>
      <input type="text" id="showroom_address" name="showroom_address" value="<?= h($values['showroom_address']) ?>" required>
      <?php if (isset($errors['showroom_address'])): ?><p class="field-error"><?= h($errors['showroom_address']) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="footer_address"><?= h($fields['footer_address']['label']) ?> <span class="required">required</span></label>
      <input type="text" id="footer_address" name="footer_address" value="<?= h($values['footer_address']) ?>" required>
      <?php if (isset($errors['footer_address'])): ?><p class="field-error"><?= h($errors['footer_address']) ?></p><?php endif; ?>
    </div>

    <div class="field">
      <label for="showroom_hours"><?= h($fields['showroom_hours']['label']) ?> <span class="required">required</span></label>
      <input type="text" id="showroom_hours" name="showroom_hours" value="<?= h($values['showroom_hours']) ?>" required>
      <?php if (isset($errors['showroom_hours'])): ?><p class="field-error"><?= h($errors['showroom_hours']) ?></p><?php endif; ?>
    </div>

    <div class="field-row-2">
      <div class="field">
        <label for="showroom_phone"><?= h($fields['showroom_phone']['label']) ?> <span class="required">required</span></label>
        <input type="text" id="showroom_phone" name="showroom_phone" value="<?= h($values['showroom_phone']) ?>" required>
        <?php if (isset($errors['showroom_phone'])): ?><p class="field-error"><?= h($errors['showroom_phone']) ?></p><?php endif; ?>
      </div>

      <div class="field">
        <label for="showroom_email"><?= h($fields['showroom_email']['label']) ?> <span class="required">required</span></label>
        <input type="text" id="showroom_email" name="showroom_email" value="<?= h($values['showroom_email']) ?>" required>
        <?php if (isset($errors['showroom_email'])): ?><p class="field-error"><?= h($errors['showroom_email']) ?></p><?php endif; ?>
      </div>
    </div>

    <div class="field">
      <label for="messenger_url"><?= h($fields['messenger_url']['label']) ?> <span class="required">required</span></label>
      <input type="text" id="messenger_url" name="messenger_url" value="<?= h($values['messenger_url']) ?>" required>
      <?php if (isset($errors['messenger_url'])): ?><p class="field-error"><?= h($errors['messenger_url']) ?></p><?php endif; ?>
    </div>
  </section>

  <div class="form-buttons">
    <button type="submit" class="btn btn-primary btn-big">Save settings</button>
    <a href="index.php" class="btn btn-light btn-big">Cancel</a>
  </div>
</form>

<?php page_end(); ?>
