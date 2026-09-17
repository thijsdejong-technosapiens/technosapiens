<?php

/**
 * @@var string $notificationTitle
 * @var string $notificationText
 * @var string $notificationType 'default' | 'informational' | 'success' | 'warning' | 'error'
 */

//set default values
if (!isset($notificationTitle)) $notificationTitle = '';
if (!isset($notificationText)) $notificationText = '';
if (!isset($notificationType)) $notificationType = 'default';

//validate type
if (!in_array($notificationType, ['default', 'informational', 'success', 'warning', 'error'])) $notificationType = 'default';

//bail if no text
if (!$notificationText) return false;

//set up container CSS classes
$containerClasses = ['ts-notification', 'ts-notification--type-' . trim($notificationType)];

//get correct dash icon class based on notification type
$dashIconClass = 'dashicons-info-outline';
switch ($notificationType) {
    case 'success':
        $dashIconClass = 'dashicons-yes-alt';
        break;
    case 'warning':
        $dashIconClass = 'dashicons-warning';
        break;
    case 'error':
        $dashIconClass = 'dashicons-dismiss';
        break;
}

?>

<div class="<?php echo implode(' ', $containerClasses); ?>" role="alert">

    <div class="ts-notification__icon-container">
        <i class="ts-notification__dashicons dashicons <?php echo $dashIconClass; ?>"></i>
    </div>

    <div class="ts-notification__meta">
        <span class="ts-notification__text">
            <?php echo ($notificationTitle ? '<strong class="ts-notification__title">' . $notificationTitle . '</strong>' . ' - ' : '') . $notificationText; ?>
        </span>
    </div>

</div>
