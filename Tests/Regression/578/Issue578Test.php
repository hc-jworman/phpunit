<?php
class Issue578Test extends PHPUnit_Framework_TestCase
{
    public function testNoticesDoublePrintStackTrace()
    {
        try {
            $this->iniSet('error_reporting', E_ALL | E_NOTICE);
            trigger_error('Stack Trace Test Notice', E_NOTICE);
        } catch (\ValueError $e) {
            \trigger_error('Invalid error type specified');
        }
    }

    public function testWarningsDoublePrintStackTrace()
    {
        try {
            $this->iniSet('error_reporting', E_ALL | E_NOTICE);
            trigger_error('Stack Trace Test Notice', E_WARNING);
        } catch (\ValueError $e) {
            \trigger_error('Invalid error type specified');
        }
    }

    public function testUnexpectedExceptionsPrintsCorrectly()
    {
        throw new Exception('Double printed exception');
    }
}
