<?php
/**
 * Template:Dashboard
 * 
 * @title      Dashboard template
 * @desc       Template for the "Dashboard" page
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();
?>
<!--[if lt IE 10]><meta http-equiv="Refresh" content="0; url=<?php echo Stephino_Rpg_Utils_Lingo::escape(get_dashboard_url());?>"><![endif]-->
<!-- stephino-rpg -->
<div class="content">
    <div role="info-badge">
        <div class="icon"></div>
        <span class="message"></span>
    </div>
    <div class="row no-gutters" data-role="header">
        <div class="col-12 banner">
            <div class="logo"></div>
            <div class="info">Stephino RPG 
                <span class="version">v. <?php echo Stephino_Rpg::PLUGIN_VERSION;?></span>
                <?php echo esc_html__('Dashboard', 'stephino-rpg');?>
            </div>
        </div>
    </div>
    <div class="row no-gutters" data-role="content">
        <div class="col-12 col-lg-6 col-xl-4 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_REAL_TIME;?>">
                <h4><?php echo esc_html__('Online users', 'stephino-rpg');?></h4>
                <article></article>
                <span class="loading"></span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-4 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_ACTIVE;?>">
                <h4><?php echo esc_html__('Active users', 'stephino-rpg');?></h4>
                <article></article>
                <span class="loading"></span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-4 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_USERS_TOTAL;?>">
                <h4><?php echo esc_html__('Total users', 'stephino-rpg');?></h4>
                <article></article>
                <span class="loading"></span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-8 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-role="table" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_LEADER_BOARD;?>">
                <h4><?php echo esc_html__('Leader board', 'stephino-rpg');?></h4>
                <article class="p-4">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('User', 'stephino-rpg');?></th>
                                <th width="150"><?php echo esc_html__('Score', 'stephino-rpg');?></th>
                                <th width="100"><?php echo esc_html__('Battles', 'stephino-rpg');?></th>
                                <th width="100"><?php echo esc_html__('Join date', 'stephino-rpg');?></th>
                                <th width="50"><?php echo esc_html__('Online', 'stephino-rpg');?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </article>
                <span class="loading"></span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-4 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_ANNOUNCEMENT;?>">
                <h4><?php echo esc_html__('Announcement', 'stephino-rpg');?></h4>
                <article class="d-flex justify-content-center align-items-center">
                    <form style="display: none;" class="col-12 p-4">
                        <div class="form-group">
                            <label for="announceTitle"><?php echo esc_html__('Title', 'stephino-rpg');?></label>
                            <input 
                                type="text"
                                autocomplete="off" 
                                id="announceTitle" 
                                class="form-control" 
                                placeholder="<?php echo esc_attr__('Announcement title', 'stephino-rpg');?>"/>
                        </div>
                        <div class="form-group">
                            <label for="annnounceContent"><?php echo esc_html__('Content', 'stephino-rpg');?> <span class="info"><?php echo esc_html__('MarkDown enabled', 'stephino-rpg');?></span></label>
                            <textarea id="annnounceContent" class="form-control" placeholder="<?php echo esc_attr__('Announcement content', 'stephino-rpg');?>"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="announceDays"><?php echo esc_html__('Visibility', 'stephino-rpg');?></label>
                            <select id="annnounceDays" class="form-control">
                                <?php for($day = 1; $day <= 30; $day++):?>
                                <option value="<?php echo $day;?>"><?php echo $day . ' ' . esc_html(_n('day', 'days', $day, 'stephino-rpg'));?></option>
                                <?php endfor;?>
                            </select>
                        </div>
                        <button class="btn btn-primary w-100"><?php echo esc_html__('Announce', 'stephino-rpg');?></button>
                    </form>
                    <div data-role="announcement" class="col-12 p4" style="display: none;">
                        <div class="card col-12 mb-2">
                            <h5></h5>
                            <p class="card col-12"></p>
                        </div>
                        <button class="btn btn-primary w-100 mb-2"><?php echo esc_html__('Delete', 'stephino-rpg');?></button>
                    </div>
                </article>
                <span class="loading"></span>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-4 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_REVENUE;?>">
                <h4><?php echo esc_html__('Revenue', 'stephino-rpg');?></h4>
                <article></article>
                <span class="loading"></span>
                <?php if (strlen(Stephino_Rpg::PLUGIN_URL_PRO)) :?>
                    <a class="btn btn-danger" target="_blank" href="<?php echo esc_url(Stephino_Rpg::PLUGIN_URL_PRO);?>">
                        <span><?php echo esc_html__('Unlock Game', 'stephino-rpg');?> &#x1F513;</span>
                    </a>
                <?php endif;?>
            </div>
        </div>
        <div class="col-12 col-lg-6 col-xl-8 p-2">
            <div class="banner"><div class="glow"></div></div>
            <div class="card col-12" data-role="table" data-card="<?php echo Stephino_Rpg_Db_Model_Statistics::EXPORT_TRANSACTIONS;?>">
                <h4><?php echo esc_html__('Transactions', 'stephino-rpg');?></h4>
                <article class="p-4">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__('User', 'stephino-rpg');?></th>
                                <th><?php echo esc_html__('Package', 'stephino-rpg');?></th>
                                <th><?php echo esc_html__('Invoice ID', 'stephino-rpg');?></th>
                                <th width="150"><?php echo esc_html__('Date', 'stephino-rpg');?></th>
                                <th width="100"><?php echo esc_html__('Amount', 'stephino-rpg');?></th>
                                <th width="50"><?php echo esc_html__('Status', 'stephino-rpg');?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </article>
                <span class="loading"></span>
            </div>
        </div>
    </div>
</div>
<!-- /stephino-rpg -->