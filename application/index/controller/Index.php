<?php
namespace app\index\controller;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWindow;  // 窗口最大化

class Index
{
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
        sleep(1);

        # 点击登录
        $this->login($driver);

        # 跳转到个人中心
        $driver->findElement(css('.myHead'))->click();
        sleep(2);  // 等待2秒

        # 跳转到我的课程 1=>我的课程  2=>我的课程套 3=>我的收藏
        $driver->findElement(css('.rightMenu li:nth-child(1)'))->click();
        sleep(1);

        # 从我的课程 => 所有课程
        $courses = $this->myCenter($driver);

        foreach ($courses as $key => $course) {
            # 课程 => 所有章节
            $this->myCourse($dirver,$course);
        }

        # 进入该课程
        $course = $courses[0]; // 取出第一个课程 元素
        $course->click();
        switchToEndWindow($driver); //切换至最后一个window 新标签
        sleep(3);

        # 移动到章节框
        $ele = $driver->findElement(css('.big-title'));
        $driver->executeScript("arguments[0].scrollIntoView();",[$ele]);

        # 获取课程所有章
        $chapters = $this->myChapter($driver);

        # 提取第一章
        $chapter = $chapters[0];

        # 点击展开第一章
        $ids = $chapter->getAttribute('ids');
        $elm->click(); // 点击展开
        sleep(1);
        // 判断是否已经展开
        $ul = $driver->findElement(
                css('.chapter_lecContent'.$ids)
            );
        $style = $ul->getAttribute('style');
        if ( !(preg_match('/height: \d{3}/',$style) >= 1) ) {
            # 元素不可见
            $elm->click(); // 再次点击展开
            sleep(1);
        }

        // 获取第一章所有小节
        $lis = $driver->findElements(
                css('.chapter_lecContent'.$ids.' li')
            );
        $lecs = [];  // 第一章所有小节
        foreach ($lis as $key => $li) {
            $it = [];
            $text = $li->getText();
            $text = preg_replace('/上次学习.*$/u','',$text);
            $it['text'] = $text;
            $lecs[] = $it;
        }

        // 提取第一小节
        $lec = $lis[0];
        // 观看
        $lec->click();
        $this->switchToEndWindow($driver); //切换至最后一个window 新标签

        sleep(2);

        $video = $driver->findElement(css('video.pv-video'));
        $src = $video->getAttribute('src');
        echo $src;

        sleep(3);

        $driver->close();  // 关闭当前标签页
        $this->switchToEndWindow($driver); //切换至最后一个window 新标签
        echo "success";

        sleep(13);
        die('success');

        $driver->element('css selector', '#login_password')->value(array('value' => str_split('1234')));
        $driver->element('css selector', '#login_remeberMe')->click();

        // 点击登录
        $driver->element('css selector', '#login_submit')->click();

        $driver->quit();exit('success');

        $element->sendKeys(WebDriverKeys::LEFT);
        $element->sendKeys("B");

        $driver->quit();exit('success');

        // 寻找当前元素
        $driver->findElement(WebDriverBy::id('kw'))->sendKeys('wwe')->submit();

        // 获取元素并输入数据
        $element = $driver->findElement(
            WebDriverBy::cssSelector('input[name=wd]')
        );
        $element->clear(); //清空
        $element->sendKeys("test value");

        //关闭浏览器
        //$driver->quit();
        exit('success');


        // 寻找当前元素
        $driver->findElement(WebDriverBy::id('su'))->sendKeys('wwe')->submit();

        // 等待新的页面加载完成....
        $driver->wait($waitSeconds)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::partialLinkText('100shuai')
            )
        );
        $driver->findElement(WebDriverBy::partialLinkText('100shuai'))->sendKeys('xxx')->click();   //一般点击链接的时候，担心因为失去焦点而抛异常，则可以先调用一下sendKeys，再click


        switchToEndWindow($driver); //切换至最后一个window

        // 等待加载....
        $driver->wait($waitSeconds)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::partialLinkText('SmackDown收视率创历史新低')
            )
        );
        echo iconv("UTF-8","GB2312",'标题2')."：" . $driver->getTitle() . "\n";    //cmd.exe中文乱码，所以需转码

        $driver->findElement(WebDriverBy::partialLinkText('SmackDown收视率创历史新低'))->click();

        switchToEndWindow($driver); //切换至最后一个window


        // 等待加载....
        $driver->wait($waitSeconds)->until(
            WebDriverExpectedCondition::titleContains('SmackDown收视率创历史新低')
        );
        echo iconv("UTF-8","GB2312",'标题3')."：" . $driver->getTitle() . "\n";    //cmd.exe中文乱码，所以需转码


        //关闭浏览器
        $driver->quit();

        // 获取当前页面title
        $title = $driver->getTitle();

        // 执行js
        //$js = "$('#kw').val('aaaa');";
        //$driver->executeScript($js);

        // 我的课程
        // 获取当前渲染后页面全部结果 字符串
        //$html = $driver->getPageSource();
        //file_put_contents('a.html',$html);
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
    }


    public function login($driver)
    {
        // 点击登录
        //$element = $driver->findElement(WebDriverBy::id('kw'));
        $element = $driver->findElement( css('.login-btn') );
        $element->click();

        // 输入账号密码
        $name = '13297963625';
        $pwd = 'LL605382289';

        $driver->findElement(css('#login_account'))->sendKeys($name);
        $driver->findElement(css('#login_password'))->sendKeys($pwd);
        $driver->findElement(css('#login_remeberMe'))->click(); # 请记住我
        sleep(1);

        // 点击登录
        $driver->findElement(css('#login_submit'))->click();
        sleep(3);
    }


    /**
     * 获取个人中心的我的课程的所有课程
     * @param  [type] $driver [description]
     * @return [type]         [description]
     */
    public function myCenter($driver)
    {
        // 获取所有课程
        $elms = $driver->findElements(css('.fl .coures-box .coures-title a'));
        $coures = []; // 课程的标题和链接
        foreach ($elms as $key => $elm) {
            $li = [];
            $li['text'] = $elm->getText();   //
            $li['link'] = $elm->getAttribute('href');   //
            $coures[] = $li;   // 保存课程的标题和链接
        }
        return $elms;
    }

    public function myCourse($driver,$course)
    {
        # 进入该课程
        $course->click();
        switchToEndWindow($driver); //切换至最后一个window 新标签
        sleep(3);

        # 移动到章节框
        $ele = $driver->findElement(css('.big-title'));
        $driver->executeScript("arguments[0].scrollIntoView();",[$ele]);

        # 课程 => 章
        $elms = $driver->findElements(css('.video .chapter .chapter-btitle'));
        $chapters = [];  // 保存章信息
        foreach ($elms as $key => $elm) {
            $li = [];
            $li['text'] = $elm->getText();   // 获取链接上的文字 : 第几章
            $chapters[] = $li;   // 保存课程章节的标题

            # 进入该章

        }
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
