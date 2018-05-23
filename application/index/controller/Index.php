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
        // start Firefox with 5 second timeout
        $waitSeconds = 15;  //需等待加载的时间，一般加载时间在0-15秒，如果超过15秒，报错。
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        //这里使用的是chrome浏览器进行测试，需到http://www.seleniumhq.org/download/上下载对应的浏览器测试插件
        //我这里下载的是win32 Google Chrome Driver 2.38版：https://chromedriver.storage.googleapis.com/index.html?path=2.25/

        // 打开浏览器
        $capabilities = DesiredCapabilities::chrome();
        $driver = RemoteWebDriver::create($host, $capabilities, 5000);

        // 窗口最大化
        $driver->manage()->window()->maximize();

        // 打开url链接
        $driver->get('http://e.kuakao.com/');

        // 获取当前页面title
        $title = $driver->getTitle();

        // 执行js
        //$js = "$('#kw').val('aaaa');";
        //$driver->executeScript($js);

        //$element = $driver->findElement(WebDriverBy::id('kw'));
        $element = $driver->findElement( WebDriverBy::cssSelector('.login-btn') );
        $element->click();


        // 输入密码
        $name = '13297963625';
        $pwd = 'LL605382289';

        $driver->findElement(
                WebDriverBy::cssSelector('#login_account')
              )->sendKeys($name);

        $driver->findElement(
                WebDriverBy::cssSelector('#login_password')
              )->sendKeys($pwd);

        $driver->findElement(
                WebDriverBy::cssSelector('#login_remeberMe')
              )->click();

        sleep(1);


        // 点击登录
        $driver->findElement(
                WebDriverBy::cssSelector('#login_submit')
              )->click();
        sleep(3);


        // 跳转到个人中心
        $driver->findElement(
                WebDriverBy::cssSelector('.myHead')
              )->click();

        sleep(2);  // 等待2秒

        // 我的课程
        // 获取当前渲染后页面全部结果 字符串
        //$html = $driver->getPageSource();
        //file_put_contents('a.html',$html);

        $elms = $driver->findElement(WebDriverBy::cssSelector('100shuai'));

        sleep(13);
        die();

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

        //切换至最后一个window
        //因为有些网站的链接点击过去带有target="_blank"属性，就新开了一个TAB，而selenium还是定位到老的TAB上，如果要实时定位到新的TAB，则需要调用此方法，切换到最后一个window
        function switchToEndWindow($driver){

            $arr = $driver->getWindowHandles();
            foreach ($arr as $k=>$v){
                if($k == (count($arr)-1)){
                    $driver->switchTo()->window($v);
                }
            }
        }
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
