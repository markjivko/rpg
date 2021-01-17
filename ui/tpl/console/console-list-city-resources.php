<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListCityResources
 * @desc       Template for the Console:ListCityResources command
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<ul>
    <?php foreach ($resourceNames as $resourceName => list($configResourceName, $dbKey)):?>
        <li>
            <b><?php echo $resourceName;?></b> (<i><?php echo $configResourceName;?></i>): <b><?php echo number_format($cityData[$dbKey], 2);?></b>
        </li>
    <?php endforeach; ?>
</ul>