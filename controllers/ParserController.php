<?php

namespace app\controllers;

use Yii;
use app\models\MerchantProduct;
use app\models\PreviewForm;

use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\UploadedFile;

class ParserController extends Controller
{
    /**
     * Возвращает список всех товаров.
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => MerchantProduct::find()
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }


    /**
     * Обрабатывает загрузку файла и возвращает его предпросмотр.
     */
    public function actionPreview()
    {
        $model = new PreviewForm(['scenario' => PreviewForm::SCENARIO_PREVIEW]);
        $attributes = MerchantProduct::attributeLabels();

        if (!Yii::$app->request->isPost) {
            return $this->render('previewForm', ['model' => $model]);
        }

        $model->load(Yii::$app->request->post());
        $model->file = UploadedFile::getInstance($model, 'file');

        if (!$model->upload()) {
            return $this->render('error', ['errors' => $model->errors]);
        }
        
        return $this->render('previewGrid', [
            'model' => $model,
            'attributes' => $attributes,
        ]);
    }


    /**
     * Импортирует файл в БД.
     */
    public function actionImport()
    {
        $model = new PreviewForm(['scenario' => PreviewForm::SCENARIO_IMPORT]);
        $model->load(Yii::$app->request->post());
        if ($model->validate() && $model->import()) {
            $this->redirect(['/parser']);
        }

        return $this->render('error', ['errors' => $model->errors]);
    }
}
