<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\LoginForm;
use common\models\SignupForm;
use yii\web\Response;
use yii\base\DynamicModel;

/**
 * Site controller
 */
class UserController extends \yii\rest\ActiveController {

	public $modelClass = 'common\models\User';
	public $response = ['status' => 200, 'msg' => ''];

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;
		return $behaviors;
	}

	public function afterAction($action, $result) {
		Yii::$app->response->format = 'json';
		Yii::$app->response->setStatusCode($action->controller->response['status']);
		if($this->action->id == 'index') {
			$result = parent::afterAction($action, $result);
			$data = $this->serializeData($result);

			foreach($data as $key => $item){
				if(isset($item['_id']) && is_array($item['_id'])) {
					$data[$key]['_id'] = (string) $item['_id']['$id'];
				}
			}
		  return $data;
	   }elseif($this->action->id == 'view' || $this->action->id == 'create' || $this->action->id == 'update') {
		  $result = parent::afterAction($action, $result);
		  $data = $this->serializeData($result);
		  if(isset($data['_id']) && is_array($data['_id'])) {
			  $data['_id'] = (string) $data['_id']['$id'];
		  }
		  return $data;
	   }else {
		  $result = parent::afterAction($action, $result);
		  return $this->serializeData($result);
	   }
		//return parent::afterAction($action, $result);
	}

	public function actionLogin() {
		$model = new LoginForm();
		//$user = new common\models\User;
		$model->attributes = Yii::$app->request->post();
		if ($model->login()) {
			$this->response['msg'] = 'login successful';
			$this->response['status'] = 200;
			//$this->response['data'] = array_filter($model->attributes);
			$this->response['data'] = $model->toJson();
		} else {
			$this->response['msg'] = 'login faild';
			$this->response['status'] = 401;
			$this->response['errors'] = $model->errors;
		}
		return $this->response;
	}

	public function actionRegister() {
		$checkUser = User::find()->where([
			'or' , 
			['google_id' => Yii::$app->request->post('password')],
			['email' => Yii::$app->request->post('email')]
		])->all();

		if (count($checkUser) > 1) {
			$this->response['status'] = 400;
			$this->response['msg'] = 'Incorrect registration info!';
			return $this->response;
		}

		if (count($checkUser) == 1) {
			$checkUser[0]->email = Yii::$app->request->post('email');
			$checkUser[0]->username = Yii::$app->request->post('email');
			$checkUser[0]->first_name = Yii::$app->request->post('first_name');
			$checkUser[0]->last_name = Yii::$app->request->post('last_name');
			$checkUser[0]->save();

			$this->response['status'] = 200;
			$this->response['msg'] = 'Registration successful';
			$this->response['data'] = $checkUser[0]->toJson();
			return $this->response;
		}

		$model = new SignupForm();
		$model->attributes = Yii::$app->request->post();
		if ($user = $model->signup()) {
			if (Yii::$app->getUser()->login($user)) {
				$data['email'] = $model->email;
				$data['first_name'] = $model->first_name;
				$data['last_name'] = $model->last_name;

				$this->response['status'] = 200;
				$this->response['msg'] = 'Registration successful';
				//$this->response['data'] = array_filter($model->attributes);
				$this->response['data'] = $user->toJson();
			}
		} else {
			$this->response['status'] = 400;
			$this->response['msg'] = 'Registration failed';
			$this->response['errors'] = $model->errors;
		}

		return $this->response;
	}
}
