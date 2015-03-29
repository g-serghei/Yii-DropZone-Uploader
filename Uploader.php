<?php

/*
Author: Grigoruta Serghei
Vesion: 1.0.0
DropZone Documentation: http://www.dropzonejs.com
*/

class Uploader extends CWidget {

    public $cssClass = null;
    public $url = null;
    public $events = array();
    
    public $model = null;
    public $attribute = null;

    
	private $options = array();
	private $assetsUrl = null;
    private static $zone_id = 0;


	public function init() {
		parent::init();
        self::$zone_id++;
        $this->assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('ext.dropzone.assets'));
	}

	public function run() {
        $id = 'dropzone' . self::$zone_id;
        
		$cs = Yii::app()->getClientScript();
		$cs->registerCssFile($this->assetsUrl . '/css/dropzone.css');
		$cs->registerScriptFile($this->assetsUrl . '/js/dropzone.js', CClientScript::POS_END);
		
        
        // options
        $this->options = array_replace(array(
                'paramName' => 'file',
                'url' => $this->url
            ), $this->options
        );
        
        if($this->model) {
            if($this->attribute === null) throw new Exception('Model attribute is requred!');
            $validators = $this->model->getValidators($this->attribute);
            $fileValidator = null;
            foreach($validators as $validator) {
                if($validator instanceof CFileValidator) {
                    $fileValidator = $validator;
                    break;
                }
            }
            
            if($fileValidator) {
                $this->options['maxFiles'] = $fileValidator->maxFiles;
                $this->options['paramName'] = get_class($this->model) . "[{$this->attribute}]";
                $this->options['maxFilesize'] = ($fileValidator->maxSize / 1024 / 1024);
                $this->options['filesizeBase'] = 1024;
                $this->options['acceptedFiles'] = '.' . preg_replace('/,[ ]*/', ', .', $fileValidator->types);
                $this->options['dictMaxFilesExceeded'] = $fileValidator->tooMany;
                $this->options['dictFileTooBig'] = $fileValidator->tooLarge;
                $this->options['dictInvalidFileType'] = $fileValidator->wrongType;
            }
        }
        
        $options = CJavaScript::encode($this->options);
        
        // events
        $events = array();
        foreach($this->events as $event_name => $handler) {
            array_push($events, "
                $id.on('$event_name', $handler);
            ");
        }
        $events = implode(PHP_EOL, $events);
        
        // html options
        $htmlOptions['id'] = $id;
        $htmlOptions['class'] = 'dropzone' . ($this->cssClass ? $this->cssClass : '');
        echo CHtml::tag('div', $htmlOptions, '', true);
        
		$cs->registerScript($id, "
            Dropzone.autoDiscover = false;
            var $id = new Dropzone('div#$id', $options);
            $events
        ", CClientScript::POS_END);

	}
    
    
    public function __get($name) {
        if(property_exists($this, $name)) {
            return $this->$name;
        } else {
            if(isset($this->options[$name]))
                return $this->options[$name];
            return null;
        }
    }
    
    public function __set($name, $value) {
        if(property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            $this->options[$name] = $value;
        }
    }
    

}
