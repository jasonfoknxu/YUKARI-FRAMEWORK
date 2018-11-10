<?php

/**
 * YUKARI FRAMEWORK
 *
 * 为小缘粉丝俱乐部 YUKARI FAN CLUB 而设计的 PHP 框架
 *
 * @version 1.0.5
 * @author yukari.top admin@yukari.top
 */
class yukari {
    /**
     * @var string 目前页面
     */
    public $page = '';
    /**
     * @var string include的页面
     */
    public $include = '';
    /**
     * @var string 页面标题
     */
    public $title = '';
    /**
     * @var string 顶部路径
     */
    public $path = '/';
    /**
     * @var bool debug模式
     */
    public $debug = false;
    /**
     * @var string 延迟加载CSS（默认：不启用）
     */
    private $deferCSS = false;
    /**
     * @var string 图片文件夹路径
     */
    private $imgDir = 'images';
    /**
     * @var string CSS文件夹路径
     */
    private $cssDir = 'styles';
    /**
     * @var string JS文件夹路径
     */
    private $jsDir = 'scripts';

    private $css, $js, $jsVar, $inlineJS, $nav, $slides, $cards, $processResult, $globalMessage, $randomImage;

    /**
     * constructor
     */
    function __construct()
    {
        if (!defined('_DATE_'))
            define('_DATE_',date('Ymd'));
    }

    /**
     * @param bool $enable 开启/关闭debug模式
     */
    function debugMode($enable=true) {
        $this->debug = $enable;
        if ($enable) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            $this->debugMsg('DEBUG MODE ENABLED');
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }

    /**
     * @param string $msg debug信息
     */
    function debugMsg($msg) {
        echo "<!-- $msg -->";
    }

    /**
     * @param string $file 要 include 的档案
     * @param bool $once include_once? （默认：不使用）
     */
    function inc($file, $once=false) {
        $path = __DIR__.'/../'.$file;
        if (file_exists($path)) {
            if ($once)
                include_once($path);
            else
                include($path);
            if ($this->debug)
                $this->debugMsg("INCLUDE SUCCESS: $path");
        } else if ($this->debug) {
            $this->debugMsg("INCLUDE FAIL: $path");
        }
    }

    /**
     * 加入预建的页面 （例如：活动网页）
     *
     * @param string $site 要include的网站
     * @return bool 成功/失败
     * @todo 未完成
     */
    function inc_site($site) {
        $path = __DIR__.'/../_sites/'.$site.'/';
        if (is_dir($path)) {
            include($path);
            if ($this->debug)
                $this->debugMsg("INCLUDE SITE SUCCESS: $path");
            return true;
        } else if ($this->debug) {
            $this->debugMsg("INCLUDE SITE FAIL: $path");
        }
        return false;
    }

    /**
     * 加入 JavaScript 档案
     *
     * @param string $filename JS文件名 (不用后缀 *.min.js)
     * @param false|string $ver 版本号 （默认：当前日期）
     * @param bool $asyncDefer async/defer
     * @see yukari::asyncDefer() async/defer设置
     */
    function addJS($filename, $ver=_DATE_, $asyncDefer=false) {
        $this->js[] = array("name" => "$filename", "version" => $ver, "asyncDefer" => $asyncDefer);
    }

    /**
     * 加入 CSS 档案
     *
     * @param string $filename CSS文件名 (不用后缀 *.min.css)
     * @param false|string $ver 版本号 （默认：当前日期）
     */
    function addCSS($filename, $ver=_DATE_) {
        $this->css[] = array("name" => "$filename", "version" => $ver);//"$filename.min.css?ver=$ver";
    }

    /**
     * 页面加载 JavaScript
     *
     * @param null|string $file JS文件名 (不用后缀 *.min.js)（默认：已加入的 JS）
     * @param false|string $ver 版本号 （默认：当前日期）
     * @param int|bool $asyncDefer async/defer （默认：不使用 async/defer）
     * @see yukari::asyncDefer() async/defer设置
     */
    function printJS($file='', $ver=_DATE_, $asyncDefer=false) {
        if (!empty($file)) {
            $asyncDefer = $this->asyncDefer($asyncDefer);
            echo '<script '.$asyncDefer.' src="'.$this->path.$this->jsDir.'/'.$file.'.min.js?ver='.$ver.'"></script>';
        } else {
            foreach ($this->js as $js) {
                $asyncDefer = $this->asyncDefer($js['asyncDefer']);
                echo '<script '.$asyncDefer.' src="'.$this->path.$this->jsDir.'/'.$js['name'].'.min.js?ver='.$js['version'].'"></script>';
            }
        }
    }

    /**
     * JavaScript async & defer
     *
     * - 1: async + defer
     * - 2: async
     * - 3: defer
     * - 0: 不用 async & defer
     *
     * @param int|bool $val 设置值 (如上)
     * @return string <script>返回值
     */
    function asyncDefer($val) {
        if ($val===3) {
            return 'defer';
        } else if ($val===2) {
            return 'async';
        } else if ($val) {
            return 'async defer';
        } else {
            return '';
        }
    }

    /**
     * 页面加载 CSS
     *
     * @param null|string $file CSS文件名 (不用后缀 *.min.css)（默认：已加入的 CSS）
     * @param false|string $ver 版本号 （默认：当前日期）
     */
    function printCSS($file='', $ver=_DATE_) {
        $defer = $this->deferCSS;
        if ($defer)
            echo '<noscript id="deferred-styles">';
        if (!empty($file)) {
            echo '<link href="'.$this->path.$this->cssDir.'/'.$file.'.min.css?ver='.$ver.'" type="text/css" rel="stylesheet">';
        } else {
            foreach ($this->css as $css) {
                $cssfile = $css['name'].'.min.css?ver='.$css['version'];
                echo '<link href="'.$this->path.$this->cssDir.'/'.$cssfile.'" type="text/css" rel="stylesheet">';
            }
        }
        if ($defer)
            echo '</noscript>';
    }

    /**
     * 启用/关闭延迟加载CSS
     *
     * @param bool $enable 启用/关闭
     * @return bool 延迟加载CSS
     */
    function deferCSS($enable=true) {
        $this->deferCSS = $enable;
        if ($enable)
            $this->addJS('deferCSS','1.0.3');
        return $enable;
    }

    /**
     * 合并 CSS 档案
     *
     * @param string $groupedFilename 合并后的CSS文件名
     * @param false|string $version 合并后的版本号 （默认：当前日期）
     * @param array $CSSarray 要合并的CSS档案（默认：已加入的 CSS）
     */
    function groupCSS($groupedFilename='yukari-css', $version=_DATE_, $CSSarray=array()) {
        if (!empty($CSSarray)) {
            $CSS = $CSSarray;
        } else {
            $CSS = $this->css;
        }
        $groupedCSS = array();
        $groupedCSSpath = __DIR__.'/../'.$this->cssDir.'/'.$groupedFilename.'.min.css';
        if (file_exists($groupedCSSpath)) {
            $f = fopen($groupedCSSpath, 'r');
            $line = fgets($f);
            fclose($f);
            $groupedCSS = str_replace('/*','',$line);
            $groupedCSS = str_replace('*/','',$groupedCSS);
            $groupedCSS = trim($groupedCSS);//json_decode(trim($groupedCSS),true);
        }
        $CSSremark = json_encode($CSS);
        if ($CSSremark!=$groupedCSS) {
            $combinedCSS = '';
            foreach ($CSS as $c) {
                $path = __DIR__ . '/../'.$this->cssDir.'/' . $c['name'] . '.min.css';
                if (file_exists($path)) {
                    $cssfile = file_get_contents($path);
                    $combinedCSS .= $cssfile;
                }
            }
            file_put_contents($groupedCSSpath, "/*$CSSremark*/".PHP_EOL.$combinedCSS);
        }
        $this->css = array(array("name" => "$groupedFilename", "version" => $version));
    }

    /**
     * 合并 JavaScript 档案
     *
     * @param string $groupedFilename 合并后的JS文件名
     * @param false|string $version 合并后的版本号 （默认：当前日期）
     * @param int|bool $asyncDefer async/defer （默认：async + defer）
     * @see yukari::asyncDefer() async/defer设置
     * @param array $JSarray 要合并的JS档案（默认：已加入的 JS）
     */
    function groupJS($groupedFilename='yukari-js',$version=_DATE_,$asyncDefer=true,$JSarray=array()) {
        if (!empty($JSarray)) {
            $JS = $JSarray;
        } else {
            $JS = $this->js;
        }
        $groupedJS = array();
        $groupedJSpath = __DIR__.'/../'.$this->jsDir.'/'.$groupedFilename.'.min.js';
        if (file_exists($groupedJSpath)) {
            $f = fopen($groupedJSpath, 'r');
            $line = fgets($f);
            fclose($f);
            $groupedJS = str_replace('/*','',$line);
            $groupedJS = str_replace('*/','',$groupedJS);
            $groupedJS = trim($groupedJS);
        }
        $JSremark = json_encode($JS);
        if ($JSremark!=$groupedJS) {
            $combinedJS = '';
            foreach ($JS as $j) {
                $path = __DIR__ . '/../'.$this->jsDir.'/' . $j['name'] . '.min.js';
                if (file_exists($path)) {
                    $jsfile = file_get_contents($path);
                    $combinedJS .= $jsfile;
                }
            }
            file_put_contents($groupedJSpath, "/*$JSremark*/".PHP_EOL.$combinedJS);
        }
        $this->js = array(array("name" => "$groupedFilename", "version" => $version, "asyncDefer" => $asyncDefer));
    }

    /**
     *清除已加入的 CSS
     */
    function clearCSS() {
        $this->css = array();
    }

    /**
     *清除已加入的 JS
     */
    function clearJS() {
        $this->js = array();
    }

    /**
     * 加入 JavaScript 变量
     *
     * @param string $name 变量名
     * @param mixed $value 变量值
     */
    function addJSVar($name, $value) {
        $this->jsVar[$name] = json_encode($value);
    }

    /**
     * 加入内联 JavaScript
     *
     * @param string $js JS脚本
     */
    function addInlineJS($js) {
        $this->inlineJS[] = $js;
    }

    /**
     * 页面加载 JavaScript 变量
     *
     * @param string $name 变量名（默认：已加入的 JS 变量）
     * @param string $value 变量值（默认：''）
     */
    function printJSVar($name='', $value='') {
        $script = '<script>';
        if (!empty($name)) {
            $script .= "var $name = ".json_encode($value).';';
        } else {
            if (!empty($this->jsVar)) {
                foreach ($this->jsVar as $var => $val) {
                    $script .= "var $var = " . $val . ';';
                }
            }
        }
        $script .= '</script>';
        echo $script;
    }

    /**
     * 页面加载内联 JavaScript
     *
     * @param string $js JS脚本（默认：已加入的内联 JS）
     */
    function printInlineJS($js='') {
        $script = '<script>'; //' window.onload = function() {';
        if (!empty($js)) {
            $script .= $js;
        } else {
            if (!empty($this->inlineJS)) {
                foreach ($this->inlineJS as $j) {
                    $script .= $j;
                }
            }
        }
        $script .= '</script>';
        echo $script;
    }

    /**
     * 图标 (支持 fontawesome)
     *
     * @param string $icon 图标class
     * @param string $additonalClass （可选）额外的 CSS class
     * @return string 图标HTML
     * @link https://fontawesome.com/icons?d=gallery&m=free
     */
    function icon($icon, $additonalClass='') {
        return (!empty($icon)) ? '<i class="icon '.$icon.' '.$additonalClass.'" aria-hidden="true"></i>':'';
    }

    /**
     * 加入导航按钮
     *
     * @param string $URL 目标URL
     * @param string $title 显示标题
     * @param string $icon 图标
     * @see yukari::icon() 图标设置
     */
    function addNav($URL, $title, $icon) {
        $act = ($this->page==$URL) ? 'active' : '';
        $ic = $this->icon($icon,'mr-2');
        $this->nav[] = '<li class="nav-item mr-3 '.$act.'"><a class="nav-link rounded waves-effect link-effect" href="'.$this->path.$URL.'">'.$ic.$title.'</a></li>';
    }

    /**
     * 加入下拉菜单
     *
     * @param string $title 显示标题
     * @param string $icon 图标
     * @see yukari::icon() 图标设置
     * @param string $dropdownMenuClass 菜单 CSS class
     * @param array $dropdownMenu 菜单项目
     * @see yukari::dropdown() 菜单项目设置
     */
    function addDropDown($title, $icon, $dropdownMenuClass, $dropdownMenu) {
        $navID = count($this->nav);
        $ic = $this->icon($icon,'mr-2');
        $HTML = '<li class="nav-item dropdown"><a class="nav-link dropdown-toggle waves-effect link-effect" id="dropdown-'.$navID.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$ic.$title.'</a><div class="dropdown-menu '.$dropdownMenuClass.'" aria-labelledby="dropdown-'.$navID.'">';
        foreach ($dropdownMenu as $menu) {
            $HTML .= $menu;
        }
        $HTML .= '</div></li>';
        $this->nav[] = $HTML;
    }

    /**
     * 下拉菜单项目
     *
     * @param string $URL 目标URL
     * @param string $title 显示标题
     * @param string $icon 图标
     * @see yukari::icon() 图标设置
     * @return string 下拉菜单项目HTML
     */
    function dropdown($URL, $title, $icon) {
        $ic = $this->icon($icon,'mr-2');
        return '<a class="dropdown-item link-effect" href="'.$URL.'">'.$ic.$title.'</a>';
    }

    /**
     * 页面加载导航列
     */
    function printNav() {
        foreach ($this->nav as $nav) {
            echo $nav;
        }
    }

    /**
     * 加入轮播横幅
     *
     * @param string $image 图片路径/URL
     * @param string $url 目标URL
     * @param string $title （可选）显示标题
     * @param string $description （可选）显示简介
     * @param bool $newTab 点击后开新分页 (target=_blank)（默认：是）
     * @param bool $video 视频（默认：否）
     */
    function addSlide($image, $url='', $title='', $description='', $newTab=true, $video=false) {
        $this->slides[] = array($image, $url, $title, $description, $newTab, $video);
    }

    /**
     * 页面加载轮播横幅
     *
     * @param string $id 轮播横幅ID
     * @param string $image 图片路径/URL （默认：已加入的轮播横幅）
     * @param string $url 目标URL
     * @param string $title （可选）显示标题
     * @param string $description （可选）显示简介
     * @param bool $newTab 点击后开新分页 (target=_blank)（默认：是）
     * @param bool $video 视频（默认：否）
     * @todo 支持视频
     */
    function printSlider($id, $image='', $url='', $title='', $description='', $newTab=true, $video=false) {
        if (!empty($image)) {
            $slides = array($image, $url, $title, $description, $newTab, $video);
        } else {
            $slides = $this->slides;
            $this->slides = array();
        }
        $numOfSlides = count($slides);
        echo '<div id="'.$id.'" class="carousel slide carousel-fade" data-ride="carousel"><ol class="carousel-indicators">';
        for ($i=0;$i<$numOfSlides;$i++) {
            $class = ($i==0) ? 'class="active"':'';
            echo '<li data-target="#'.$id.'" data-slide-to="'.$i.'" '.$class.'></li>';
        }
        echo '</ol><div class="carousel-inner">';
        $first = true;
        foreach ($slides as $slide) {
            $openInNewTab = ($slide[4]) ? 'target="_blank"' : '';
            $class = '';
            if ($first) {
                $class = 'active';
                $first = false;
            }
            echo '<div class="carousel-item '.$class.'"><a href="'.$slide[1].'" '.$openInNewTab.'>';
            if (!empty($slide[2])) { // 有标题
                //echo '<div class="view"><img class="d-block w-100" src="'.$slide[0].'" alt="'.$slide[2].'"><div class="mask rgba-black-light"></div></div><div class="carousel-caption"><h1 class="h1-responsive">'.$slide[2].'</h1><p>'.$slide[3].'</p></div>';
                echo '<div class="view"><div class="d-block w-100 slide-image" style="background-image:url('."$slide[0]".');"></div><div class="mask rgba-black-light"></div></div><div class="carousel-caption"><h1 class="h1-responsive">'.$slide[2].'</h1><p class="h3-responsive">'.$slide[3].'</p></div>';
            } else {
                //echo '<img class="d-block w-100" src="'.$slide[0].'" alt="'.$slide[2].'">';
                echo '<div class="d-block w-100 slide-image" style="background-image:url('."$slide[0]".');"></div>';
            }
            echo '</a></div>';
        }
        echo '</div><a class="carousel-control-prev" href="#'.$id.'" role="button" data-slide="prev"><i class="fas fa-chevron-left fa-3x" aria-hidden="true"></i><span class="sr-only">上一页</span></a><a class="carousel-control-next" href="#'.$id.'" role="button" data-slide="next"><i class="fas fa-chevron-right fa-3x" aria-hidden="true"></i><span class="sr-only">下一页</span></a></div>';
    }

    /**
     * 加入卡片
     *
     * @param string $title 卡片标题
     * @param string $icon 卡片图片
     * @param string $description 卡片描述
     * @param string $url 目标URL
     * @param string $addClass （可选）额外的 CSS class
     */
    function addCard($title, $icon, $description, $url, $addClass='') {// Title, Icon, Description, URL
        $this->cards[] = array($title, $icon, $description, $url, $addClass);
    }

    function printCards($title='', $icon='', $description='', $url='', $addClass='') {
        $addClass = '';
        if (!empty($title) && !empty($description)) {
            $cards = array($title, $icon, $description, $url, $addClass);
        } else {
            $cards = $this->cards;
            $this->cards = array();
            //$addClass = $title;
        }
        foreach ($cards as $card) {
            echo '<div class="col-4 col-sm-4 col-md-3 col-lg-3 col-xl-2 px-1 px-md-2 mx-md-auto my-1 my-md-3"><div class="card h-100 '.$card[4].'"><div class="view overlay text-center h-100"><img class="card-img-top" src="'.$this->path.$this->imgDir.'/icons/'.$card[1].'.png" alt="'.$card[0].'" title="'.$card[0].'" /><div class="card-body text-center grey lighten-3 px-1 px-md-2 px-lg-3 h-100"><h4 class="h4-responsive card-title">'.$card[0].'</h4><p class="card-text">'.$card[2].'</p></div><a href="'.$card[3].'" target="_blank"><div class="mask waves-effect rgba-pink-slight"></div></a></div></div></div>';
        }

    }

    /**
     * 页面加入 wrapper（开）
     *
     * @param bool $fluid 自动宽度（默认：是）
     * @param string $addClass （可选）额外的 CSS class
     */
    function printWrapper($fluid=true, $addClass='') {
        if (!empty($addClass))
            $addClass = ' '.$addClass;
        if ($fluid)
            echo '<div class="container-fluid w-responsive mt-3'.$addClass.'">';
        else
            echo '<div class="container mt-3'.$addClass.'">';
    }

    /**
     * 页面加入 wrapper（关）
     */
    function printWrapperEnd() {
        echo '</div>';
    }

    /**
     * 消息框
     *
     * @param string $id 消息框ID
     * @param string $title 消息框标题
     * @param string $content 消息框內容 (HTML)
     * @param string $class （可选）额外的 CSS class
     * @param string $button （可选）按钮
     * @todo 消息框 footer / 底部按钮
     * @return string 消息框HTML
     */
    function modal($id, $title, $content, $class='', $button='') {
        $HTML = '<div id="'.$id.'" class="'.$id.' modal fade" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-dialog-centered '.$class.'" role="document"><div class="modal-content">';
        if (!empty($title)) {
            $HTML .= '<div class="modal-header bg-primary"><h5 class="modal-title" id="' . $id . '-title">' . $title . '</h5><button type="button" class="close" data-dismiss="modal" aria-label="关闭"><span aria-hidden="true" title="关闭">&times;</span></button></div>';
        }
        $HTML .= '<div class="modal-body">'.$content.'</div>';
        if (!empty($button)) { // TODO: WIP
            $HTML .= '<div class="modal-footer"><!--<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button><button type="button" class="btn btn-primary">Send message</button>--></div>';
        }
        $HTML .= '</div></div></div>';
        return $HTML;
    }

    /**
     * 加入全局通知
     *
     * @param string $content 通知內容
     * @param string $class （可选）通知 CSS class
     */
    function addGlobalMessage($content, $class='') {
        $this->globalMessage[] = array('content'=>$content, 'class'=>$class);
    }

    /**
     * 页面加入全局通知
     *
     * @param string $content 通知內容（默认：已加入的全局通知）
     * @param string $class （可选）通知 CSS class（默认：primary）
     */
    function printGlobalMessage($content='', $class='') {
        if (!empty($content)) {
            $msg = array(array('content'=>$content, 'class'=>$class));
        } else {
            $msg = $this->globalMessage;
        }
        $HTML = '';
        if (!empty($msg)) {
            foreach ($msg as $m) {
                if (empty($m['class']))
                    $m['class'] = 'primary';
                $HTML .= '<div class="alert alert-' . $m['class'] . ' m-0 text-center h4" role="alert">' . $m['content'] . '</div>';
            }
        }
        echo $HTML;
    }

    /**
     * 加入程序
     *
     * @param string $name 程序名
     */
    function addProcess($name) {
        $this->inc("_includes/process/$name.php");
    }

    /**
     * 运行程序
     *
     * @param string $result 程序返回值
     */
    function processResult($result) {
        $this->processResult = $result;
    }

    /**
     * 取得程序结果
     *
     * @return mixed 程序运行后的返回值
     */
    function getProcessResult() {
        return $this->processResult;
    }

    /**
     * 取代 PHP 的 $_GET
     *
     * @param string $name 变量名
     * @return mixed 变量值
     */
    function _get($name) {
        $return = (isset($_GET[$name])) ? $_GET[$name] : '';
        if ($this->debug)
            $this->debugMsg("GET: $name = $return");
        return $return;
    }

    /**
     * 取代 PHP 的 $_POST
     *
     * @param string $name 变量名
     * @return mixed 变量值
     */
    function _post($name) {
        $return = (isset($_POST[$name])) ? trim($_POST[$name]) : '';
        if ($this->debug)
            $this->debugMsg("POST: $name = $return");
        return $return;
    }

    /**
     * 先 $_GET 后 $_POST，如 $_GET 没有结果，改用 $_POST
     *
     * @param string $name 变量名
     * @return mixed 变量值
     */
    function _req($name) {
        $return = $this->_get($name);
        if (empty($return))
            $return = $this->_post($name);
        return $return;
    }

    /**
     * 缘历活动日期
     *
     * @param int $eventID 缘历活动ID
     * @return array 日期
     */
    function calendarEventDate($eventID) {
        $this->inc('yukari/core/records.php',true);
        $REC = new records();
        $data = $REC->getRecord($eventID);
        $date = explode('-', $data['date']);
        $date['year'] = $date[0];
        $date['month'] = $date[1];
        $date['day'] = $date[2];
        $date['display_date'] = "$date[0] 年 $date[1] 月 $date[2] 日";
        $date['display_month'] = "$date[0] 年 $date[1] 月";
        $date['display_year'] = "$date[0] 年";
        return $date;
    }

    /**
     * 缘历资料
     *
     * @param int $eventID 缘历活动ID
     * @param bool $modal 消息框模式（默认：是）
     * @return string 缘历资料HTML
     * @uses core: records
     */
    function calendarEventDetails($eventID,$modal=true) {
        $this->inc('yukari/core/records.php',true);
        $REC = new records();
        $data = $REC->getRecord($eventID);
        if (!empty($data)) {
            $date = DateTime::createFromFormat('Y-m-d', $data['date'])->format('Y 年 n 月 j 日');

            switch ($data['category']) {
                case 0: // 196
                case 2: // 22
                    $startText = '开播时间';
                    $endText = '下播时间';
                    $durText = '直播长度';
                    break;
                case 1: // YY
                    $startText = '来 YY 时间';
                    $endText = '离开 YY 时间';
                    $durText = '时长';
                    break;
                default:
                    $startText = '开始时间';
                    $endText = '结束时间';
                    $durText = '时长';
            }
            $titleText = '直播间标题';
            $contentText = '主要内容';
            $remarkText = '备注';
            $table = '<table class="table">';
            if (!$modal)
                $table .= '<tr><th colspan="2">【'.$date.'】</th></tr>';
            if (!empty($data['title']))
                $table .= '<tr><td><div>' . $titleText . '</div></td><td><div>' . nl2br($data['title']) . '</div></td></tr>';
            if ($data['startTime'] !== '00:00:00')
                $table .= '<tr><td><div>' . $startText . '</div></td><td><div>' . substr($data['startTime'], 0, -3) . '</div></td></tr>';
            if ($data['endTime'] !== '00:00:00') {
                $endTime = substr($data['endTime'], 0, -3);
                if ($data['startTime'] !== '00:00:00') {
                    if (intval(substr($data['startTime'], 0, 2)) > intval(substr($data['endTime'], 0, 2))) {
                        $endDate = new DateTime($data['date']);
                        $endDate->modify('+1 day');
                        $endDate = $endDate->format('Y 年 n 月 j 日');
                        $endTime .= "（{$endDate}）";
                    }
                }
                $table .= '<tr><td><div>' . $endText . '</div></td><td><div>' . $endTime . '</div></td></tr>';
            }
            if ($data['duration'] !== '00:00:00')
                $table .= '<tr><td><div>' . $durText . '</div></td><td><div>' . $REC->recordDuration($data['duration']) . '</div></td></tr>';
            if (!empty($data['content']))
                $table .= '<tr><td><div>' . $contentText . '</div></td><td><div>' . $data['content'] . '</div></td></tr>';
            if (!empty($data['remark']))
                $table .= '<tr><td><div>' . $remarkText . '</div></td><td><div>' . nl2br($data['remark']) . '</div></td></tr>';

            $startDate = $data['date'];
            $endDate = ($REC->checkPassDay($data['startTime'], $data['endTime'])) ? ((new DateTime($startDate))->modify('+1 day')->format('Y-m-d')) : $startDate;
            $roomCovers = $REC->getRoomCovers($startDate.' '.$data['startTime'],$endDate.' '.$data['endTime']);
            if (!empty($roomCovers)) {
                $table .= '<tr><td><div>直播间封面</div></td><td><div class="roomCovers">';
                foreach ($roomCovers as $rc) {
                    $table .= '<img src="'.$rc['thumb'].'" alt="直播间封面 ('.$rc['update'].')" />';
                }
                $table .='</div></td></tr>';

            }
            if (!$modal)
                return $table;
            return $this->modal('calendar-info',"【{$date}】",$table,'modal-primary modal-fluid w-responsive');
        }
        return '';
    }

    /**
     * 加入随机图片
     *
     * @param string $path 图片路径
     * @param string $title （可选）图片标题
     */
    function addRandomImage($path, $title='') {
        $this->randomImage[] = array('path'=>$path,'title'=>$title);
    }

    /**
     * 页面加入随机图片
     *
     * @todo 支持图片标题
     * @todo 支持自定数量
     */
    function printRandomImage(/*$num=1*/) {
        $random = array_rand($this->randomImage, 1);
        echo $this->randomImage[$random]['path'];
        /*foreach ($random as $r) {
            echo $r['path'];
        }*/
    }

}