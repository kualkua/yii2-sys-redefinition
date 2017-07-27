<?php
/**
 * Created by IntelliJ IDEA.
 * User: wir_wolf
 * Date: 22.09.15
 * Time: 15:17
 */

namespace Redefinitions\Components\Web;

/**
 * Class Response
 * @package Redefinitions\Components\Web
 */
class Response extends \yii\web\Response
{
    /**
     * @param Response $response
     * @param array $headers
     * @return Response
     * @throws \Exception
     */
    public static function prepareHeaders(Response $response, array $headers) {
        if (count($headers) < 1) {
            throw new \Exception('No headers provided');
        }
        foreach ($headers as $k => $v) {
            $response->headers->set($k, $v);
        }
        return $response;
    }
}