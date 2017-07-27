<?php
/**
 * Created by IntelliJ IDEA.
 * User: wir_wolf
 * Date: 22.09.15
 * Time: 15:25
 */

namespace Redefinitions\Components\Web;

use Yii;
use yii\base\Action;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * Class Controller
 * @package app\System\Components
 */
class Controller extends \yii\rest\Controller
{

    /**
     * @var Request $request
     */
    protected $request;

    /**
     * @var Response $response
     */
    protected $response;

    protected $responseFormat = Response::FORMAT_JSON;

    /**
     * @param Action $action
     * @return bool
     */
    public function beforeAction($action) {
        $this->request = &Yii::$app->request;
        $this->response = &Yii::$app->response;
        $this->response->format = $this->responseFormat;
        if ($this->request->getIsOptions() and $this->action->id !== 'options') {
            $this->runAction('options');
            return false;
        } else {
            return parent::beforeAction($action);
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {

        if (!isset(Yii::$app->params['globalBehaviors'])) {
            throw new InvalidParamException('Property globalBehaviors not found in global params');
        }

        $behaviors = parent::behaviors();

        $behaviors = ArrayHelper::merge($behaviors, Yii::$app->params['globalBehaviors']);
        if (isset($behaviors['verbFilter'])) {
            $behaviors['verbFilter']['actions'] = ArrayHelper::merge($behaviors['verbFilter']['actions'], $this->verbs());
        }
        return $behaviors;
    }

    /**
     * @return array
     */
    public function actions() {
        $actions = parent::actions();
        $actions = ArrayHelper::merge($actions, [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ]);
        return $actions;
    }

    /**
     * @param Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result) {
        if ($this->action->id == 'options') {
            Yii::$app->getResponse()->setStatusCode(204);
        }
        return parent::afterAction($action, $result);
    }
}