<?php
namespace app\index\controller;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWindow;  // 窗口最大化

use think\Db;

/**
 * 课程套餐
 */
class Index
{
    protected $driver = '';

    protected $title = '2018年考研金融专硕考研协议班';
    protected $course = '';         // 课程
    protected $cslink = '';         // 课程链接
    protected $chapter = '';        // 章
    protected $lecture = '';        // 小节
    protected $lclink = '';         // 小节链接
    protected $vlink  = '';         // 视频链接
    protected $vtype  =  1 ;        // 视频类型 1->直播  2->教材课程

    public function index()
    {

        # 定义需要使用的函数
        $this->funcInit();

        # 打开浏览器
        list($driver,$wtime) = openBrower();

        # 测试
        # $this->testAnothor($driver);

        // 打开url链接
        $driver->get('http://e.kuakao.com/');
        sleep(8);

        # 点击登录
        $this->login($driver);

        # 打开新链接
        # 课程套餐 > 2018年考研金融专硕考研协议班
        $driver->get('http://e.kuakao.com/classPackage/classPackageDetail/271');
        sleep(8);

        # 找到所有课程
        $courses = $this->myCenter($driver);
        foreach ($courses as $key => $course) {
            # 课程 => 所有章节
            if ( $key < 15  ) {
                continue;
            }
            try {
                $this->myCourse($driver,$course);
            } catch (\Exception $e) {
                echo "video is not of time \n";
                continue;
            }
        }

        ####################  ---THE-END---  ###########################
    }

    public function funcInit()
    {
        /**
         * 打开浏览器
         * @return [type] [description]
         */
        function openBrower()
        {
            $waitSeconds = 15;  //最长等待加载的时间，一般加载时间在0-15秒，如果超过15秒，报错。

            $host = 'http://localhost:4444/wd/hub'; // 默认访问端口
            //这里使用的是chrome浏览器进行测试，需到http://www.seleniumhq.org/download/上下载对应的浏览器测试插件
            // 我这里下载的是win32 Google Chrome Driver 2.38版：
            // https://chromedriver.storage.googleapis.com/index.html

            // 打开浏览器
            $capabilities = DesiredCapabilities::chrome();  // 定义浏览器 并指定等待期待时间:5000=>5s
            $driver = RemoteWebDriver::create($host, $capabilities, 5000);

            // 窗口最大化
            $driver->manage()->window()->maximize();

            return [$driver,$waitSeconds];
        }

        /**
         * css选择器
         * @param  string $selector 选择器
         * @return WedCssSelector   Web选择器
         */
        function css($selector='') {
            return WebDriverBy::cssSelector($selector);
        }

        /**
         * 跳转到最后一个标签页
         * @param  [type] $driver WebDriver
         * @return none
         */
        function switchToEndWindow($driver) {
            $arr = $driver->getWindowHandles();
            foreach ($arr as $k=>$v){
                if($k == (count($arr)-1)){
                    $driver->switchTo()->window($v);
                }
            }
        }

        /**
         * 判断元素是否存在
         * @param  [type]  $driver   [description]
         * @param  [type]  $selector [description]
         * @return boolean           [description]
         */
        function isElementExsit($selector,$driver){
            try {
                $element = $driver->findElement(css($selector));
                return true;
            } catch (\Exception $e) {
                echo 'element [ '. $selector .' ] is not found!' . "\n";
                return false;
            }
        }
    }

    /**
     * 点击登录框并输入账号登录
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function login($driver)
    {
        // 点击登录
        //$element = $driver->findElement(WebDriverBy::id('kw'));
        $element = $driver->findElement( css('.login-btn') );
        $element->click();

        // 输入账号密码
        $name = '13207152679';
        $pwd = 'Gong4921';

        $driver->findElement(css('#login_account'))->sendKeys($name);
        $driver->findElement(css('#login_password'))->sendKeys($pwd);
        $driver->findElement(css('#login_remeberMe'))->click(); # 请记住我
        sleep(3);

        // 点击登录
        $driver->findElement(css('#login_submit'))->click();
        sleep(5);
    }


    /**
     * 获取课程套餐I的所有课程
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function myCenter($driver)
    {
        // 获取所有课程
        $elms = $driver->findElements(css('.stage-line .stag-detail .detail-cont-box a'));
        $coures = []; // 课程的标题和链接
        foreach ($elms as $key => $elm) {
            $li = [];
            $li['text'] = $elm->getText();              // 课程标题
            $li['link'] = $elm->getAttribute('href');   // 课程链接
            $coures[] = $li;   // 保存课程的标题和链接

            $this->course = $li['text'];
            $this->cslink = $li['link'];
        }
        return $elms;
    }

    /**
     * 进入该课程,并获取课程的所有章
     * @param  [type] $driver [description]
     * @param  [type] $course [description]
     * @return [type]         [description]
     */
    public function myCourse($driver,$course)
    {
        # 进入该课程 [滚动焦点到元素]
        $driver->executeScript("arguments[0].scrollIntoView();",[$course]);
        sleep(6);
        $course->click();
        sleep(1);
        switchToEndWindow($driver); //切换至最后一个window 新标签
        sleep(9);

        # 保存标题
        if ( isElementExsit('.w h2 span:nth-child(1)',$driver) ) {
            $h2 = $driver->findElement(css('.w h2 span:nth-child(1)'));
            $this->course = $h2->getText();
        }

        # 先看是否有视频目录
        if ( isElementExsit(".tab-menu .video_flag",$driver) ) {
            /*  屏蔽视频课程
            # 视频目录
            $driver->findElement(css('.tab-menu .video_flag'))->click();
            sleep(2);

            $this->vtype = 2; # 教材课程
            $this->courseVideo($driver);*/
        }
        # 直播课程
        if ( isElementExsit('.tab-menu .live_flag',$driver) ) {
            # 直播课程 (跳过)
            $driver->findElement(css('.tab-menu .live_flag'))->click();
            sleep(2);

            $this->vtype = 1; # 直播课程
            $this->courseLive($driver);
        }

        if ( isElementExsit('.w h2 span',$driver) ) {
            $driver->close();  // 关闭当前标签页
        }
        sleep(3);
        switchToEndWindow($driver); // 切换至最后一个window 新标签
    }

    /**
     * 视频目录课程
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function courseVideo($driver)
    {
        # 判断是否存在章节内容
        if ( !isElementExsit('.big-title',$driver) ) {
            return false;
        }

        # 移动到章节框
        $ele = $driver->findElement(css('.big-title'));
        $driver->executeScript("arguments[0].scrollIntoView();",[$ele]);

        # 课程 => 章
        $elms = $driver->findElements(css('.video .chapter .chapter-btitle'));
        $chapters = [];   // 保存章信息
        foreach ($elms as $key => $elm) {
            sleep(3);
            $li = [];
            $li['text'] = $elm->getText();   // 获取链接上的文字 : 第几章
            $chapters[] = $li;   // 保存课程章节的标题

            $this->chapter = $li['text'];
            # 进入该章 (获取焦点后进入)
            $driver->executeScript("arguments[0].scrollIntoView();",[$elm]);
            $this->myChapter($driver,$elm);
        }

    }

    /**
     * 直播课程
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function courseLive($driver)
    {
        # 判断是否存在直播内容
        if ( !isElementExsit('.live_contents .chapter li',$driver) ) {
            return false;
        }

        # 移动到直播节框
        $ele = $driver->findElement(css('.live_contents'));
        $driver->executeScript("arguments[0].scrollIntoView();",[$ele]);

        # 课程 => 节
        $elms = $driver->findElements(css('.chapter .chapter-stitle li.livestudy'));
        $lectures = [];   // 保存章信息
        foreach ($elms as $key => $elm) {
            $li = [];
            $li['text'] = $key . ' ' . $elm->getAttribute('name');   // 获取链接上的文字 : 第几节
            $lectures[] = $li;   // 保存课程小节的标题

            $this->chapter = $key;         # 章
            $this->lecture = $li['text'];  # 节

            # 进入该小节观看 (获取焦点后进入)
            $driver->executeScript("arguments[0].scrollIntoView();",[$elm]);
            $this->myLecture($driver,$elm);
        }
    }

    /**
     * 章 => 每节 获取该章的所有小节课
     * @param  [type] $driver  [description]
     * @param  [type] $chapter [description]
     * @return [type]          [description]
     */
    public function myChapter($driver,$chapter)
    {
        # 点击展开该章
        $ids = $chapter->getAttribute('ids');
        $chapter->click(); // 点击展开
        sleep(1);
        // 判断是否已经展开
        $ul = $driver->findElement(css('.chapter_lecContent'.$ids));
        $style = $ul->getAttribute('style');
        if ( !(preg_match('/height: \d{3}/',$style) >= 1) ) {
            # 元素不可见
            $chapter->click(); // 再次点击展开
            sleep(1);
        }

        // 获取该章所有小节
        $lis = $driver->findElements(css('.chapter_lecContent'.$ids.' li'));
        $lecs = [];  // 该章所有小节
        foreach ($lis as $key => $li) {
            $it = [];
            $text = $li->getText();
            $text = preg_replace('/上次学习.*$/','',$text);
            $it['text'] = $text;
            $lecs[] = $it;

            $this->lecture = $text;
            $this->myLecture($driver,$li);
        }
    }

    /**
     * 进入该小节观看,并获取该节链接
     * @param  [type] $driver  [description]
     * @param  [type] $lecture [description]
     * @return [type]          [description]
     */
    public function myLecture($driver,$lecture)
    {
        $lecture->click();
        switchToEndWindow($driver); //切换至最后一个window 新标签
        sleep(31);

        if ( isElementExsit('iframe',$driver) ) {
            $js = "$('iframe').attr('id','iframe');";
            $driver->executeScript($js);
            sleep(1);
            $driver->switchTo()->frame("iframe");
            sleep(2);
        }

        if ( !isElementExsit('video',$driver) ) {
            echo "video not found ...";
        } else {
            $video = $driver->findElement(css('video'));
            $src = $video->getAttribute('src');

            $this->lclink = $driver->getCurrentURL();
            $this->vlink = $src;
            echo $src , "\n";  //该链接就是视频地址
            $this->saveVideo();
        }

        switchToEndWindow($driver); //切换至最后一个window 新标签
        sleep(2);
        $driver->close();  // 关闭当前标签页
        sleep(3);
        switchToEndWindow($driver); // 切换至最后一个window 新标签
        sleep(3);
    }

    public function saveVideo()
    {
         $data = [
            'title'  =>   $this->title,         // 标题
            'course'  =>   $this->course,       // 课程
            'cslink'  =>   $this->cslink,       // 课程链接
            'chapter'  =>   $this->chapter,     // 章
            'lecture'  =>   $this->lecture,     // 小节
            'lclink'  =>   $this->lclink,       // 小节链接
            'vlink'  =>   $this->vlink,         // 视频链接
            'vtype'  =>   $this->vtype,         // 视频类型 1->直播  2->教材课程
            'ctime' => time(),
            'utime' => time(),
         ];
         dump($data);
         Db::name('info')->insert($data);
    }


    /**
     * 测试操作
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function testAnothor($driver)
    {
        // 打开url链接
        $driver->get('http://www.baidu.com/');

        //$driver->element('css selector', '#kw')->value(['value'=>'qqq']);

        $driver->findElement(css('#kw'))->sendKeys('qqq');
        $driver->findElement(css('#su'))->click();
        sleep(3);
        $elms = $driver->findElements(WebDriverBy::cssSelector('.c-container'));
        dump($elms);
        die('success');
    }




}
