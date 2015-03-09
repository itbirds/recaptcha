<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          https://developers.google.com/recaptcha/docs/php
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * @copyright Copyright (c) 2014, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace ReCaptcha;

/**
 * Test the Post Request, mocking out the actual request
 */
class PostRequestTest extends \PHPUnit_Framework_TestCase
{
    public static $assert = null;
    protected $parameters = null;
    protected $runcount = 0;

    public function setUp() {
        $this->parameters = new RequestParameters("secret", "response", "remoteip", "version");
    }

    public function tearDown() {
        self::$assert = null;
    }

    public function testSSLContextOptions() {
        $req = new PostRequest();
        self::$assert = array($this, "sslContextOptionsCallback");
        $req->submit($this->parameters);
        $this->assertEquals(1, $this->runcount, "The assertion was ran");
    }

    public function sslContextOptionsCallback(array $args) {
        $this->runcount++;
        $this->assertCount(3, $args);
        $this->assertStringStartsWith("https://www.google.com/", $args[0]);
        $this->assertFalse($args[1]);
        $this->assertTrue(is_resource($args[2]), "The context options should be a resource");

        $options = stream_context_get_options($args[2]);
        $this->assertArrayHasKey('http', $options);
        $this->assertArrayHasKey('verify_peer', $options['http']);
        $this->assertTrue($options['http']['verify_peer']);

        $key = version_compare(PHP_VERSION, "5.6.0", "<") ? "CN_name" : "peer_name";

        $this->assertArrayHasKey($key, $options['http']);
        $this->assertEquals("www.google.com", $options['http'][$key]);

    }

}

function file_get_contents() {
    if (PostRequestTest::$assert) {
        $cb = PostRequestTest::$assert;
        return $cb(func_get_args());
    }
    // Since we can't represent maxlen in userland...
    return call_user_func_array("file_get_contents", func_get_args());
}