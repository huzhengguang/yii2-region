<?php
namespace lito\region;

use kartik\select2\Select2;
use yii\base\InvalidParamException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class Region extends Widget
{
    public $model = null;

    /**
     * @var string 此属性不用处理
     */
    public $attribute;

    /**
     * @var array 省份配置
     */
    public $province = [];

    /**
     * @var array 城市配置
     */
    public $city = [];

    /**
     * @var array 县/区配置
     */
    public $district = [];

    /**
     * @var mixed 数据源
     */
    public $url;

    public function init()
    {
        if (!$this->model) {
            throw new InvalidParamException('model不能为null!');
        }

        if (empty($this->province) || empty($this->city)) {
            throw new InvalidParamException('province和city不能为空！');
        }

        $cityId = Html::getInputId($this->model, $this->city['attribute']);
        if (empty($this->city['options']['prompt'])) {
            $this->city['options']['prompt'] = '选择城市';
        }
        $cityDefault = Html::renderSelectOptions('city', ['' => $this->city['options']['prompt']]);

        $joinChar = strripos($this->url, '?') ? '&' : '?';
        $url = $this->url . $joinChar;

        if (!empty($this->district)) {
            if (empty($this->district['options']['prompt'])) {
                $this->district['options']['prompt'] = '选择县/区';
            }
            $districtId = Html::getInputId($this->model, $this->district['attribute']);
            $districtDefault = Html::renderSelectOptions('district', ['' => $this->district['options']['prompt']]);
            $this->city['options'] = ArrayHelper::merge($this->city['options'], [
                'onchange' => "
                    if($(this).val() != ''){
                        $.get('{$url}parent_id='+$(this).val(), function(data) {
                            $('#{$districtId}').html('{$districtDefault}'+data);
                        })
                    }else{
                        $('#{$districtId}').html('{$districtDefault}');
                    }
                "
            ]);
        }

        $this->province['options'] = ArrayHelper::merge($this->province['options'], [
            'onchange' => "
                if($(this).val()!=''){
                    $.get('{$url}parent_id='+$(this).val(), function(data) {
                        $('#{$cityId}').html('{$cityDefault}'+data);
                    })
                }else{
                    $('#{$cityId}').html('{$cityDefault}');
                }
                $('#{$districtId}').html('{$districtDefault}');
            "
        ]);

    }

    public function run()
    {
        $output[] = Html::tag('span', Select2::widget([
            'model' => $this->model,
            'attribute' => $this->province['attribute'],
            'data' => $this->province['items'],
            'options' =>$this->province['options'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]),['class'=>'input-group-btn']);

        $output[] = Html::tag('span', Select2::widget([
            'model' => $this->model,
            'attribute' => $this->city['attribute'],
            'data' => $this->city['items'],
            'options' =>$this->city['options'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]),['class'=>'input-group-btn']);
        if (!empty($this->district)) {

            $output[] = Html::tag('span', Select2::widget([
                'model' => $this->model,
                'attribute' => $this->district['attribute'],
                'data' => $this->district['items'],
                'options' =>$this->district['options'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
                'pluginEvents'=>[
                    'change'=>isset($this->district['event'])?$this->district['event']:''
                ]
            ]),['class'=>'input-group-btn']);
        }
        return @implode("\n", $output);
    }

}
