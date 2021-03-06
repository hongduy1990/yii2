<?php
namespace backend\controllers;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\AdminLoginForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Inflector;
/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index','getall','getall2'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            /*
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post','GET'], // thêm GET để logout hoặc tắt comment
                ],
            ],
            */

        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionGetall()
    {
        $controllers = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@app/controllers'), ['recursive' => true]);
        $actions = [];
        foreach ($controllers as $controller) {
            $contents = file_get_contents($controller);
            $controllerId = Inflector::camel2id(substr(basename($controller), 0, -14));
            preg_match_all('/public function action(\w+?)\(/', $contents, $result);
            foreach ($result[1] as $action) {
                if($action!=='s'){
                    $actionId = Inflector::camel2id($action);
                    $route = $controllerId . '-' . $actionId;
                    //$route =$actionId.'-'.$controllerId;
                    $actions[$route] = array($route,Inflector::camel2words($route));
                }
            }
        }
        asort($actions);
        echo "<pre>";
        print_r($actions);
    }
    public function actionGetall2() //actionGetcontrollersandactions
    {
        $controllerlist = [];
        if ($handle = opendir('../controllers')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && substr($file, strrpos($file, '.') - 10) == 'Controller.php') {
                    $controllerlist[] = $file;
                }
            }
            closedir($handle);
        }
        asort($controllerlist);
        $fulllist = [];
        foreach ($controllerlist as $controller):
            $handle = fopen('../controllers/' . $controller, "r");
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    if (preg_match('/public function action(.*?)\(/', $line, $display)):
                        if (strlen($display[1]) > 2):
                            $fulllist[substr($controller, 0, -4)][] = strtolower($display[1]);
                        endif;
                    endif;
                }
            }
            fclose($handle);
        endforeach;
        echo "<pre>";
        print_r($fulllist);
    }

    public function actionLogin()
    {
        $this->layout =FALSE;
        if (!Yii::$app->user->isGuest) {
            //return $this->goHome();
            return $this->redirect(['orders/index']);
        }
        $model = new AdminLoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(['default/index']);
            //return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
