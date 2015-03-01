<?php

class Qlick_Form_NulledForm extends Zend_Form
{
//    // защита от CSRF
//    public function __construct($options = null) {
//        parent::__construct($options);
// 
//        $uniqueSalt = Zend_Crypt::hash('MD5', $this->name . uniqid() . microtime());
//        //$csrfToken = new Zend_Form_Element_Hash($this->name . '_csrf_token');
//        //$csrfToken->setSalt($uniqueSalt);
//        $this->addElement( 'hash', 'token', array('salt' => $uniqueSalt) );
//    }
    
    /**
     * Установить шаблон для формы
     *
     * @param string $template Имя файла с шаблоном без расширения
     */
    public function setTemplate($template)
    {
        $this->setDecorators(array(
            array('viewScript', array(
               'viewScript' => $template . '.phtml'
            )))
        );
    }

    /**
     * Добавление элемента в форму без декораторов
     *
     * @see Zend_Form::addElement()
     */
    public function addElement($element, $name = null, $options = null)
    {
        parent::addElement($element, $name, $options);

        if (isset($this->_elements[$name])) {
            $this->_elements[$name]->removeDecorator('Label');
            $this->_elements[$name]->removeDecorator('HtmlTag');
            $this->_elements[$name]->removeDecorator('DtDdWrapper');
            $this->_elements[$name]->removeDecorator('Description');
        }
    }

    /**
     * Создание элемента формы
     *
     * @see Zend_Form::createElement()
     */
    public function createElement($type, $name, $options = null)
    {
        $element = parent::createElement($type, $name, $options);
        $element->removeDecorator('Label');
        $element->removeDecorator('HtmlTag');
        $element->removeDecorator('DtDdWrapper');
        $element->removeDecorator('Description');
        return $element;
    }
    
    public function trackReferrer(Zend_Controller_Request_Abstract $request)
    {
        $this->addElement('hidden', 'referrer');
        $this->setDefault('referrer', $request->getParam('referrer', $request->getServer('HTTP_REFERER')));
        return $this;
    }
    
    public function getReferrer($default = false)
    {
        if (!isset($this->referrer)) return $default;
        $val = $this->referrer->getValue();
        if ($val) return $val;
        return $default;
    }    
    
}

?>
