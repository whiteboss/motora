<?php
/**
 * 
 * Форма редактирования и добавления модели
 * 
 */

class Auto_Form_EditModel extends Qlick_Form_NulledForm {

    public function init() {

        $this->setName( 'edit_model_form' );
        $this->setMethod( 'post' );

        $this->addElement( 'text', 'name', array(
            'label'    => 'Наименование',
            'required' => true,
            'filters'  => array( 'StringTrim', 'StripTags' ),
            'decorators'    => array(array('ViewScript', array(
                'viewScript'    => 'standart_input.phtml',
                'maxlength'     => '40'
            )))
        ) );

        $table = new Auto_Model_Table_CarBody();
        $this->addElement( 'select', 'id_body', array(
            'label'    => 'Кузов',
            'required' => true,
            'multiOptions' => $table->getAll(),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $table = new Auto_Model_Table_CarMarks();
        $this->addElement( 'select', 'id_brand', array(
            'label'    => 'Марка',
            'required' => true,
            'multiOptions' => $table->getAll(),
            'decorators'    => array(array('ViewScript', array(
                'viewScript' => 'standart_select.phtml',
                'class'      => 'form element'
            )))
        ) );

        $this->addElement( 'file', 'main_photo', array(
            'label'    => 'Основная фотка для листинга',
            'required' => false,
            'decorators'    => array("File",array('ViewScript', array(
                 'viewScript'   => 'standart_file.phtml',
                 'placement'    => false
            )))
        ) );

        $this->addElement( 'hidden', 'photo', array(
            'label'    => 'Фото',
            'filters'  => array('StringTrim', 'StripTags'),
        ) );
        
        $this->addElement( 'textarea', 'description', array(
            'label'    => 'Описание',
            'value'     => '<table>
                                        <tr class="auto_TTX_section TTX_1">
                                                <th>
                                                        Тип кузова
                                                </th>
                                                <th>
                                                        Седан
                                                </th>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Марка кузова
                                                </td>
                                                <td>
                                                        TR596F
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Масса, кг
                                                </td>
                                                <td>
                                                        1600
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Число мест
                                                </td>
                                                <td>
                                                        5
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Число дверей
                                                </td>
                                                <td>
                                                        4
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Колеса
                                                </td>
                                                <td>
                                                        215/55R16 91V
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Объем топливного бака, л.
                                                </td>
                                                <td>
                                                        80
                                                </td>
                                        </tr>
                                </table>

                                <table>
                                        <tr class="auto_TTX_section TTX_2">
                                                <th>
                                                        Подушки безопасности
                                                </th>
                                                <th>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </th>
                                        </tr>
                                        <tr>
                                                <td>
                                                Ксеноновые фары
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                Кожаный салон
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_none"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Люк
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        CD-чейнджер
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Климат-контроль
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_none"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Парковочный радар
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        ABS
                                                </td>
                                                <td>
                                                        <div class="COLLECTION auto_TTX_yes"></div>
                                                </td>
                                        </tr>
                                </table>
                                <p class="complectation_main">* Для комплектации 2.5 Gran Tourismo 250S (12.2001 &mdash; 05.2003)</p>
                                <p>
                                        Модель передне/полноприводного купе гольф-класса Toyota Celica дебютировала в 1970 г. в модификации двухдверного купе. В 1976 г. был выпущен вариант с кузовом лифтбек класса GT. В 1978 г. свет увидело второе поколение автомобиля. В 1980 г. на платформе Toyota Celica была выпущена четырехдверная модификация в кузове седан. Третье поколение модели Toyota Celica начали производить в 1982 г. Пятая инкарнация этого спортивного авто в совершенно ином, актуальном дизайне появилась в 1989 г. Шестое поколение модели показали на токийском автосалоне в 1993 г., а седьмое — в Детройте в 1999 г. Производство Toyota Celica было приостановлено в 2005 г.
                                </p>
                                <p>
                                        Стильный и яркий Toyota Celica — это не только модель-долгожитель в линейке японского автопроизводителя. Это ещё и прекрасный образец спортивного купе, которое в течение нескольких десятилетий, да и по сей день, позволяет своему владельцу познать острую динамику езды, маневренность и великолепную управляемость высокотехнологичного авто, оснащенного функциональным двигателем (1,8 — литровый мотор VVTL-i).
                                </p>

                                <table class="complectations">
                                        <tr>
                                                <th>Комплектация
                                                </th>
                                                <th>Двигатель
                                                </th>
                                                <th>Объем,<br>куб. см.
                                                </th>
                                                <th>Топливо
                                                </th>
                                                <th>Мощность,<br>л. с.
                                                </th>
                                                <th>Привод
                                                </th>
                                                <th>Трансм.
                                                </th>
                                        </tr>
                                        <tr>
                                                <td>2.0 V20E Gran Tourismo
                                                </td>
                                                <td>3.0 V6
                                                </td>
                                                <td>2982
                                                </td>
                                                <td>Бензин
                                                </td>
                                                <td>128
                                                </td>
                                                <td>Задний
                                                </td>
                                                <td>4TS
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>2.0 V20E Gran Tourismo
                                                </td>
                                                <td>3.0 V6
                                                </td>
                                                <td>2982
                                                </td>
                                                <td>Бензин
                                                </td>
                                                <td>128
                                                </td>
                                                <td>Задний
                                                </td>
                                                <td>4TS
                                                </td>
                                        </tr>
                                        <tr>
                                                <td>2.0 V20E Gran Tourismo
                                                </td>
                                                <td>3.0 V6
                                                </td>
                                                <td>2982
                                                </td>
                                                <td>Бензин
                                                </td>
                                                <td>128
                                                </td>
                                                <td>Задний
                                                </td>
                                                <td>Ручная
                                                </td>
                                        </tr>
                                </table>',
                'rows'    => 4,
                'decorators'    => array(array('ViewScript', array(
                    'viewScript'    => 'standart_textarea.phtml',
                )))
        ) );

        $this->addElement ('submit', 'add_item', array(
            'label'   => 'Опубликовать',
            'ignore'  => true,
            'decorators' => array(array('ViewScript', array(
                'viewScript' => 'standart_submit.phtml',
                'formname'      => $this->getName(),
                'button_class'  => 'standard_save_button COLLECTION'
            )))
        ));

    }
    
}