<?php
/**
 * Created by IntelliJ IDEA.
 * User: wir_wolf
 * Date: 13.12.16
 * Time: 15:49
 */

namespace Redefinitions\Components\Web;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

/**
 * Class Action
 * @package Redefinitions\Components\Web
 */
class Action extends \yii\base\Action
{

    /**
     * @var \yii\base\Model
     */
    protected $modelClass = null;

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller.
     *
     * @param array $params the parameters to be bound to the action's run() method.
     * @return mixed the result of the action
     * @throws InvalidConfigException if the action class does not have a run() method
     * @throws UserException
     */
    public function runWithParams($params) {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        $args = $this->controller->bindActionParams($this, $params);
        Yii::trace('Running action: ' . get_class($this) . '::run()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }
        $status = $this->validateData(ArrayHelper::getValue($params, 'formName', ''));
        if ($status !== true) {
            return $this->sendError($status);
        }
        try {
            $result = $this->beforeRun();
            if ($result !== true) {
                return $this->sendError($result);
            }
            $result = call_user_func_array([
                $this,
                'run'
            ], $args);
            return $this->afterRun($result);
        } catch (UserException $e) {
            $res = $this->sendErrorException($e);
            if ($res != false) {
                return $res;
            }
            throw $e;
        }
    }


    /**
     * @param $formName
     * @return bool|string
     */
    public function validateData($formName) {
        if ($this->modelClass !== null and is_string($this->modelClass)) {
            $this->modelClass = new $this->modelClass();
            $this->modelClass->load(Yii::$app->request->data, $formName);
            if (!$this->modelClass->validate()) {
                return $this->validationError();
            }
            return true;
        }
        return true;

    }


    public function sendErrorException(UserException $e) {
        unset($e);
        return false;
    }

    public function sendError($data) {
        $request = Yii::$app->getResponse();
        $request->setStatusCode('406', 'Not Acceptable');
        $request->data = $data;
        return $request;
    }

    /**
     * @return string
     */
    public function validationError() {
        return 'Validate input data error';
    }

    /**
     * This method is called right before `run()` is executed.
     * You may override this method to do preparation work for the action run.
     * If the method returns false, it will cancel the action.
     *
     * @return boolean whether to run the action.
     */
    protected function beforeRun() {
        return true;
    }

    /**
     * This method is called right after `run()` is executed.
     * You may override this method to do post-processing work for the action run.
     */
    protected function afterRun($result) {
        return $result;
    }
}