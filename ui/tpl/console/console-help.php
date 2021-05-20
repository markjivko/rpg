<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - Help
 * @desc       Template for the Console:Help command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<?php if (null != $commandName):?>
    <?php echo $commandInfo;?>
<?php else:?>
    <b><?php echo Stephino_Rpg::PLUGIN_NAME;?> Console Commands</b><br/><br/>
    <?php 
        foreach ($methods as $methodKey => $methodInfo):
            list($cliMethodName, $cliMethodDetailsArray) = $methodInfo;
    ?>
        <b>`<?php echo $cliMethodName;?>`</b>: <?php echo current($cliMethodDetailsArray);?>
        <?php if($methodKey < count($methods) - 1): ?><br/><?php endif;?>
    <?php endforeach;?>
<?php endif;?>