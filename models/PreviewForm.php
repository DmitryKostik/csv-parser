<?php

namespace app\models;

use SplFileObject;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

class PreviewForm extends Model
{
    /**
     * @var UploadedFile $file Excel or CSV file.
     */
    public $file;

    /**
     * @var string Путь к файлу.
     */
    public $filePath;

    /**
     * @var string Кодировка файла.
     */
    public $encoding = 3;

    /**
     * @var string Разделитель.
     */
    public $delimiter = 1;

    /**
     * @var int Количество строк при предпросмотре.
     */
    public $previewCount = 10;

    /**
     * @var array Соответствие колонок CSV полям таблицы.
     */
    public $columnMap;
    
    /** @var int Количество столбцов в переданном файле */
    protected $_columnsCount;

    /** @var string Сценарий предпросмотра */
    const SCENARIO_PREVIEW = 'preview';

    /** @var string Сценарий импорта файла */
    const SCENARIO_IMPORT = 'import';


    /**
     * Правила проверки модели.
     */
    public function rules()
    {
        return [
            ['file', 'file', 'skipOnEmpty' => false, 
            'extensions' => ['csv'], 'checkExtensionByMimeType' => false,
            'wrongExtension' => 'Доступный формат файла .csv',
            'message' => 'Выберите файл',
            'uploadRequired' => 'Выберите файл'],

            ['encoding', 'in', 'range' => array_keys($this->getEncodings())],
            ['delimiter', 'in', 'range' => array_keys($this->getDelimiters())],

            ['columnMap', 'validateMapColumns'],

            ['filePath', function ($attribute, $params) {
                if (!file_exists($this->$attribute)) {
                    $this->addError($attribute, 'Файл не найден');
                }
            }],
        ];
    }


    /**
     * Возможные сценарии модели.
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_PREVIEW => ['encoding', 'delimiter', 'file'],
            self::SCENARIO_IMPORT => ['encoding', 'delimiter', 'columnMap', 'filePath'],
        ];
    }



    /**
     * Метки аттрибутов
     */
    public function attributeLabels() : array
    {
        return [
            'file' => 'Файл для импорта',
            'encoding' => 'Кодировка',
            'delimiter' => 'Разделитель',
        ];
    }


    /**
     * Разделители доступные для разбора.
     */
    public function getDelimiters() : array
    {
        return [
            ';',
            ','
        ];
    }


    /**
     * Возвращает разделитель в строковом формате.
     * @return string|null
     */
    public function getDelimiter()
    {
        return $this->getDelimiters()[$this->delimiter] ?? null;
    }


    /**
     * Кодировки доступные для разбора
     */
    public function getEncodings()
    {
        return [
            'Unicode',
            'Windows-1251',
            'ASCII',
            'UTF-8',
        ];
    }


    /**
     * Возвращает кодировку в строковом формате.
     * @return string|null если такого ключа не существует.
     */
    public function getEncoding()
    {
        return $this->getEncodings()[$this->encoding] ?? null;
    }


    /**
     * Геттер для количества столбцов.
     * @return int|null если количество столбцов не было посчитано.
     */
    public function getColumnsCount()
    {
        return $this->_columnsCount;
    }

    
    /**
     * Сохраняет переданный на предпросмотр файл.
     * @return bool.
     */
    public function upload() : bool
    {
        if ($this->validate()) {
            $path = 'uploads/' . $this->file->baseName . time() . '.' . $this->file->extension;
            $path = Yii::getAlias('@webroot/') . $path;
            if ($this->file->saveAs($path)) {
                $this->filePath = $path;
                return true;
            }
            return false;
        } else {
            return false;
        }
    }


    /**
     * Разбирает CSV - файл и возвращает массив для предпросмотра.
     * @return array Массив для предпросмотра.
     */
    public function getPreview() : array
    {
        $fileObject = new SplFileObject($this->filePath);
        $totalCount = $this->previewCount <= $this->getTotalCount($fileObject)
            ? $this->previewCount - 1
            : $this->getTotalCount($fileObject) - 1;

        for ($count = 0; $count < $totalCount; ++$count) {
            $data = $fileObject->fgetcsv($this->getDelimiter());

            $models[] = mb_convert_encoding($data, "UTF-8", $this->getEncoding());
            $fileObject->next();

            if ($count == 0) {
                $this->_columnsCount = count($models[0]);
            }
        }

        return $models;
    }


    /**
     * Возвращает общее количество записей в CSV - файле.
     */
    public function getTotalCount($fileObject) : int
    {
        $count = 0;
 
        while (!$fileObject->eof()) {
            $fileObject->next();
            $fileObject->current();
            ++$count;
        }

        $fileObject->rewind();
 
        return $count;
    }


    /**
     * Построчно читает CSV файл и сохраняет модели.
     */
    public function import()
    {
        $fileObject = new SplFileObject($this->filePath);

        $totalCount = $this->getTotalCount($fileObject) - 1;

        $aviableKeys = array_keys($this->columnMap);

        for ($count = 0; $count < $totalCount; ++$count) {
            $data = $fileObject->fgetcsv($this->getDelimiter());
            $data = mb_convert_encoding($data, "UTF-8", $this->getEncoding());

            foreach ($data as $key => $value) {
                if (in_array($key, $aviableKeys)) {
                    $data[$this->columnMap[$key]] = $value;
                }

                unset($data[$key]);
            }

            $models[] = $data;

            $fileObject->next();

            if ($count % 1000 == 0 && $count != 0) {
                $this->batchInsert($models);
                $models = [];
            }
        }

        $this->batchInsert($models);

        unlink($this->filePath);
        return true;
    }


    /**
     * Проверяет карту столбцов на корректность.
     * - Что это массив.
     * - Что выбрано больше одного столбца.
     * - Что столбцы не повторяются.
     * @return bool
     */
    public function validateMapColumns() : bool
    {
        if (!is_array($this->columnMap)) {
            $this->addError('columnMap', 'Карта столбцов не массив');
            return false;
        }

        $this->clearEmptyMapColumns();

        if (count($this->columnMap) < 1) {
            $this->addError('columnMap', 'Выберите хотя бы один столбец');
            return false;
        }

        if ($this->isMapColumnsHasDuplicates()) {
            $this->addError('columnMap', 'Столбцы не должны повторятся');
            return false;
        }

        return true;
    }


    /**
     * Удаляет пустые значения из карты столбцов.
     */
    public function clearEmptyMapColumns() : void
    {
        $validColumns = array_keys(MerchantProduct::attributeLabels());
        foreach ($this->columnMap as $key => $value) {
            if (!in_array($value, $validColumns)) {
                unset($this->columnMap[$key]);
            }
        }
    }


    /**
     * Проверяет есть ли дубликаты в карте столбцов.
     * @return bool
     */
    public function isMapColumnsHasDuplicates() : bool
    {
        $temp = [];
        foreach ($this->columnMap as $value) {
            $temp[$value] = true;
        }

        return count($temp) != count($this->columnMap);
    }


    /**
     * Сохраняет переданный массив моделей
     * @param array $models Массив моделей.
     * @return void
     */
    public function batchInsert(array $models) : void
    {
        foreach ($models as $model) {
            $merchProduct = new MerchantProduct;
            $merchProduct->setAttributes($model);
            $merchProduct->save();
        }
    }
}