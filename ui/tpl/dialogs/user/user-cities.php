<?php
/**
 * Template:Dialog:User Cities
 * 
 * @title      User Cities dialog
 * @desc       Template for the user Cities list
 * @copyright  (c) 2020, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $userData array */
/* @var $userName string */
/* @var $userCitiesList array */
/* @var $myEmpire boolean */
?>
<div class="col-12 framed p-4">
    <table class="table table-hover table-striped table-responsive-lg" data-table="user-cities">
        <thead>
            <tr>
                <th width="50">#</th>
                <th width="150"><?php echo esc_html__('Name', 'stephino-rpg');?></th>
                <th><?php echo esc_html__('Level', 'stephino-rpg');?></th>
                <th><?php echo Stephino_Rpg_Config::get()->core()->getMetricPopulationName(true);?></th>
                <th width="150"><?php echo Stephino_Rpg_Config::get()->core()->getConfigIslandName(true);?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($userCitiesList as $cityRow):?>
            <tr>
                <th width="50"><?php echo $cityRow[Stephino_Rpg_Db_Table_Cities::COL_ID]; ?></th>
                <td width="150">
                    <button 
                        class="btn btn-default" 
                        data-click="<?php echo ($myEmpire ? 'navigate' : 'dialog');?>"
                        data-click-args="<?php echo ($myEmpire ? 'city' : 'dialogCityInfo') . ',' . intval($cityRow[Stephino_Rpg_Db_Table_Cities::COL_ID]);?>">
                        <span><?php 
                            echo Stephino_Rpg_Utils_Lingo::getCityName($cityRow);
                        ?></span>
                    </button>
                </td>
                <td>
                    <?php echo $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_LEVEL];?>
                </td>
                <td>
                    <span title="<?php echo number_format($cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]);?>">
                        <?php echo Stephino_Rpg_Utils_Lingo::isuFormat($cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_METRIC_POPULATION]);?>
                    </span>
                </td>
                <td width="150">
                    <button 
                        class="btn btn-default" 
                        data-click="navigate" 
                        data-click-args="island,<?php echo (int) $cityRow[Stephino_Rpg_Db_Table_Cities::COL_CITY_ISLAND_ID];?>">
                        <span><?php 
                            echo esc_html(
                                isset($cityRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME])
                                    ? $cityRow[Stephino_Rpg_Db_Table_Islands::COL_ISLAND_NAME]
                                    : Stephino_Rpg_Config::get()->core()->getConfigIslandName()
                            );
                        ?></span>
                    </button>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>