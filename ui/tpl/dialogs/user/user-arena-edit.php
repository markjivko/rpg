<?php
/**
 * Template:Dialog:User Arena
 * 
 * @title      User Arena dialog - Edit
 * @desc       Template for creating or editing platformers
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

/* @var $ptfId int*/
/* @var $ptfRow array*/
/* @var $tileSetC array*/
?>
<script type="text/javascript">var stephino_rpg_ptf_tiles = <?php echo json_encode(Stephino_Rpg_Db::get()->modelPtfs()->getTileList());?>;</script>
<div data-role="ptf-embed" class="framed ptf-creator" data-game-id="<?php echo $ptfId;?>" style="position: relative; overflow: hidden;">
    <svg viewBox="0 0 <?php echo Stephino_Rpg_Db::get()->modelPtfs()->getViewBox(true);?>" style="display: block; width: 100%; position: relative; z-index: 0;"></svg>
    <div data-tile-set="<?php echo implode(',', $tileSetC);?>"
         data-tile-set-width="<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];?>"
         data-tile-set-height="<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT];?>"
         data-tile-side="<?php echo Stephino_Rpg_Db_Model_Ptfs::TILE_SIDE;?>"
         data-effect="ptfCreator" 
         data-effect-args="canvas,<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH;?>,<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT;?>"></div>
    <div data-effect="ptfCreator" class="ptf-nav ptf-nav-w" data-effect-args="nav,w">W</div>
    <div data-effect="ptfCreator" class="ptf-nav ptf-nav-a" data-effect-args="nav,a">A</div>
    <div data-effect="ptfCreator" class="ptf-nav ptf-nav-s" data-effect-args="nav,s">S</div>
    <div data-effect="ptfCreator" class="ptf-nav ptf-nav-d" data-effect-args="nav,d">D</div>
</div>
<div class="col-12 framed p-4">
    <div class="row">
        <div class="col-12 col-md-6" data-role="ptf-tools" data-effect="ptfCreator" data-effect-args="brushes">
            <h4><?php echo esc_html__('Tiles', 'stephino-rpg');?></h4>
            <p><?php echo esc_html__('Click on a tile then place it on the canvas above', 'stephino-rpg');?></p>
        </div>
        <div class="col-12 col-md-6" data-role="ptfForm">
            <div data-name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME;?>" class="row">
                <div class="col-12">
                    <label for="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME;?>">
                        <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Name', 'stephino-rpg');?></h4>
                        <div class="param-desc"><?php echo esc_html__('Name of your game', 'stephino-rpg');?></div>
                    </label>
                </div>
                <div class="col-12 param-input">
                    <input 
                        type="text" 
                        autocomplete="off"
                        class="form-control" 
                        data-effect="charCounter"
                        maxlength="64"
                        name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME;?>" 
                        id="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME;?>"
                        value="<?php echo esc_attr($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_NAME]);?>" />
                </div>
            </div>
            <div data-name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH;?>" class="row">
                <div class="col-12">
                    <label for="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH;?>">
                        <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Width', 'stephino-rpg');?></h4>
                        <div class="param-desc">
                            <?php echo esc_html__('Game width', 'stephino-rpg');?> |
                            <?php echo sprintf(esc_html__('Navigate with %s', 'stephino-rpg'), '<b>WASD</b>');?>
                        </div>
                    </label>
                </div>
                <div class="col-12 param-input row no-gutters">
                    <input 
                        type="range" 
                        data-preview="true"
                        data-change="ptfCreatorResize"
                        name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH;?>" 
                        id="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH;?>"
                        value="<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_WIDTH];?>"
                        min="<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_WIDTH;?>" 
                        max="<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MAX_WIDTH;?>" />
                </div>
            </div>
            <div data-name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT;?>" class="row">
                <div class="col-12">
                    <label for="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT;?>">
                        <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Height', 'stephino-rpg');?></h4>
                        <div class="param-desc">
                            <?php echo esc_html__('Game height', 'stephino-rpg');?> |
                            <?php echo sprintf(esc_html__('Navigate with %s', 'stephino-rpg'), '<b>WASD</b>');?>
                        </div>
                    </label>
                </div>
                <div class="col-12 param-input row no-gutters">
                    <input 
                        type="range" 
                        data-preview="true"
                        data-change="ptfCreatorResize"
                        name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT;?>" 
                        id="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT;?>"
                        value="<?php echo (int) $ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_HEIGHT];?>"
                        min="<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MIN_HEIGHT;?>" 
                        max="<?php echo Stephino_Rpg_Db_Table_Ptfs::PTF_MAX_HEIGHT;?>" />
                </div>
            </div>
            <div data-name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY;?>" class="row">
                <div class="col-12">
                    <label for="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY;?>">
                        <h4 class="text-left p-0 mb-0"><?php echo esc_html__('Visibility', 'stephino-rpg');?></h4>
                        <div class="param-desc"><?php echo esc_html__('Get others to play your game by making it public', 'stephino-rpg');?></div>
                    </label>
                </div>
                <div class="col-12 param-input row no-gutters">
                    <select 
                        class="form-control"
                        name="<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY;?>" 
                        id="input_<?php echo Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY;?>">
                        <?php foreach (Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITIES as $ptfVisType):?>
                            <option value="<?php echo $ptfVisType;?>" <?php 
                                if ($ptfRow[Stephino_Rpg_Db_Table_Ptfs::COL_PTF_VISIBILITY] == $ptfVisType) {
                                    echo 'selected="selected"';
                                }
                            ?>><?php 
                                switch ($ptfVisType) {
                                    case Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PUBLIC:
                                        echo esc_html__('Public', 'stephino-rpg');
                                        break;
                                    
                                    case Stephino_Rpg_Db_Table_Ptfs::PTF_VISIBILITY_PRIVATE:
                                        echo esc_html__('Private', 'stephino-rpg');
                                        break;
                                }
                            ?></option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <button class="btn w-100 mt-4" data-click="ptfCreatorSave" data-click-args="<?php echo $ptfId;?>">
                <span><?php echo esc_html__('Save and Play', 'stephino-rpg');?></span>
            </button>
        </div>
    </div>
</div>