<?php
/**
 * Created by wir_wolf.
 * User: Andru Cherny
 * Date: 15.03.17
 * Time: 15:57
 */

namespace Redefinitions\Components\Web;

use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\RequestParserInterface;

/**
 *
 * To enable parsing for JSON requests you can configure [[Request::parsers]] using this class:
 *
 * ```php
 * 'request' => [
 *     'parsers' => [
 *         'application/xml' => 'Redefinitions\Components\Web\XmlParser',
 *         'text/xml' => 'Redefinitions\Components\Web\XmlParser',
 *     ]
 * ]
 * ```
 *
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class XmlParser implements RequestParserInterface
{

    /**
     * If parser result as array, this is default,
     * if you want to get object, set it to false.
     *
     * @var bool
     */
    public $asArray = true;

    /**
     * Whether throw the [[BadRequestHttpException]] if the process error.
     *
     * @var bool
     */
    public $throwException = true;

    /**
     * {@inheritdoc}
     */
    public function parse($rawBody, $contentType) {
        libxml_use_internal_errors(true);
        $result = simplexml_load_string($rawBody, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($result === false) {
            $errors = libxml_get_errors();
            $latestError = array_pop($errors);
            $error = [
                'message' => $latestError->message,
                'type'    => $latestError->level,
                'code'    => $latestError->code,
                'file'    => $latestError->file,
                'line'    => $latestError->line,
            ];
            if ($this->throwException) {
                throw new BadRequestHttpException($latestError->message);
            }
            return $error;
        }
        if (!$this->asArray) {
            return $result;
        }
        return Json::decode(Json::encode($result), $this->asArray);
    }
}
