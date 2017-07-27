<?php
/**
 * Created by Chr0non.
 * Author: Artem Grebenik
 * E-mail: chronon314[dog]gmail[dot]com
 * Date: 02.10.15
 * Time: 12:16
 */

namespace Redefinitions\Components\Web;

use yii\helpers\ArrayHelper;

/**
 * Class Request
 * @package Redefinitions\Components\Web
 *
 * @property array $data depracated!
 */
class Request extends \yii\web\Request
{

    /**
     * @var array
     */
    private $requestData = null;

    /**
     * @deprecated this method removed in next realise
     * @return array
     */
    public function getData() {
        if ($this->requestData === null) {
            $this->requestData = ArrayHelper::merge(\Yii::$app->request->getBodyParams(), \Yii::$app->request->get());
        }
        return $this->requestData;
    }
}