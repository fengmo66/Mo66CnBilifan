<?php
/**
 * b站追番插件
 *
 * @package Mo66CnBilifan
 * @author FengMo
 * @version 1.0.1
 * @link http://Mo66.cn
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once ('classFunctions.php');//引入文件
class Mo66CnBilifan_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('page_bilifan.php')->navBar = array('Mo66CnBilifan_Plugin', 'render');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Text('Mo66CnBilifan_uid', NULL, 'uid', _t('请输入b站uid'));
        $form->addInput($name);
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
        echo '<meta name="referrer" content="never">';
        echo '<link rel="stylesheet" type="text/css" href="' . Helper::options()->pluginUrl . '/Mo66CnBilifan/css/style.css" />';
        $uid=Typecho_Widget::widget('Widget_Options')->plugin('Mo66CnBilifan')->Mo66CnBilifan_uid;
        $bili=new BilibiliAnimeInfo($uid);
        for($i=0;$i<$bili->sum;$i++){
            echo '<a href="https://www.bilibili.com/bangumi/play/ss'.$bili->season_id[$i].'" target=\'_blank\' class=\'Mo66CnBilifanItem\'>
                        <img src="'.$bili->image_url[$i].'" />
                        <div class=\'textBox\'>'.$bili->title[$i].'<br>
                        '.$bili->evaluate[$i].'<br>
                        <div class=\'jinduBG\'>
                        <div class=\'jinduText\'>进度:'.$bili->progress[$i]."/". $bili->total[$i].'</div>
                        <div class=\'jinduFG\' style=\'width:'.Functions::percent($bili->progress[$i],$bili->total[$i]).'%;\'>
                        </div>
                        </div>
                        </div>
                    </a>';
        }
    }
}