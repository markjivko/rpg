<?php
/**
 * Template:Dialog:Common Pagination
 * 
 * @title      Common pagination template
 * @desc       Template for pagination
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $pagination Stephino_Rpg_Utils_Pagination */
if (isset($pagination) 
    && $pagination instanceof Stephino_Rpg_Utils_Pagination
    && $pagination->getPagesTotal() > 1):
?>
<ul data-role="pagination" class="pagination pagination-lg mb-0 justify-content-center">
    <?php 
        foreach ($pagination->getList() as $paginationItem):
            $paginationItemAction = null !== $paginationItem 
                && $paginationItem != $pagination->getPageCurrent() 
                && null !== $pagination->getAction()
                    ? ('data-click="' . $pagination->getAction() . '" data-click-args="' . $paginationItem . '"')
                    : '';
            $paginationItemClass = null === $paginationItem
                ? 'disabled'
                : ($paginationItem == $pagination->getPageCurrent()
                    ? 'active'
                    : ''
                );
    ?>
    <li class="page-item">
        <span <?php echo $paginationItemAction;?>
            class="page-link <?php echo $paginationItemClass;?>">
            <?php echo (null === $paginationItem ? '&#8230;' : $paginationItem);?>
        </span>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>