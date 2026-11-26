<?php
/**
 * My account: change the sign-in password.
 */

require __DIR__ . '/lib.php';

$errors = [];
$done = false;

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $current = (string) ($_POST['current_password'] ?? '');
    $new = (string) ($_POST['new_password'] ?? '');
    $repeat = (string) ($_POST['repeat_password'] ?? '');

    $stmt = $pdo->prepare('SELECT password FROM employees WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        $errors[] = 'Your current password is not right.';
    }
    if (strlen($new) < 8) {
        $errors[] = 'The new password needs at least 8 characters.';
    } elseif ($new !== $repeat) {
        $errors[] = 'The two new passwords are not the same.';
    }

    if (!$errors) {
        $pdo->prepare('UPDATE employees SET password = ? WHERE id = ?')
            ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['user_id']]);
        $done = true;
    }
}

page_start('My account', 'account');
?>

<h1>My account</h1>
<p>Signed in as <strong><?= h($_SESSION['full_name'] ?? '') ?></strong> (<?= h($_SESSION['username'] ?? '') ?>)</p>

<?php if ($done): ?>
  <div class="flash" role="status"><span>Your password was changed. Use the new one next time you sign in.</span></div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="notice notice-error" role="alert"><?= implode('<br>', array_map('h', $errors)) ?></div>
<?php endif; ?>

<form method="post" class="car-form">
  <?= csrf_field() ?>
  <section class="form-section">
    <h2>Change my password</h2>
    <div class="field">
      <label for="current_password">Current password</label>
      <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
    </div>
    <div class="field">
      <label for="new_password">New password <span class="optional">at least 8 characters</span></label>
      <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="8" required>
    </div>
    <div class="field">
      <label for="repeat_password">Type the new password again</label>
      <input type="password" id="repeat_password" name="repeat_password" autocomplete="new-password" minlength="8" required>
    </div>
  </section>
  <div class="form-buttons">
    <button type="submit" class="btn btn-primary btn-big">Change password</button>
  </div>
</form>

<?php page_end(); ?>
