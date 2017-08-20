<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

/**
 * Class VerifyHaveTrailerForVideoTest
 *
 * Как и требовалось, сделал минимум ожиданий.
 */
class VerifyHaveTrailerForVideoTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RemoteWebDriver
     */
    static $webDriver;

    public function setUp()
    {
        $capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        self::$webDriver = RemoteWebDriver::create('http://localhost:4444/wd/hub', $capabilities);
    }

    #Проверка на наличие трейлера у страницы
    public function testVerifyHaveTrailerForVideo()
    {
        #Входные значения
        $searchQuery = 'ураган';
        $searchResultPosition = 1;
        $searchResultsBlocXpath = "//div[@class='serp-list serp-list_type_search serp-controller__list" .
            " counter__reqid clearfix serp-controller__list serp-controller__list_type_search" .
            " i-bem serp-list_js_inited']/div[$searchResultPosition]" .
            "//img[@class='thumb-image__image thumb-preview__target']";

        #Переходим на яндекс
        self::$webDriver->get('https://yandex.ru/video/');
        #Находим и вводим в строку поиска необходимое значение
        $searchField = self::$webDriver->findElement(WebDriverBy::xpath("//table[@class='input__box-layout']//input"));
        $searchField->sendKeys($searchQuery);
        $searchField->submit();
        #Ожидаем ответа
        self::waitForAjax(10);
        #Находим и наводим на то видео, которое интересует
        $searchResultsBloc = self::$webDriver->findElement(WebDriverBy::xpath($searchResultsBlocXpath));
        #Запоминаем какая картинка была
        $imageSrc = $searchResultsBloc->getAttribute('src');
        self::$webDriver->getMouse()->mouseMove( $searchResultsBloc->getCoordinates());
        #Ждем когда картинка сменится
        self::$webDriver->wait(5, 200)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated(
                WebDriverBy::xpath($searchResultsBlocXpath . "[@src='$imageSrc']")));
    }

    public function tearDown()
    {
        self::$webDriver->quit();
    }

    #Функция для ожидания завершения ajax
    public static function waitForAjax($timeout = 5, $interval = 200)
    {
        self::$webDriver->wait($timeout, $interval)->until(function() {
            $condition = 'return ($.active == 0);';
            return self::$webDriver->executeScript($condition);
        });
    }

}