<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>

<div class="form">

    <?php $form = ActiveForm::begin([
        'id' => 'preview-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

        <?= $form->field($model, 'file')->fileInput() ?>

        <?= $form->field($model, 'encoding')->dropDownList($model->encodings, ['prompt' => "Выберите кодировку"]); ?>

        <?= $form->field($model, 'delimiter')->dropDownList($model->delimiters, ['prompt' => "Выберите разделитель"]); ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('Предпросмотр', ['class' => 'btn btn-primary', 'name' => 'preview-button']) ?>
            </div>
        </div>

    <?php ActiveForm::end(); ?>
</div>
