<?php
/**
 * Template:WordPress Notice
 * 
 * @title      WordPress Notice template
 * @desc       Template for admin notice for locked plugin
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<script type="text/javascript">
    (function ($) {
        var setCookie = function (cookieName, cookieValue, cookieExpDays) {
            // Prepare the expiration date
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + cookieExpDays);
            
            // Append the cookie
            document.cookie = cookieName + "=" + encodeURIComponent(cookieValue) + ((null === cookieExpDays) ? "" : "; expires=" + exdate.toUTCString());
        };
        $(document).off('click.stephino-rpg-unlock-notice-dismiss').on(
            'click.stephino-rpg-unlock-notice-dismiss',
            '.stephino-rpg-unlock-notice-dismiss',
            function (e) {
                e.preventDefault();
                var noticeOnkect = $(this).closest('.stephino-rpg-unlock-notice');
                noticeOnkect.fadeTo(100, 0, function () {
                    noticeOnkect.slideUp(100, function () {
                        noticeOnkect.remove();
                    });
                });
                setCookie('stephino-rpg-unlock-notice','<?php echo esc_attr(Stephino_Rpg::PLUGIN_VERSION); ?>', 14);
            }
        );
    })(window.jQuery);
</script>
<div class="updated stephino-rpg-unlock-notice" style="position: relative;">
    <p>
        <?php 
            echo sprintf(
                esc_html__('Buy %s version %s to unlock the Game Mechanics, enable PayPal micro-transactions and more!', 'stephino-rpg'),
                '<b>Stephino RPG Pro</b>',
                '<b>' . Stephino_Rpg::PLUGIN_VERSION_PRO . '+</b>'
            );
        ?>
        <a href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_PRO);?>"><b><?php echo esc_html__('Unlock Game', 'stephino-rpg');?></b></a>
    </p>
    <button type="button" class="notice-dismiss stephino-rpg-unlock-notice-dismiss">
        <span class="screen-reader-text"><?php echo esc_html__('Dismiss', 'stephino-rpg');?></span>
    </button>
</div>