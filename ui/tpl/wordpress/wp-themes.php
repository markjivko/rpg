<?php
/**
 * Template:Themes
 * 
 * @title      Themes template
 * @desc       Template for the "Themes" page
 * @copyright  (c) 2021, Stephino
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    GPL v3+, https://gnu.org/licenses/gpl-3.0.txt
 */
!defined('STEPHINO_RPG_ROOT') && exit();

// Theme slug or null if not in edit mode
$themeSlug = Stephino_Rpg_Utils_Sanitizer::getTheme();
?>
<!--[if lt IE 10]><meta http-equiv="refresh" content="0; url=<?php echo esc_attr(get_dashboard_url());?>"><![endif]-->
<!-- stephino-rpg -->
<div class="content" data-editor="<?php echo (null === $themeSlug ? 'false' : 'true');?>">
    <div role="info-badge">
        <div class="icon"></div>
        <span class="message"></span>
    </div>
    <div class="row no-gutters" data-role="header">
        <div class="col-12 banner">
            <div class="logo"></div>
            <div class="info">
                <?php echo Stephino_Rpg::PLUGIN_NAME;?> 
                <span class="version">v. <?php echo Stephino_Rpg::PLUGIN_VERSION;?></span>
                <?php echo esc_html__('Themes', 'stephino-rpg');?>
                <?php if (null !== $themeSlug): ?>
                    &gt; <b><?php echo esc_html(Stephino_Rpg_Utils_Themes::getTheme($themeSlug)->getName());?></b>
                    <?php if (!Stephino_Rpg_Utils_Themes::getTheme($themeSlug)->isActive()):?>(<?php echo esc_html__('Inactive', 'stephino-rpg');?>)<?php endif;?>
                <?php endif;?>
            </div>
        </div>
    </div>
    <?php if (null === $themeSlug): ?>
        <div class="row no-gutters justify-content-center" data-role="content">
            <?php foreach(Stephino_Rpg_Utils_Themes::getInstalled() as $theme): ?>
                <div class="col-12 col-lg-6 col-xl-4 p-2" data-role="theme-card" data-slug="<?php echo esc_attr($theme->getThemeSlug());?>">
                    <div class="banner"><div class="glow"></div></div>
                    <div class="card col-12 <?php if ($theme->isActive()):?>active<?php endif;?>">
                        <h4>
                            <span>
                                <?php if (!$theme->isDefault()) { echo esc_html__('Theme', 'stephino-rpg');} ?>
                                <b><?php echo esc_html($theme->getThemeSlug());?></b>
                                <?php echo esc_html__('by', 'stephino-rpg');?> 
                                <a rel="noreferrer" target="_blank" href="<?php echo esc_attr($theme->getAuthorUrl());?>"><?php 
                                    echo strlen($theme->getAuthor()) ? esc_html($theme->getAuthor()) : esc_html__('Unknown', 'stephino-rpg');
                                ?></a>
                                <?php if ($theme->isDefault()) { echo '(' . esc_html__('Design locked', 'stephino-rpg') . ')'; } ?>
                            </span>
                            
                            <?php if (!$theme->isDefault()):?>
                                <span data-role="export" class="btn btn-primary ml-2"><?php echo esc_html__('Export', 'stephino-rpg');?></span>
                            <?php endif;?>
                                
                            <?php if (!$theme->isDefault() && !$theme->isActive()):?>
                                <span data-role="delete" class="btn btn-danger ml-2">&times;</span>
                            <?php endif;?>
                        </h4>
                        <div data-url="<?php echo esc_attr($theme->getFileUrl(Stephino_Rpg_Theme::FOLDER_IMG_UI . '/768.png'));?>" data-effect="parallax"></div>
                        <article class="p-4">
                            <div class="mb-4">
                                <b><?php echo esc_html($theme->getName());?></b><br/>
                                <?php echo esc_html(Stephino_Rpg_Utils_Lingo::ellipsize($theme->getDescription(), Stephino_Rpg_Theme::MAX_LENGTH_DESCRIPTION));?>
                            </div>
                            <div class="row">
                                <?php if (!$theme->isDefault()):?>
                                    <div class="col">
                                        <a 
                                            href="<?php 
                                                echo esc_attr(
                                                    Stephino_Rpg_Utils_Media::getAdminUrl() . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_THEMES
                                                    . '&theme=' . $theme->getThemeSlug()
                                                );
                                            ?>" 
                                            class="btn <?php echo ($theme->isActive() ? 'btn-info' : 'btn-primary');?> w-100">
                                            <?php echo esc_html__('Design', 'stephino-rpg');?>
                                        </a>
                                    </div>
                                <?php endif;?>
                                <?php if (!$theme->isActive()):?>
                                    <div class="col">
                                        <button data-role="activate" class="btn <?php 
                                            echo (
                                                $theme->isDefault() || (count(Stephino_Rpg_Utils_Themes::getInstalled()) > 2 && !$theme->isActive()) 
                                                    ? 'btn-primary' 
                                                    : 'btn-info'
                                            );
                                            ?> w-100">
                                            <?php echo esc_html__('Activate', 'stephino-rpg') . (Stephino_Rpg::get()->isPro() ? '' : ' &#x1F512;');?>
                                        </button>
                                    </div>
                                <?php else:?>
                                    <?php if (1 === count(Stephino_Rpg_Utils_Themes::getInstalled())):?>
                                        <div class="col">
                                            <div class="btn btn-info w-100" data-role="add"><?php echo esc_html__('Modify design', 'stephino-rpg');?></div>
                                        </div>
                                    <?php else:?>
                                        <div class="col">
                                            <a href="<?php echo esc_attr(Stephino_Rpg_Utils_Media::getAdminUrl() . '-' . Stephino_Rpg_Renderer_Html::TEMPLATE_OPTIONS);?>" class="btn btn-info w-100">
                                                <?php echo Stephino_Rpg_Utils_Lingo::getOptionsLabel(false, true);?>
                                            </a>
                                        </div>
                                    <?php endif;?>
                                <?php endif;?>
                            </div>
                        </article>
                    </div>
                </div>
            <?php endforeach;?>
            <div class="add-circle" data-role="add">&plus;</div>
        </div>
    <?php else:?>
        <div 
            class="row align-items-center" 
            data-role="content" 
            data-theme-url="<?php echo esc_attr(Stephino_Rpg_Utils_Themes::getTheme($themeSlug)->getFileUrl());?>">
            <div class="col-12 col-lg-4 col-xl-3 d-flex align-items-center">
                <div 
                    data-role="explorer" 
                    data-explorer-theme="<?php echo $themeSlug;?>"
                    data-explorer-title="<?php echo esc_attr__('Theme assets', 'stephino-rpg');?>">
                </div>
            </div>
            <div class="col-12 col-lg-8 col-xl-9">
                <div data-role="viewer">
                    <div class="viewer-title"></div>
                    <div class="viewer-content"></div>
                    <div class="viewer-footer"></div>
                </div>
            </div>
        </div>
    <?php endif;?>
</div>
<?php if (null === $themeSlug): ?>
    <!-- Modals -->
    <div id="modal-template" class="modal" data-role="add-dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header no-gutters">
                    <h5 class="modal-title"><?php 
                        echo 1 === count(Stephino_Rpg_Utils_Themes::getInstalled())
                            ? esc_html__('Create your first theme', 'stephino-rpg')
                            : esc_html__('Create a new theme', 'stephino-rpg');
                    ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <?php if (1 === count(Stephino_Rpg_Utils_Themes::getInstalled())):?>
                            <div class="row">
                                <div class="col-12 text-center">
                                    <h5><?php echo esc_html__('Design modifications are only allowed as part of themes', 'stephino-rpg');?></h5>
                                </div>
                            </div>
                        <?php endif;?>
                        <div class="row mt-4 form-row">
                            <div class="col-12 col-lg-3">
                                <label for="themeName">
                                    <h4><?php echo esc_html__('Theme name', 'stephino-rpg');?></h4>
                                </label>
                            </div>
                            <div class="col-12 col-lg-9 param-input">
                                <input 
                                    type="text" 
                                    class="form-control mb-2" 
                                    name="themeName" 
                                    id="themeName" 
                                    autocomplete="off"
                                    placeholder="<?php echo esc_attr__('Theme name');?>..." />
                            </div>
                        </div>
                        <div class="row form-row">
                            <div class="col-12 col-lg-3">
                                <label for="themeLicense">
                                    <h4><?php echo esc_html__('License', 'stephino-rpg');?></h4>
                                    <div class="param-desc">
                                        <?php echo sprintf(
                                            esc_html__('All themes are licensed under %s', 'stephino-rpg'),
                                            Stephino_Rpg_Theme::LICENSE_NAME
                                        );?>
                                    </div>
                                </label>
                            </div>
                            <div class="col-12 col-lg-9 param-input">
                                <a rel="noreferrer" class="theme-license" target="_blank" href="<?php echo esc_attr(Stephino_Rpg_Theme::LICENSE_URL);?>">
                                    <img src="<?php 
                                        echo Stephino_Rpg_Utils_Media::getPluginsUrl() . '/' . Stephino_Rpg::FOLDER_THEMES . '/' 
                                            . Stephino_Rpg_Theme::THEME_DEFAULT . '/' . Stephino_Rpg_Theme::FILE_LICENSE_IMG;
                                    ?>"/>
                                </a>
                            </div>
                        </div>
                        <div class="row form-row">
                            <div class="col-12 col-lg-3">
                                <label for="themeTemplate">
                                    <h4><?php echo esc_html__('Template', 'stephino-rpg');?></h4>
                                    <div class="param-desc">
                                        <?php echo esc_html__('Start a new theme by modifying an existing one', 'stephino-rpg');?>
                                    </div>
                                </label>
                            </div>
                            <div class="col-12 col-lg-9 param-input">
                                <select class="form-control" name="themeTemplate" id="themeTemplate">
                                    <?php foreach(Stephino_Rpg_Utils_Themes::getInstalled() as $theme): ?>
                                        <option value="<?php echo esc_attr($theme->getThemeSlug());?>"><?php 
                                            echo esc_html($theme->getName()) . ' ' 
                                                . esc_html__('by', 'stephino-rpg') . ' ' 
                                                . esc_html($theme->getAuthor());
                                        ?></option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <input 
                                    type="submit" 
                                    class="btn <?php echo (1 === count(Stephino_Rpg_Utils_Themes::getInstalled()) ? 'btn-info' : 'btn-primary');?> w-100" 
                                    value="<?php echo esc_attr__('Create theme', 'stephino-rpg');?>" />
                            </div>
                        </div>
                    </form>
                    <div class="row mt-4 form-row">
                        <div class="col-12">
                            <span data-role="divider"><?php echo esc_html__('Or', 'stephino-rpg');?></span>
                            <button class="btn btn-primary w-100" data-role="import"><?php echo esc_html__('Import theme', 'stephino-rpg');?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>
<!-- /stephino-rpg -->