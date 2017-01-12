<?php
namespace Browser;
use Symfony\Component\Process\Process;

/**
 * CasperJS wrapper
 *
 * installation:
 * 1 - install phantomJS: http://phantomjs.org/download.html
 * 2 - install CasperJS: http://casperjs.org/installation.html
 *
 * @author aguidet
 *
 */
class Casper
{
    private $TAG_CURRENT_URL = '[CURRENT_URL]';
    private $TAG_CURRENT_TITLE = '[CURRENT_TITLE]';
    private $TAG_CURRENT_PAGE_CONTENT = '[CURRENT_PAGE_CONTENT]';
    private $TAG_CURRENT_HTML = '[CURRENT_HTML]';

    private $debug = false;
    private $options = array();
    private $script = '';
    private $output = array();
    private $requestedUrls = array();
    private $currentUrl = '';
    private $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36';
    // default viewport values
    private $viewPortWidth = 1024;
    private $currentPageContent = '';
    private $currentHtml = '';
    private $loadTime = '';
    private $tempDir = '/tmp';
    private $path2casper = '/usr/local/bin/'; //path to CasperJS
    private $onErrorImage;

    const TIMEOUT = 240;

    public function __construct($path2casper = null, $tempDir = null)
    {
        if ($path2casper) {
            $this->path2casper = $path2casper;
        }
        if ($tempDir) {
            $this->tempDir = $tempDir;
        }
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath2Casper($path)
    {
        $this->path2casper = $path;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath2Casper()
    {
        return $this->path2casper;
    }

    /**
     * Set the Headers
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $headersScript = "
casper.page.customHeaders = {
";
        if (!empty($headers)) {
            $headerLines = [];
            foreach ($headers as $header => $value) {
                // Current version of casperjs will not decode gzipped output
                if ($header == 'Accept-Encoding') {
                    continue;
                }
                $headerLine = "    '{$header}': '";
                $headerLine .= (is_array($value)) ? implode(',', $value) : $value;
                $headerLine .= "'";
                $headerLines[] = $headerLine;
            }
            $headersScript .= implode(",\n", $headerLines)."\n";
        }
        $headersScript .= "};";
        $this->_script .= $headersScript;

        return $this;
    }

    /**
     * Set the UserAgent
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * enable debug logging into syslog
     *
     * @param bool $debug
     *
     * @return Casper
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    public function setViewPort($width, $height)
    {
        $this->viewPortWidth = $width;

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.viewport($width, $height);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }


    /**
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * set specific options to casperJS
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param array $output
     *
     * @return Casper
     */
    private function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return array
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * clear the current casper script
     */
    private function clear()
    {
        $this->script = '';
        $this->output = array();
        $this->requestedUrls = array();
        $this->currentUrl = '';
    }

    /**
     * open the specified url
     *
     * @param string $url
     *
     * @return \Browser\Casper
     */
    public function start($url)
    {
        $this->clear();

        $fragment = <<<FRAGMENT
var xpath = require('casper').selectXPath;
var utils = require("utils");
var myObj = {};
var valueFromInputOptions = [];
var casper = require('casper').create({
    verbose: true,
    logLevel: 'debug',
    pageSettings: {
        webSecurityEnabled: false
    }
});
 
casper.options.waitTimeout = 25000;

casper.waitForSelectorText = function(selector, text, then, onTimeout, timeout){
    this.waitForSelector(selector, function _then() {
        this.waitFor(function _check(){
            var content = this.fetchText(selector);
            if (utils.isRegExp(text)) {
                return text.test(content);
            }
            return content.indexOf(text) !== -1;
        }, then, onTimeout, timeout);
    }, onTimeout, timeout);
    return this;
};

casper.on('error', function() {
    var currentDate = new Date();
    var dateTime = currentDate.getDate() + "-"
        + (currentDate.getMonth()+1)  + "-" 
        + currentDate.getFullYear() + "@"  
        + currentDate.getHours() + ":"  
        + currentDate.getMinutes() + ":" 
        + currentDate.getSeconds();

    this.capture("/var/www/var/cache/$this->onErrorImage-" + dateTime + ".png", { top: 0, left:0, width:1200, height: 2500});
});

casper.userAgent('$this->userAgent');
casper.start().then(function() {
    this.open('$url', {
        headers: {
            'Accept': 'text/html'
        }
    });
});

FRAGMENT;

        $this->script = $fragment;

        return $this;
    }

    /**
     * Open URL after the initial opening
     *
     * @param $url
     *
     * @return $this
     */
    public function thenOpen($url)
    {
        $fragment = <<<FRAGMENT
casper.thenOpen('$url');

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * fill the form with the array of data
     * then submit it if submit is true
     *
     * @param string $selector
     * @param array $data
     * @param string|bool $submit
     *
     * @return \Browser\Casper
     */
    public function fillForm($selector, $data = array(), $submit = false)
    {
        $jsonData = json_encode($data);
        $jsonSubmit = ($submit) ? 'true' : 'false';

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.fill('$selector', $jsonData, $jsonSubmit);
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function fillFormSelectors($selector, $data = array(), $submit = false)
    {
        $jsonData = json_encode($data);
        $jsonSubmit = ($submit) ? 'true' : 'false';

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.fillSelectors('$selector', $jsonData, $jsonSubmit);
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * Sends native keyboard events
     * to the element matching the provided selector:
     *
     * @param string $selector
     * @param string $string
     *
     * @param string $function
     * @return Casper
     */
    public function sendKeys($selector, $string = null, $function = '')
    {
        $jsonData = json_encode($string);

        if ($function) {
            $jsonData = $function;
        }

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.sendKeys('$selector', $jsonData);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }


    /**
     * @param $selector
     * @param $string
     * @param string $function
     * @return $this
     */
    public function sendKeysByXpath($selector, $string, $function = '')
    {
        $jsonData = json_encode($string);
        if ($function) {
            $jsonData = $function;
        }

        $fragment = <<<FRAGMENT
casper.then(function () {
    this.sendKeys(xpath('$selector'), $jsonData);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     * @param integer $timeout
     *
     * @return \Browser\Casper
     */
    public function waitForText($text, $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.waitForText(
    '$text',
    function () {
        this.echo('found text "$text"');
    },
    function () {
        this.echo('timeout occured');
    },
    $timeout
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * wait until timeout
     *
     * @param number $timeout
     *
     * @return \Browser\Casper
     */
    public function wait($timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.wait(
    $timeout,
    function () {
        this.echo('timeout occured');
    }
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * wait until the text $text
     * appear on the page
     *
     * @param string $text
     *
     * @return \Browser\Casper
     */
    public function waitForSelector($selector, $function = '', $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.waitForSelector(
    '$selector',
    function () {
        $function
    },
    function () {
        this.echo('timeout occured');
    },
    $timeout
);

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     *
     * @param string $selector
     *
     * @return \Browser\Casper
     */
    public function click($selector)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.click('$selector');
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param string $xpath
     * @return \Browser\Casper $this
     */
    public function clickByXpath($xpath)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.click(xpath('$xpath'));
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;

    }

    /**
     * take a screenshot of the page
     * area containing the selector
     *
     * @param string $selector
     * @param string $filename
     *
     * @return \Browser\Casper
     */
    public function captureSelector($selector, $filename)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.captureSelector('$filename', '$selector');
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }


    /**
     * take a screenshot of the page
     * area defined by
     * array(top left width height)
     *
     * @param array $area
     * @param string $filename
     *
     * @return \Browser\Casper
     */
    public function capture(array $area, $filename)
    {
        $top = $area['top'];
        $left = $area['left'];
        $width = $area['width'];
        $height = $area['height'];

        $fragment = <<<FRAGMENT
casper.then(function() {
    this.capture('$filename', {
        top: $top,
        left: $left,
        width: $width,
        height: $height
    });
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * take a screenshot of the whole page
     * area defined by viewport width
     * and rendered height
     *
     * @param string $filename
     *
     * @return \Browser\Casper
     */
    public function capturePage($filename)
    {

        $fragment = <<<FRAGMENT
casper.on('load.finished', function() {
    this.capture('$filename', {
        top: 0,
        left: 0,
        width: $this->viewPortWidth,
        height: this.evaluate(function() {
        return __utils__.getDocumentHeight();
        }),
    });
});
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * switch to the child frame number $id
     *
     * @param string $id
     *
     * @return \Browser\Casper
     */
    public function switchToChildFrame($id)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToChildFrame($id);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * get back to parent frame
     *
     * @return \Browser\Casper
     */
    public function switchToParentFrame()
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    this.page.switchToParentFrame();
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * wait for selector text
     *
     * @param $selector
     * @param $text
     * @param $then
     * @param $onTimeout
     * @param int $timeout
     * @return Casper
     */
    public function waitForSelectorTextByXpath($selector, $text, $then = '', $onTimeout = '', $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
    casper.waitForSelectorText(xpath('$selector'), "$text", function() { $then }, function() { $onTimeout }, $timeout);
FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    /**
     * @param $function
     * @return $this
     */
    public function then($function)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
        $function
    });

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function evaluate($function)
    {
        $fragment = <<<FRAGMENT
casper.then(function() {
    casper.evaluate(function() {
        $function
    });
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function waitForUrl($url, $then = '', $onTimeout = '', $timeout = 5000)
    {
        $fragment = <<<FRAGMENT
casper.waitForUrl($url, $then, $onTimeout, $timeout);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function waitWhileVisibleByXpath($selector)
    {
        $fragment = <<<FRAGMENT
    casper.waitWhileVisible(xpath('$selector'));
FRAGMENT;
        $this->script .= $fragment;

        return $this;
    }

    public function waitFor($function)
    {
        $fragment = <<<FRAGMENT
    casper.waitFor($function);
FRAGMENT;
        $this->script .= $fragment;

        return $this;
    }


    public function run()
    {
        $fragment = <<<FRAGMENT
casper.then(function () {
    this.echo('$this->TAG_CURRENT_URL' + this.getCurrentUrl());
    this.echo('$this->TAG_CURRENT_TITLE' + this.getTitle());
});
casper.run();

FRAGMENT;

        $this->script .= $fragment;
        $filename = tempnam($this->tempDir, 'php-casperjs-');

        file_put_contents($filename, $this->script);

        // options parsing
        $options = '';
        foreach ($this->options as $option => $value) {
            $options .= ' --' . $option . '=' . $value;
        }

        $process = new Process($this->path2casper . 'casperjs ' . $filename . $options);
        $process->setTimeout(self::TIMEOUT);

        if ($this->isDebug()) {
            $process->run(function($type, $buffer) {
                if (Process::ERR == $type) {
                    echo 'ERR >'. $buffer;
                } else {
                    echo 'OUT >'. $buffer;
                }
            });
        } else {
            $process->run();
        }

        $this->setOutput(explode("\n", $process->getOutput()));
        $this->processOutput();

        unlink($filename);
        return $process;
    }

    /**
     * process the output after navigation
     * and fill the differents attributes for
     * later usage
     */
    private function processOutput()
    {
        foreach ($this->getOutput() as $outputLine) {
            if (strpos($outputLine, $this->TAG_CURRENT_URL) !== false) {
                $this->currentUrl = str_replace(
                    $this->TAG_CURRENT_URL,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, "Navigation requested: url=") !== false) {
                $frag0 = explode('Navigation requested: url=', $outputLine);
                $frag1 = explode(', type=', $frag0[1]);
                $this->requestedUrls[] = $frag1[0];
            }

            if ($this->isDebug()) {
                syslog(LOG_INFO, '[PHP-CASPERJS] ' . $outputLine);
            }
            if (strpos(
                    $outputLine,
                    $this->TAG_CURRENT_PAGE_CONTENT
                ) !== false
            ) {
                $this->currentPageContent = str_replace(
                    $this->TAG_CURRENT_PAGE_CONTENT,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, $this->TAG_CURRENT_HTML) !== false) {
                $this->currentHtml = str_replace(
                    $this->TAG_CURRENT_HTML,
                    '',
                    $outputLine
                );
            }

            if (strpos($outputLine, " steps in ") !== false) {
                $frag = explode(' steps in ', $outputLine);
                $this->loadTime = $frag[1];
            }
        }
    }

    public function withFrame($selector)
    {
        $fragment = <<<FRAGMENT
casper.then(function () {
    var link_tmp = this.getElementInfo('$selector');
    var link = link_tmp.attributes.src;
    casper.open(link);
});

FRAGMENT;

        $this->script .= $fragment;

        return $this;
    }

    public function clearInputByXpath($selector)
    {
        $fragment = <<<FRAGMENT
casper.then(function () {
    casper.sendKeys(xpath('$selector'), "", {reset: true} );
});

FRAGMENT;
        $this->script .= $fragment;

        return $this;
    }

    public function ifCheckboxFalseCheckItXpath($selector)
    {
        $fragment = <<<FRAGMENT

casper.then(function () {
    var checkboxElement = casper.getElementsInfo(xpath('$selector'))[0];
    
    if (false == checkboxElement.attributes.hasOwnProperty('checked')) {
        this.click(xpath('$selector'));
    }
});

FRAGMENT;
        $this->casperBrowser->then($fragment);

        return $this;
    }

    public function getCurrentUrl()
    {
        return $this->currentUrl;
    }

    public function getRequestedUrls()
    {
        return $this->requestedUrls;
    }

    public function getCurrentPageContent()
    {
        return $this->currentPageContent;
    }

    public function getHTML()
    {
        return $this->currentHtml;
    }

    public function getLoadTime()
    {
        return $this->loadTime;
    }

    public function setOnErrorImage($imageName)
    {
        $this->onErrorImage = $imageName;
    }
}
