<?php
/**
 * Template:Console:Help
 * 
 * @title      Console template - ListPtf
 * @desc       Template for the Console:ListPtf command
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfData array */
?>
<ul>
    <?php foreach ($ptfData as $ptfRow):?>
        <li>
            <pre><?php 
                echo json_encode(
                    array(
                        'name'    => $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME],
                        'version' => (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VERSION],
                        'width'   => (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH],
                        'height'  => (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT],
                    )
                );
            ?></pre>
            <pre><?php echo $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_CONTENT];?></pre>
        </li>
    <?php endforeach; ?>
</ul>