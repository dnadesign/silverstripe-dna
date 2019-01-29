<?php

namespace DNA\DNARaygun;

use SilverStripe\Raygun\RaygunHandler as SSRaygunHandler;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Factory;
use Raygun4php\RaygunClient;
use SilverStripe\Control\Director;


class RaygunHandler extends SSRaygunHandler {

    use Configurable;

    private static $app_name = 'DEFAULT';
    /**
     * @param array $record
     * @param array $tags
     * @param array $customData
     * @param int|float $timestamp
     */
    protected function writeError(array $record, array $tags = array(), array $customData = array(), $timestamp = null)
    {

        if (Director::isDev()) {
            return false;
        }

        try {
            $message = sprintf("[%s] %s",
                self::config()->app_name,
                $record['message']
            );

            $context = $record['context'];

            $this->client->SendError(
                0,
                $message,
                $context['file'],
                $context['line'],
                $tags,
                $customData,
                $timestamp
            );
        } catch (Exception $e) {
            // raygun exception.
        }
    }

    /**
     * @param array $record
     * @param array $tags
     * @param array $customData
     * @param int|float $timestamp
     */
    protected function writeException(array $record, array $tags = array(), array $customData = array(), $timestamp = null)
    {
        if (Director::isDev()) {
            return false;
        }

        try {
            $exception = $record['context']['exception'];

            $message = array();
            $message['errstr'] = $exception->getMessage();
            $message['errno'] = $exception->getCode();
            $message['errcontext'] = $exception->getTrace();
            $message['errfile'] = $exception->getFile();
            $message['errline'] = $exception->getLine();
            $message['errstr'] = sprintf("[%s] %s",
                self::config()->app_name,
                $message['errstr']
            );

            $reportedException = new ReportedException($message);

            $this->client->SendException($reportedException, $tags, $customData, $timestamp);
        } catch (Exception $e) {
            // raygun exception
        }
    }

}
